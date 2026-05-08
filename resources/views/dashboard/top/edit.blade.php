<x-app-layout>
    <x-slot:title>イベント編集</x-slot:title>
    <x-slot:page>index</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:login>{{ $user->last_login_at?->format('Y.m.d H:i') }}</x-slot:login>
    <x-slot:old>{{ @$old }}</x-slot:old>
    @include('dashboard.top.partials.form', ['article' => $article])
</x-app-layout>