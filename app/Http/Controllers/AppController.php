<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Article;
use App\Models\ArticleVenue;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'venue_id' => ['nullable', 'integer', 'exists:article_venues,id'],
            'publish_from' => ['nullable', 'date'],
            'publish_to' => ['nullable', 'date', 'after_or_equal:publish_from'],
        ]);

        $venueId = $validated['venue_id'] ?? null;
        $publishFrom = $validated['publish_from'] ?? null;
        $publishTo = $validated['publish_to'] ?? null;

        $query = Article::with(['venue:id,venue_name', 'images'])
            ->orderBy('created_at', 'desc');

        if (!empty($venueId)) {
            $query->where('venue_id', $venueId);
        }

        if (!empty($publishFrom) || !empty($publishTo)) {
            $query->whereNotNull('published_at');

            if (!empty($publishTo)) {
                $query->whereDate('published_at', '<=', $publishTo);
            }

            if (!empty($publishFrom)) {
                $query->where(function ($periodQuery) use ($publishFrom) {
                    $periodQuery->whereNull('unpublished_at')
                        ->orWhereDate('unpublished_at', '>=', $publishFrom);
                });
            }
        }

        $data = $query->paginate(10)->withQueryString();
        $venues = ArticleVenue::query()
            ->orderBy('venue_name')
            ->get(['id', 'venue_name']);

        $filters = [
            'venue_id' => $venueId,
            'publish_from' => $publishFrom,
            'publish_to' => $publishTo,
        ];

        $this->loadArticleReservationCounts($data);

        return response()
            ->view('dashboard.top.index', compact('user', 'data', 'venues', 'filters'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 0);
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
    public function store(Request $request): RedirectResponse
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {   
        $user = Auth::user();
        $article = Article::with([
                'user',
                'venue',
                'images',
                'reservationSlots' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->findOrFail($id);

        return view('dashboard.top.show', compact('user', 'article'));
    }

    public function copy(Article $article): RedirectResponse
    {
        $user = Auth::user();

        $newArticle = DB::transaction(function () use ($article, $user) {
            $copy = Article::create([
                'user_id'              => $user->id,
                'title'                => $article->title . '【コピー】',
                'body'                 => $article->body,
                'freeword_1'           => $article->freeword_1,
                'freeword_2'           => $article->freeword_2,
                'header_image'         => $article->header_image,
                'body_image'           => $article->body_image,
                'body_image_captions'  => $article->body_image_captions,
                'memo'                 => $article->memo,
                'manager'              => $article->manager,
                'venue_id'             => $article->venue_id,
                'emails'               => $article->emails,
                'published_at'         => $article->published_at,
                'unpublished_at'       => $article->unpublished_at,
                'status'               => 'draft',
            ]);

            foreach ($article->images as $image) {
                Image::create([
                    'article_id'    => $copy->id,
                    'path'          => $image->path,
                    'original_name' => $image->original_name,
                    'size'          => $image->size,
                    'mime_type'     => $image->mime_type,
                    'sort_order'    => $image->sort_order,
                ]);
            }

            return $copy;
        });

        return redirect()->route('top.show', $newArticle)->with('msg', '記事をコピーしました');
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

    public function search(Request $request)
    {
        $user = Auth::user();
        $keyword = $request->input('keyword');
        $state = $request->input('state');

        $data = Article::orderBy('created_at', 'desc')
            ->paginate(100)
            ->withQueryString();

        if ($state !== null) {
            if ($state == 'unread') {
                $data = $service->unreadFilter($user, $keyword);
            } elseif ($state == 'myunread') {
                $data = $service->myUnreadFilter($user, $keyword);
            } else {
                $data = $service->filter($user, $state, $keyword);
            }
        }

        $this->loadArticleReservationCounts($data);

        $data->count = activeCount($user);
        $data->tilde = hasTildeCheck($data);
                
        return view('dashboard.top.index', compact('user', 'data', 'keyword'));
    }

    public function filter(Request $request)
    {
        $user = Auth::user();
        $service = new AppService;
        $state = $request->input('state');
        $keyword = $request->input('keyword');

        if ($state == 'unread') {
            $data = $service->unreadFilter($user, $state, $keyword);
        } elseif ($state == 'myunread') {
            $data = $service->myUnreadFilter($user, $state, $keyword);
        } else {
            $data = $service->filter($user, $state, $keyword);
        }

        $this->loadArticleReservationCounts($data);

        $data->count = activeCount($user);
        $data->tilde = hasTildeCheck($data);
                
        return view('dashboard.top.index', compact('user', 'data', 'state'));
    }

    public function export(Request $request)
    {
        $from = date('Ymd', strtotime($request->from));
        $to = date('Ymd', strtotime($request->to));
        $csv = '神座お客様アンケート' . $from . '_' . $to . '.csv';
        foreach (csvHeaders() as $str) {
            $header[] = mb_convert_encoding($str, 'SJIS-WIN', 'UTF-8');
        }

        if ($from == $to) {
            $data = Reservation::with('user')->with('state')->where('created_at', 'LIKE', "%{$from}%")->oldest()->get();    
        } else {
            $data = Reservation::with('user')->with('state')->whereBetween('created_at', [$from, $to])->oldest()->get();
        }

        foreach ($data as $datum) {
            mergingResponses($datum);
            $state = $datum->state->post_state == 'negative' ? '注意' : null;
            $state = $datum->state->post_active == 'active' ? '要対応' : $state;
            $state = $datum->state->post_ng == 'NG' ? '非公開' : $state;
            $datum->state = $state;
            $row[] = $datum->id;
            $row[] = $datum->state;
            $row[] = Carbon::parse($datum->created_at)->format('Y/m/d H:i');
            $row[] = $datum->user->name;
            $row[] = $datum->age;
            $row[] = $datum->gender;
            $row[] = $datum->name;
            $row[] = $datum->tel;
            $row[] = $datum->zipcode;
            $row[] = $datum->address;
            $row[] = $datum->email;
            $row[] = $datum->q01_a1;
            $row[] = $datum->q01_a2;
            $row[] = $datum->q01_a3;
            $row[] = $datum->q01_a4;
            $row[] = $datum->q01_a5;
            $row[] = $datum->q02;
            $row[] = $datum->q03;
            $row[] = $datum->q04;
            $row[] = str_replace(["\r\n", "\r", "\n"], '', $datum->q05);
            $row[] = $datum->q06;
            $row[] = str_replace(["\r\n", "\r", "\n"], '', $datum->q07);
            $row[] = $datum->q08;
            $row[] = $datum->q09;
            $row[] = $datum->q10;
            $row[] = $datum->q11;
            $row[] = $datum->q12;
            $row[] = $datum->q13;
            $row[] = $datum->q14;
            $row[] = $datum->q15;
            $row[] = $datum->q16;
            $row[] = $datum->q17;
            $row[] = $datum->q18;
            $row[] = $datum->q19;
            $row[] = str_replace(["\r\n", "\r", "\n"], '', $datum->q20);
            $rows[] = $row;
            $row = null;
        }

        $res = fopen($csv, 'w');
        fputcsv($res, $header);

        foreach($rows as $row) {
            mb_convert_variables('SJIS', 'UTF-8', $row);
            fputcsv($res, $row);
        }

        fclose($res);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $csv); 
        header('Content-Transfer-Encoding: binary');

        readfile($csv);
        unlink($csv);
    }

    public function loggedIn()
    {   
        $user = Auth::user();
        $service = new AppService;
        $data = $service->dataExtraction($user);
        $this->loadArticleReservationCounts($data);
        $data->count = activeCount($user);
        $data->tilde = hasTildeCheck($data);

        return response()
            ->view('dashboard.top.index', compact('user', 'data'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 0);
    }

    public function distribution(Request $request)
    {
        State::where('reservation_id', $request->id)
            ->update(['post_ng' => 'OK']);
        
        $shop = User::where('shop_id', $request->shop_id)->first();

        Mail::to($shop->email)->send(new SendMail($shop, $shop->name));
    
        return redirect()->back()->with('dst_msg', '配信しました');
    }

    private function loadArticleReservationCounts($data): void
    {
        if (!is_object($data) || !method_exists($data, 'getCollection')) {
            return;
        }

        $collection = $data->getCollection();
        if (!$collection) {
            return;
        }

        $articles = $collection->filter(fn ($item) => $item instanceof Article);
        if ($articles->isEmpty()) {
            return;
        }

        $articles->loadCount([
            'reservationSlots as reservation_slots_count',
            'reservations as reservations_count',
        ]);
        $articles->loadSum('reservationSlots as reservation_slots_capacity_sum', 'capacity');
    }
}