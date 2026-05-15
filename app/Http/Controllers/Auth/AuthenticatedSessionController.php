<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    private const SWITCHABLE_ROLES = ['developer', 'system', 'admin', 'manager', 'staff'];

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $email = $request->email;

        User::where('email', $email)
            ->update(['last_login_at' => now()]);

        return redirect('dashboard/top');

        // return redirect()->intended(route('dashboard/', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionUserId = (int) $request->session()->get('dev_role_switch_user_id', 0);

        if ($user && $sessionUserId === (int) $user->id) {
            $originalRole = (string) $request->session()->get('dev_role_original_role', 'developer');

            if (in_array($originalRole, self::SWITCHABLE_ROLES, true)) {
                $user->update(['role' => $originalRole]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('dashboard/login');
    }
}
