<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DevRoleSwitchController extends Controller
{
    private const SWITCHABLE_ROLES = ['developer', 'system', 'admin', 'manager', 'staff'];

    public function switch(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $session = $request->session();
        $sessionUserId = (int) $session->get('dev_role_switch_user_id', 0);
        $isSwitchSessionOwner = $sessionUserId === (int) $user->id;

        if (!$isSwitchSessionOwner && $user->role !== 'developer') {
            abort(403);
        }

        if (!$isSwitchSessionOwner) {
            $session->put('dev_role_switch_user_id', (int) $user->id);
            $session->put('dev_role_original_role', (string) $user->role);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(self::SWITCHABLE_ROLES)],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('msg', '検証用ロールを切り替えました。');
    }

    public function reset(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $session = $request->session();
        $sessionUserId = (int) $session->get('dev_role_switch_user_id', 0);

        if ($sessionUserId !== (int) $user->id) {
            abort(403);
        }

        $originalRole = (string) $session->get('dev_role_original_role', 'developer');
        if (!in_array($originalRole, self::SWITCHABLE_ROLES, true)) {
            $originalRole = 'developer';
        }

        $user->update(['role' => $originalRole]);

        $session->forget(['dev_role_switch_user_id', 'dev_role_original_role']);

        return back()->with('msg', '検証用ロールを解除しました。');
    }
}
