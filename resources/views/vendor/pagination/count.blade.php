@if ($paginator->hasPages())
    <li class="msg">
        検索結果：{{ $paginator->total() }}件
    </li>
@endif
