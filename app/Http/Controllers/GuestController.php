<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FormPostRequest;
use App\Models\User;
use App\Models\Post;
use App\Services\GuestService;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort(404);
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

        $user = User::where('shop_id', $request->shop_id)->first();

        $services = new GuestService;
        $request = $services->requestConvert($request);   
        $data = new Post();
        $data->fill($request->all())->save();

        $state = $services->stateCheck($data);

        $emails = $services->mailList($data);
        $name = $user->name;

        foreach ($emails as $email) {
            Mail::to($email->email)->send(new SendMail($email, $name));
        }
        
        return view('guest.thanks', compact('name'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shop = User::where('shop_id', $id)->first();

        if (empty($shop)) {
            abort(404);
        }

        return response()
        ->view('guest.index', compact('shop'))
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', 0);
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
}
