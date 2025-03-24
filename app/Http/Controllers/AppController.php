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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

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

        return response()
            ->view('dashboard.top.index', compact('user', 'data'))
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

        $data = mergingResponses($data);

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
        $csv = '神座お客様アンケート' . $from . '_' . $to . '.csv';
        foreach (csvHeaders() as $str) {
            $header[] = mb_convert_encoding($str, 'SJIS-WIN', 'UTF-8');
        }

        if ($from == $to) {
            $data = Post::with('user')->with('state')->where('created_at', 'LIKE', "%{$from}%")->oldest()->get();    
        } else {
            $data = Post::with('user')->with('state')->whereBetween('created_at', [$from, $to])->oldest()->get();
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
            $row[] = $datum->q01;
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
        State::where('post_id', $request->id)
            ->update(['post_ng' => 'OK']);
        
        $shop = User::where('shop_id', $request->shop_id)->first();

        Mail::to($shop->email)->send(new SendMail($shop, $shop->name));
    
        return redirect()->back()->with('dst_msg', '配信しました');
    }
}