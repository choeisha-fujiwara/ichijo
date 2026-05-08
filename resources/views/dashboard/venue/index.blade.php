<x-app-layout>
    <x-slot:title>会場管理</x-slot:title>
    <x-slot:page>venue</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <div class="content venue-page">
        <div class="venue-head">
            <div class="venue-head-row">
                <div>
                    <h2>会場一覧</h2>
                    <p>カードをクリックすると会場情報を編集できます。</p>
                </div>
                <a href="{{ route('venue.create') }}" class="venue-add-button">＋新規追加</a>
            </div>
        </div>
        <div class="venue-list">
            @foreach ($venues as $venue)
            <a href="{{ route('venue.show', $venue) }}" class="venue-card">
                @if (!empty($venue->image))
                    <img src="{{ route('venue.image', $venue) }}" alt="{{ $venue->venue_name }}">
                @else
                    <div class="venue-card-placeholder">NO IMAGE</div>
                @endif
                <h3>{{ $venue->venue_name }}</h3>
                <p>{{ $venue->address }}</p>
            </a>
            @endforeach
        </div>
        @if ($venues->isEmpty())
            <p class="venue-empty">会場データがありません。</p>
        @endif
        

    </div>
    <ul class="msg">
        @if (@$msg)
        <li>{{ @$msg }}</li>
        @endif
        @if (@$keyword)
        {{ @$data->links('vendor.pagination.count') }}
        @endif
    </ul>
</x-app-layout>   