<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Area;
use App\Models\State;
use App\Models\Comment;
use App\Services\AppService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\PostExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $user = Auth::user();
        $service = new AppService;
        $data = $service->dataExtraction($user);
        $data->count = activeCount($user);
        $data->tilde = hasTildeCheck($data);

        return view('dashboard.top.index', compact('user', 'data'));
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
        $user = Auth::user();
        $data = Post::findOrFail($id);
        
        if ($user->role == 'shop') {
            $data = deletionPersonalFindData($data);
        }

        $data = alreadyRead($user, $data);
        $data->count = activeCount($user);

        return view('dashboard.top.show', compact('user', 'data'));
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
        $service = new AppService;
        $keyword = $request->input('keyword');
        $state = $request->input('state');

        $data = $service->search($user, $keyword);

        if ($state !== null) {
            if ($state == 'unread') {
                $data = $service->unreadFilter($user, $keyword);
            } elseif ($state == 'myunread') {
                $data = $service->myUnreadFilter($user, $keyword);
            } else {
                $data = $service->filter($user, $state, $keyword);
            }
        }

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

        $data->count = activeCount($user);
        $data->tilde = hasTildeCheck($data);
                
        return view('dashboard.top.index', compact('user', 'data', 'state'));
    }

    public function export(Request $request)
    {
        $from = date('Ymd', strtotime($request->from));
        $to = date('Ymd', strtotime($request->to));
        $file = '神座お客様アンケート' . $from . '_' . $to . '.csv';

        return Excel::download(new PostExport($from, $to), $file);
    }
}
