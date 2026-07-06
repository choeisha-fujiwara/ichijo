<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FormPostRequest;
use App\Mail\ReservationAutoReply;
use App\Mail\ReservationPostedNotification;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Article;
use App\Models\ReservationSlot;
use App\Models\PageView;
use App\Services\GuestService;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            return redirect('dashboard/top');
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormPostRequest $request)
    {
        $request->session()->regenerateToken();

        $validated = $request->validated();

        $slot = DB::transaction(function () use ($validated) {
            $slot = ReservationSlot::query()
                ->lockForUpdate()
                ->where('id', $validated['reservation_slot_id'])
                ->where('article_id', $validated['article_id'])
                ->first();

            if (! $slot || $slot->capacity <= $slot->reserved_count) {
                throw ValidationException::withMessages([
                    'reservation_slot_id' => '選択した予約枠は受け付けできません。',
                ]);
            }

            $data = new Reservation();
            $data->fill([
                'article_id' => $validated['article_id'],
                'reservation_slot_id' => $validated['reservation_slot_id'],
                'reservation_datetime' => $validated['reservation_datetime'],
                'firstname' => $validated['first_name'],
                'lastname' => $validated['last_name'],
                'firstname_kana' => $validated['first_name_kana'],
                'lastname_kana' => $validated['last_name_kana'],
                'zipcode' => $validated['postal_code_1'].'-'.$validated['postal_code_2'],
                'prefecture' => $validated['address_prefectures'],
                'city' => $validated['address_municipalities'],
                'address' => $validated['address_detail'],
                'building' => $validated['address_building'] ?? null,
                'phone' => $validated['phone-1'].'-'.$validated['phone-2'].'-'.$validated['phone-3'],
                'email' => $validated['email'],
                'memo' => $validated['memo'] ?? null,
            ])->save();

            $slot->increment('reserved_count');

            return $slot->fresh();
        });

        $name = trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''));

        $article = Article::with('venue')->find($validated['article_id']);
        $venueName = $article?->venue?->venue_name;

        $reservationDateTime = null;
        if (!empty($validated['reservation_datetime'])) {
            $reservationDateTime = \Carbon\Carbon::parse($validated['reservation_datetime'])->format('Y年m月d日 H:i');
        } elseif ($slot && $slot->date && $slot->start_time) {
            $reservationDateTime = \Carbon\Carbon::parse($slot->date.' '.$slot->start_time)->format('Y年m月d日 H:i');
        }

        $notificationPayload = [
            'articleTitle' => (string) ($article?->title ?? ''),
            'venueName' => (string) ($venueName ?? ''),
            'reservationDateTime' => (string) ($reservationDateTime ?? ''),
            'fullName' => trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? '')),
            'fullNameKana' => trim(($validated['first_name_kana'] ?? '').' '.($validated['last_name_kana'] ?? '')),
            'phone' => (string) (($validated['phone-1'] ?? '').'-'.($validated['phone-2'] ?? '').'-'.($validated['phone-3'] ?? '')),
            'email' => (string) ($validated['email'] ?? ''),
            'address' => trim((string) (($validated['address_prefectures'] ?? '').($validated['address_municipalities'] ?? '').($validated['address_detail'] ?? '').(!empty($validated['address_building']) ? ' '.$validated['address_building'] : ''))),
            'memo' => (string) ($validated['memo'] ?? ''),
        ];

        $destinationEmails = collect($article?->emails ?? [])
            ->filter(fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->map(fn ($email) => trim($email))
            ->filter()
            ->unique()
            ->values();

        if ($destinationEmails->isEmpty()) {
            Log::warning('Reservation notification destination is empty.', [
                ...$this->reservationMailLogContext($article, $validated['email'] ?? null),
            ]);
        }

        foreach ($destinationEmails as $destinationEmail) {
            try {
                Mail::to($destinationEmail)->send(new ReservationPostedNotification($notificationPayload));
            } catch (\Throwable $e) {
                Log::error('Failed to send reservation notification email.', [
                    ...$this->reservationMailLogContext($article, $validated['email'] ?? null, $destinationEmail),
                    'exception' => $e::class,
                    'error' => $e->getMessage(),
                ]);
                report($e);
            }
        }

        $autoReplySent = true;
        try {
            Mail::to($validated['email'])->send(new ReservationAutoReply($name, $venueName, $reservationDateTime));
        } catch (\Throwable $e) {
            $autoReplySent = false;
            Log::error('Failed to send reservation auto-reply email.', [
                ...$this->reservationMailLogContext($article, $validated['email'] ?? null, $validated['email'] ?? null),
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }
        
        
        $backUrl = $article ? route('show.public', $article->public_token) : null;

        return view('guest.thanks', compact('name', 'autoReplySent', 'backUrl'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = Article::with(['images', 'venue'])->where('id', $id)->first();

        if (empty($article)) {
            abort(404);
        }

        PageView::recordView($article->id);

        return $this->renderGuestPage($article);
    }

    public function showByToken(string $token)
    {
        $article = Article::with(['images', 'venue'])
            ->where('public_token', $token)
            ->first();

        if (empty($article)) {
            abort(404);
        }

        PageView::recordView($article->id);

        return $this->renderGuestPage($article);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function renderGuestPage(Article $article)
    {
        if (!Auth::check() && !$this->canGuestViewArticle($article)) {
            abort(404);
        }

        $reservation = ReservationSlot::where('article_id', $article->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()
            ->view('guest.index', compact('article', 'reservation'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 0);
    }

    private function canGuestViewArticle(Article $article): bool
    {
        if ($article->status !== 'publish' || empty($article->published_at)) {
            return false;
        }

        $now = now();

        if ($now->lt($article->published_at)) {
            return false;
        }

        if (!empty($article->unpublished_at) && $now->gt($article->unpublished_at)) {
            return false;
        }

        return true;
    }

    private function reservationMailLogContext(?Article $article, ?string $reservationEmail = null, ?string $destinationEmail = null): array
    {
        return [
            'article_id' => $article?->id,
            'reservation_email' => $reservationEmail,
            'destination_email' => $destinationEmail,
            'mail_default' => (string) Config::get('mail.default'),
            'mail_from_address' => (string) Config::get('mail.from.address'),
            'mail_from_name' => (string) Config::get('mail.from.name'),
            'mail_host' => (string) Config::get('mail.mailers.smtp.host'),
            'mail_port' => (string) Config::get('mail.mailers.smtp.port'),
            'mail_encryption' => (string) Config::get('mail.mailers.smtp.encryption'),
        ];
    }
}
