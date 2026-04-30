<x-app-layout>
    <x-slot:title>イベント作成</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:keyword>{{ @$keyword }}</x-slot:keyword>
    <x-slot:state>{{ @$state }}</x-slot:state>
    <x-slot:old>{{ @$old }}</x-slot:old>
    @include('dashboard.top.partials.form', ['article' => null])
    <ul class="msg">
        @if (@$msg)
        <li>{{ @$msg }}</li>
        @endif
        @if (@$keyword)
        {{ @$data->links('vendor.pagination.count') }}
        @endif
    </ul>
</x-app-layout>   