<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FormPostRequest;
use App\Models\User;
use App\Models\Customer;
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
        $data = new Customer();
        $services = new GuestService;

        $data = $services->requestConvert($request, $data);
        $data->save();

        $name = $request->shop_name;
        $area = $request->area;
        $emails = User::where('area', '=', $area)->orWhere('role', '=', 'admin')->get();

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
        $user = User::find($id);
        
        if (empty($user) || $user->role !== 'shop') {
            abort(404);
        }

        return view('guest.index', compact('user'));
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
