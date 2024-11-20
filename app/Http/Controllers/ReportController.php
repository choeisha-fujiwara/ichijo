<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Area;
use App\Models\State;
use App\Services\AppService;
use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $areas = Area::groupBy('area_name')->get(['area_name']);
        $blocks = Area::groupBy('block_name')->where('block_name', '!=', null)->get(['block_name']);
        $users = User::where('role', 'shop')->get();
        $from = Carbon::now()->subMonthNoOverflow(6);
        $to = Carbon::now();

        $requests = collect();
        $requests->area = null;
        $requests->block = null;
        $requests->shop = null;
        $requests->from = null;
        $requests->to = null;
        $requests->return = null;

        if ($user->role == 'admin') {
            $data = Post::whereBetween('created_at', [$from, $to])->get(); 
        }

        if ($user->role == 'area_manager') {
            $area = $user->area->area_name;
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($area) {
                $query->where('area_name', $area);
            })
            ->whereBetween('created_at', [$from, $to])
            ->get();
            $requests->area = $area;
            $requests->return = 'area';
        }

        if ($user->role == 'manager') {
            $area = $user->area->area_name;
            $block = $user->area->block_name;
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($area, $block) {
                $query->where('area_name', $area)
                ->where('block_name', $block);
            })
            ->whereBetween('created_at', [$from, $to])
            ->get();
            $requests->area = $area;
            $requests->block = $block;
            $users = $users->where('area_id', $user->area_id);
        }

        if ($user->role == 'shop') {
            $data = Post::whereBetween('created_at', [$from, $to])
            ->where('shop_id', $user->shop_id)
            ->get();
            $requests->shop = $user->shop_id;
            $requests->shop_name = $user->name;
        }

        $data->isEmpty() ? $sources = 'none' : $sources = compositionRatio($data);
        $data->count = activeCount($user);

        return view('dashboard.report.index', compact('user', 'sources', 'areas', 'blocks', 'users', 'data', 'requests'));
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

    public function report(Request $request)
    {
        $user = Auth::user();
        $area = $request->area;
        $block = $request->block;
        $shop = $request->shop;
        $from = $request->from;
        $to = $request->to;

        $areas = Area::groupBy('area_name')->get(['area_name']);
        $blocks = Area::groupBy('block_name')->where('block_name', '!=', null)->get(['block_name']);
        $users = User::where('role', 'shop')->get();

        if ($shop) {
            $data = Post::whereBetween('created_at', [$from, $to])
                ->where('shop_id', $shop)
                ->get();
        } elseif ($block) {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($area, $block) {
                $query->where('area_name', $area)
                ->where('block_name', $block);
            })
            ->whereBetween('created_at', [$from, $to])
            ->get();
        } elseif ($area == '全店') {
            $data = Post::whereBetween('created_at', [$from, $to])->get();
        } elseif ($area) {
            $data = Post::with('user.area')
            ->whereHas('user.area', function ($query) use ($area) {
                $query->where('area_name', $area);
            })
            ->whereBetween('created_at', [$from, $to])
            ->get();
        }

        $requests = collect();
        $requests->area = $area;
        $requests->block = $block;
        if ($shop !== null) {
            $user->role !== 'shop' ? $requests->shop = $shop : $requests->shop = null;
            $user->role !== 'shop' ? $requests->shop_name = User::where('shop_id', $shop)->first()->name : $requests->shop_name = $user->name;
        } else {
            $requests->shop = null;
        }
        $requests->users = User::where('role', 'shop')->get();
        $requests->from = $from;
        $requests->to = $to;
        $requests->return = 'return';

        $data->isEmpty() ? $sources = 'none' : $sources = compositionRatio($data);
        $data->count = activeCount($user);

        return view('dashboard.report.index', compact('user', 'sources', 'areas', 'blocks', 'users', 'data', 'requests'));
    }
}
