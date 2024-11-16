<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\State;

class CommentController extends Controller
{
    public function comment(Request $request)
    {
        $data = new Comment();
        $data->fill($request->all())->save();

        $active = State::where('post_id', $request->post_id)
            ->update(['post_active' => 'active']);

        return redirect()->back()->with('msg', 'コメントを更新しました');
    }

    public function destroy(string $id)
    {
        Comment::destroy($id);

        return redirect()->back()->with('msg', 'コメントを削除しました');
    }

    public function approval(Request $request)
    {
        $data = State::where('post_id', $request->post_id)
            ->update(['post_active' => 'approval']);

        return redirect()->back()->with('msg', 'この投稿を承認しました');
    }
}
