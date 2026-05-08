<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserAdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('dashboard.users.index', compact('user', 'users'));
    }
}
