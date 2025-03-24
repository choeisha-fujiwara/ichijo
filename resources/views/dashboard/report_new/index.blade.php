<x-app-layout>
    <x-slot:title>レポート</x-slot:title>
    <x-slot:page>report</x-slot:page>
    <x-slot:name>{{ $user->name }}</x-slot:name>
    <x-slot:role>{{ $user->role }}</x-slot:role>
    <x-slot:count>{{ @$data->count }}</x-slot:count>
    <x-slot:old>{{ @$old }}</x-slot:old>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
    <div class="content report">
        <div class="sp-select-btn">
            <span class="material-symbols-outlined">settings</span>
        </div>
        <div class="report-header">
            <form action="report" method="POST">
                @csrf
                <div class="report-selects">
                    <div class="select area {{ $user->role == 'admin' ? 'active' : null }}">
                        <p class="select-title area {{ $user->role !== 'admin' ? 'on' : null }}" data-name="area"><span class="title-span">{{ $requests->area == null ? 'エリア選択' : $requests->area }}</span><span class="material-symbols-outlined icon">arrow_drop_down</span></p>
                        <div class="select-buttons area">
                            <p class="select-button" data-name="area" data-area="全店">全店</p>
                            @foreach ($areas as $area)
                            <p class="select-button" data-name="area" data-area="{{ $area->area_name }}">{{ $area->area_name }}</p>
                            @endforeach
                        </div>
                        <input type="hidden" class="select-input area" name="area" value="{{ $requests->area == null ? null : $requests->area }}" />
                    </div>
                    <div class="select block {{ $user->role == 'admin' || $user->role == 'area_manager' ? 'active' : null }}">
                        <p class="select-title" data-name="block"><span class="title-span">{{ $requests->block == null ? 'ブロック選択' : $requests->block }}</span><span class="material-symbols-outlined icon">arrow_drop_down</span></p>
                        <div class="select-buttons block">
                            @foreach ($blocks as $block)
                            <p class="select-button" data-name="block" data-block="{{ $block->block_name }}">{{ $block->block_name }}</p>
                            @endforeach
                        </div>
                        <input type="hidden" class="select-input block" name="block" value="{{ $requests->block == null ? null : $requests->block }}" />
                    </div>
                    <div class="select shop {{ $user->role !== 'shop' ? 'active' : null }}">
                        <p class="select-title shop" data-name="shop"><span class="title-span">{{ $requests->shop == null ? '店舗選択' : $requests->shop_name }}</span><span class="material-symbols-outlined icon">arrow_drop_down</span></p>
                        <div class="select-buttons shop">
                            @foreach ($users as $user)
                            <p class="select-button active" data-name="shop" data-shop="{{ $user->name }}" data-value="{{ $user->shop_id }}" data-belong="{{ $user->area->area_name }}" data-blocking="{{ $user->area->area_name }}{{ $user->area->block_name }}">{{ $user->name }}</p>
                            @endforeach
                        </div>
                        <input type="hidden" class="select-input shop" name="shop" value="{{ $requests->shop == null ? null : $requests->shop }}">
                    </div>
                    <div class="datepickers {{ $user->role == 'shop' ? 'active' : null }}">
                        <div class="datepicker">
                            <input type="text" id="from" class="input-date from" name="from" placeholder="開始日" autocomplete="off" value="{{ $requests->from == null ? null : $requests->from }}" readonly>
                        </div>
                        <div>
                            <p><span class="material-symbols-outlined icon">arrow_right</span></p>
                        </div>
                        <div class="datepicker">
                            <input type="text" id="to" class="input-date to" name="to" placeholder="終了日" autocomplete="off" value="{{ $requests->to == null ? null : $requests->to }}" readonly>
                        </div>
                    </div>
                    <div class="report-submit">
                        <label for="submit-btn" class="submit-label">
                            <span class="material-symbols-outlined thin icon">deployed_code</span>
                            <span>集計</span>
                        </label>
                        <input type="submit" id="submit-btn" value="" />
                    </div>
                </div>
            </form>
        </div>
        <div class="report-contents table-box small-box {{ $sources == 'none' ? 'no-data' : null }}">
            <div class="report-wrapper q02" data-section="q02">
                <x-new-chart section="ラーメンの味" />
            </div>
            <div class="report-wrapper q03 hidden" data-section="q03">
                <x-new-chart section="スープの味" />
            </div>
            <div class="report-wrapper q04 hidden" data-section="q04">
                <x-new-chart section="チャーシューの味" />
            </div>
            <div class="report-wrapper q06 hidden" data-section="q06">
                <x-new-chart section="炒飯の味" />
            </div>
            <div class="report-wrapper q08 hidden" data-section="q08">
                <x-new-chart section="餃子の味" />
            </div>
            <div class="report-wrapper q09 hidden" data-section="q09">
                <x-new-chart section="接客について" />
            </div>
        </div>
    </div>
    <ul class="msg">
        @if(@$msg)
            <li>{{ @$msg }}</li>
        @endif
        {!! $sources == 'none' ? '<li>該当データがありません</li>' : null !!}
    </ul>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/i18n/jquery.ui.datepicker-ja.min.js"></script>
    
    <script>
        var data = @json($sources);
        var sections = data[0][0][0];
        var items1 = ['', '非常に満足した', '満足した', 'どちらでもない', '満足できなかった', 'まったく満足できなかった'];
        var items2 = ['', '非常に満足した+満足した', 'どちらでもない', 'まったく満足できなかった+満足できなかった'];
        
        if (data[0][0]['q02'].length > 7) {
            $('.report-contents').removeClass('small-box')
        }

        $(window).on('load', function () {
            columnChart(data[0][0], data[0][1], 'q02', 'top', items1);
            columnChart(data[1][0], data[1][1], 'q02', 'sec', items2);
        });

        $('.report-contents').on('scroll', function(){
            var scroll = Math.round($('.report-contents').scrollTop() / $('.report-wrapper').height());
            var section = sections[scroll];
            if($('.report-wrapper' + '.' + section).hasClass('hidden')) {
                columnChart(data[0][0], data[0][1], section, 'top', items1);
                columnChart(data[1][0], data[1][1], section, 'sec', items2);
                $('.report-wrapper' + '.' + section).removeClass('hidden');
            }
        });

        function columnChart(data, tooltips, section, cat, items) {
            var chartHeight = $('.chart-bg span').height() * 4 + 3;
            var count = cat == 'top' ? 6 : 4;
            if (window.matchMedia("(max-width: 576px)").matches) {
                var chartCount = 4;
            } else {
                var chartCount = data[section].length;
            }
            for (i = 0; i < chartCount; i++) {
                var bars = '<div class="chart-bars bars' + i + '"></div>';
                $('.' + section + ' .report-content.' + cat + ' .chart-inner').append(bars);
                for (ii = 0; ii < count; ii++) {
                    var annotation = data[section][i][ii];
                    var tooltip = tooltips[section][i][ii];
                    var before = data[section][i][ii - 1];
                    var height = annotation * chartHeight / 100;
                    var bar = '<div class="' + section + ' ' + cat + ' chart-bar bar' + ii + (annotation < 3 ? ' small' : '') + (before < 3 ? ' before-small' : '') + '"><p class="annotation">' + annotation + (ii !== 0 ? '%' : '') + '</p><p class="tooltip">' + items[ii] + '<br>' + (tooltip !== undefined ? tooltip : 0) + '件</p></div>';
                    $('.' + section + ' .' + cat + ' .bars' + i).append(bar);
                    $('.' + section + ' .' + cat + ' .bars' + i + ' .bar' + ii).height(height);
                }
            }
            $('.chart-bar').addClass('active');
            setTimeout(() => {
                $('.chart-bar').addClass('inactivity');
            }, 10000);
        };
    </script>
    <script>
        var requests = @json($requests->return);
        if (requests == 'return') {
            $('.datepickers, .submit-label').addClass('active');
            if (@json($requests->area)) {
                $('[data-area="' + @json($requests->area) + '"]').addClass('selected');
                $('.shop .select-button').removeClass('active');
                if (@json($requests->block) == null) {
                    $('[data-belong="' + @json($requests->area) + '"]').addClass('active');
                } else {
                    $('[data-blocking="' + @json($requests->area) + @json($requests->block) + '"]').addClass('active');
                }
            }
        }
    </script>  
</x-app-layout>