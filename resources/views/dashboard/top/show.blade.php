<x-app-layout>
    <x-slot:title>投稿詳細</x-slot:title>
    <x-slot:page>show</x-slot:page>
    <x-slot:name>{{ @$user->name }}</x-slot:name>
    <x-slot:role>{{ @$user->role }}</x-slot:role>
    <x-slot:count>{{ $data->count }}</x-slot:count>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <div class="show">
        <div class="show-inner inner">
            <div class="show-contents">
                <div class="show-state {{ $data->state->post_state }} {{ $data->state->post_active }} {{ $data->state->post_ng == 'NG' ? 'ng' : null }}">
                    <span class="material-symbols-outlined state-icon">circle</span>
                    {!! $data->state->post_state == 'negative' ? '<span class="state-msg negative">注意！</span>' : null !!}
                    {!! $data->state->post_active == 'active' ? '<span class="state-msg active">要対応</span>' : null !!}
                    {!! $data->state->post_active == 'approval' ? '<span class="state-msg approval">承認済み</span>' : null !!}
                    {!! $data->state->post_ng == 'NG' ? '<span class="state-msg ng">非公開</span>' : null !!}
                    {!! json_decode($data->state->post_read, true)['shop'] !== '' ? '<span class="material-symbols-outlined read">circle</span><span class="state-msg reading">既読</span>' : '<span class="material-symbols-outlined unread">circle</span><span class="state-msg reading">未読</span>' !!}
                </div>
                <div class="show-date">{{ $data->created_at->isoFormat('M月D日（ddd） H:mm') }}</div>
                {{-- <div class="read-data">
                    @if ($user->role == 'admin')
                    <p>{!! json_decode($data->state->post_read, true)['area_manager'] !== '' ? '<span class="material-symbols-outlined read-icon read">circle</span><span>AMG</span>' : '<span class="material-symbols-outlined read-icon unread">circle</span><span>AMG</span>' !!}</p>
                    @endif
                    @if ($user->role == 'admin' || $user->role == 'area_manager')
                    <p>{!! json_decode($data->state->post_read, true)['manager'] !== '' ? '<span class="material-symbols-outlined read-icon read">circle</span><span>MG</span>' : '<span class="material-symbols-outlined read-icon unread">circle</span><span>MG</span>' !!}</p>
                    @endif
                    @if ($user->role == 'admin' || $user->role == 'area_manager' || $user->role == 'manager')
                    <p>{!! json_decode($data->state->post_read, true)['shop'] !== '' ? '<span class="material-symbols-outlined read-icon read">circle</span><span>店舗</span>' : '<span class="material-symbols-outlined read-icon unread">circle</span><span>店舗</span>' !!}</p>
                    @endif
                </div> --}}
                <div class="shop-name">{{ $data->user->name }}</div>
                <div class="attribute-data {{ $user->role == 'shop' ? 'shop' : null }}">
                    <p class="icon"><span class="material-symbols-outlined">person</span></p>
                    <p>{{ $data->age }}</p>
                    <p>{{ $data->gender }}</p>
                </div>
                @if ($user->role !== 'shop')
                <div class="personal-data">
                    <div class="flex">
                        <p class="item">Name:</p>
                        <p class="{{ $data->name !== null ? null : 'none' }}">{{ $data->name !== null ? $data->name : 'None' }}</p>
                    </div>
                    <div class="flex">
                        <p class="item">Address:</p>
                        <p class="{{ $data->zipcode !== null ? null : 'none' }}">
                            <span class="{{ $data->zipcode !== null ? null : 'none' }}">{{ $data->zipcode !== null ? '〒' . $data->zipcode : '〒' }}</span>
                            <span class="{{ $data->address !== null ? null : 'none' }}">{{ $data->address !== null ? $data->address : 'None' }}</span>
                        </p>
                    </div>
                    <div class="flex">
                        <p class="item">Tel:</p>
                        <p class="{{ $data->tel !== null ? null : 'none' }}">{{ $data->tel !== null ? $data->tel : 'None' }}</p>
                    </div>
                    <div class="flex">
                        <p class="item">Email:</p>
                        <p class="{{ $data->email !== null ? null : 'none' }}">{{ $data->email !== null ? $data->email : 'None' }}</p>
                    </div>
                </div>
                @endif
                <div class="questions-data">
                    @php
                    $count = count(questions());
                    @endphp
                    @for ($i = 1; $i < $count; $i ++)
                    @php
                    $num = $i < 10 ? 'q0' . $i : 'q' . $i;
                    @endphp
                    <div class="questions-datum {{ textareaCheck(questions()[$i]) }}">
                        <p>
                            <span class="material-symbols-outlined question-icon">circle</span>
                            <span>{{ questions()[$i] }}</span>
                        </p>
                        <p class="reply {{ negativeCheck($data->$num) }} {{ wordCheck($data->$num) }}">
                            <span class="material-symbols-outlined question-icon">arrow_right_alt</span>
                            <span class="{{ $data->$num !== null ? null : 'none'}}">{{ $data->$num !== null ? $data->$num : '無回答' }}</span>
                        </p>
                    </div>
                    @endfor
                </div>
                <div class="comment-data">
                    @foreach ($data->comments as $comment)
                    <div class="comment-card">
                        <p class="comment-user"><span class="material-symbols-outlined icon">account_circle</span><span>{{ $comment->user->name }}</span></p>
                        <p class="comment-date">{{ $comment->created_at->isoFormat('M月D日（ddd） H:mm') }}</p>
                        <p class="comment">{{ $comment->comment }}</p>
                        @if ($user->role == 'admin' || $user->id == $comment->user_id)
                        <p class="destroy"><a href="{{ route('comment.destroy', ['id' => $comment->id]) }}"><span class="material-symbols-outlined icon">delete</span></a></p>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="comment-form">
                    @if (count($data->comments) > 0 && $user->role == 'admin' && $data->state->post_active !== 'approval')
                    <form action="approval" method="POST">
                    @csrf
                        <input type="hidden" name="post_id" value="{{ $data->id }}" />
                        <input type="submit" class="approval-btn" value="承認する" />
                    </form>
                    @endif
                    {!! count($data->comments) == 0 ? '<p class="no-comment">まだコメントはありません</p>' : null !!}
                    <form action="comment" method="POST" class="comment-form comment-write">
                    @csrf
                        <p class="comment-guide"><span>コメントを書く</span><span class="material-symbols-outlined icon">edit</span></p>
                        <textarea name="comment" class="active-text">{{ old('comment') }}</textarea>
                        <input type="hidden" name="post_id" value="{{ $data->id }}" />
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <input type="submit" class="comment-btn" value="送信" />
                    </form>
                </div>
                <div class="close-btn">
                    <p data-updated="{{ session('updated') }}">戻る</p>
                </div>
            </div>
        </div>
    </div>
    <ul class="msg scroll">
        @if(session('msg'))
            <li>{{ session('msg') }}</li>
        @endif
    </ul>
    <div class="hidden-obj page" data-page="show"></div>
</x-app-layout>