<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\ArticleVenue;

class UserAdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'staff') {
            return redirect()->route('top.index');
        }

        $since = now()->subDays(30)->toDateTimeString();

        $displayRoles = match ($user->role) {
            'manager' => ['staff'],
            default => ['staff', 'manager'],
        };

        $users = User::query()
            ->whereIn('role', $displayRoles)
            ->orderBy('id')
            ->addSelect([
                'articles_count' => \App\Models\Article::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('user_id', 'users.id')
                    ->where('created_at', '>=', $since),
                'recent_reservation_count' => Reservation::query()
                    ->selectRaw('COUNT(*)')
                    ->join('articles', 'articles.id', '=', 'reservations.article_id')
                    ->whereColumn('articles.user_id', 'users.id')
                    ->where('reservations.created_at', '>=', $since),
            ])
            ->withCount(['reservationAccessLogs as recent_access_count' => function ($q) use ($since) {
                $q->where('accessed_at', '>=', $since);
            }])
            ->paginate(50)
            ->withQueryString();

        // manager のメトリクスを各所属の合計に変更
        $users->getCollection()->transform(function ($user) use ($since) {
            if ($user->role === 'manager') {
                // 同じ所属の staff ユーザーのメトリクスを集計
                $staffMetrics = User::query()
                    ->where('role', 'staff')
                    ->where('affiliation', $user->affiliation)
                    ->addSelect([
                        'articles_count' => \App\Models\Article::query()
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('user_id', 'users.id')
                            ->where('created_at', '>=', $since),
                        'recent_reservation_count' => Reservation::query()
                            ->selectRaw('COUNT(*)')
                            ->join('articles', 'articles.id', '=', 'reservations.article_id')
                            ->whereColumn('articles.user_id', 'users.id')
                            ->where('reservations.created_at', '>=', $since),
                    ])
                    ->withCount(['reservationAccessLogs as recent_access_count' => function ($q) use ($since) {
                        $q->where('accessed_at', '>=', $since);
                    }])
                    ->get()
                    ->reduce(function ($carry, $u) {
                        return [
                            'articles_count' => ($carry['articles_count'] ?? 0) + ($u->articles_count ?? 0),
                            'recent_reservation_count' => ($carry['recent_reservation_count'] ?? 0) + ($u->recent_reservation_count ?? 0),
                            'recent_access_count' => ($carry['recent_access_count'] ?? 0) + ($u->recent_access_count ?? 0),
                        ];
                    }, []);

                $user->articles_count = $staffMetrics['articles_count'] ?? 0;
                $user->recent_reservation_count = $staffMetrics['recent_reservation_count'] ?? 0;
                $user->recent_access_count = $staffMetrics['recent_access_count'] ?? 0;
            }

            return $user;
        });

        return view('dashboard.users.index', compact('user', 'users'));
    }

    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->role === 'staff') {
            return redirect()->route('top.index');
        }

        $availableRoles = match ($user->role) {
            'manager' => ['staff'],
            'admin' => ['staff', 'manager'],
            default => ['staff', 'manager'],
        };

        $venues = ArticleVenue::orderBy('venue_name')->get(['id', 'venue_name']);

        return view('dashboard.users.create', compact('user', 'availableRoles', 'venues'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->role === 'staff') {
            return redirect()->route('top.index');
        }

        if ($user->role === 'manager') {
            $request->merge(['role' => 'staff']);
        }

        $availableRoles = match ($user->role) {
            'manager' => ['staff'],
            'admin' => ['staff', 'manager'],
            default => ['staff', 'manager'],
        };

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', $availableRoles)],
            'affiliation' => ['required', 'string', 'max:255'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('msg', 'ユーザーを追加しました');
    }

    public function edit(User $user): View|RedirectResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'staff') {
            return redirect()->route('top.index');
        }

        if (!$this->canEditTarget($currentUser, $user)) {
            return redirect()->route('users.index')->withErrors(['このユーザーは編集できません。']);
        }

        $venues = ArticleVenue::orderBy('venue_name')->get(['id', 'venue_name']);

        return view('dashboard.users.edit', [
            'user' => $currentUser,
            'target' => $user,
            'venues' => $venues,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'staff') {
            return redirect()->route('top.index');
        }

        if (!$this->canEditTarget($currentUser, $user)) {
            return redirect()->route('users.index')->withErrors(['このユーザーは編集できません。']);
        }

        $venueNames = ArticleVenue::query()->pluck('venue_name')->toArray();

        $validated = $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
            'affiliation' => ['required', 'string', 'max:255', Rule::in($venueNames)],
        ]);

        $updates = [
            'affiliation' => $validated['affiliation'],
        ];

        if (!empty($validated['password'])) {
            $updates['password'] = Hash::make($validated['password']);
        }

        $user->update($updates);

        return redirect()->route('users.index')->with('msg', 'ユーザー情報を更新しました。');
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'staff') {
            return redirect()->route('top.index');
        }

        if ((int) $currentUser->id === (int) $user->id) {
            return redirect()->route('users.index')->withErrors(['自分自身は削除できません。']);
        }

        $deletableRoles = match ($currentUser->role) {
            'manager' => ['staff'],
            default => ['staff', 'manager'],
        };

        if (!in_array($user->role, $deletableRoles, true)) {
            return redirect()->route('users.index')->withErrors(['このユーザーは削除できません。']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('msg', 'ユーザーを削除しました。');
    }

    private function canEditTarget(User $user, User $target): bool
    {
        if ($target->role === 'staff') {
            return in_array($user->role, ['manager', 'admin', 'system', 'developer'], true);
        }

        if ($target->role === 'manager') {
            return in_array($user->role, ['admin', 'system', 'developer'], true);
        }

        return false;
    }
}
