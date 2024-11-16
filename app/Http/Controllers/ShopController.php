<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Area;
use App\Models\State;
use App\Services\ShopService;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $data = User::where('role', 'shop')
            ->orderBy('shop_id', 'asc')
            ->get();
        
        $service = new ShopService;
        $data = $service->shopsData($data);
    
        $data->count = activeCount($user);

        return view('dashboard.shop.index', compact('user', 'data'));
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    public function shopSearch(Request $request)
    {
        $user = Auth::user();
        $keyword = $request->input('keyword');

        $data = User::where('role', 'shop')
        ->where('name', 'LIKE', "%{$keyword}%")
        ->orderBy('shop_id', 'asc')
        ->get();

        $service = new ShopService;
        $data = $service->shopsData($data);
    
        $data->count = activeCount($user);

        return view('dashboard.shop.index', compact('user', 'data', 'keyword'));
    }
}
