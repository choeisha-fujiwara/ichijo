// メッセージ表示・非表示
$(function() {
    if($('.msg ul').length) {
        setTimeout(function() {
            $('.msg li').addClass('end');
        }, 5200);
    }
});

// メニューの動き
$('.menu ul').hover (
    function () {
        $('.menu').addClass('active');
        $('.badge').addClass('active');
    },
    function () {
        $('.menu').removeClass('active');
        $('.badge').removeClass('active');
    }
);

// スクロールでtheadにシャドウ
$('.table-box').on('scroll',function() {
    if($('.table-box').scrollTop() > 0) {
        $('.table-head ul, .thead .tr').addClass('fixed');
    } else {
        $('.table-head ul, .thead .tr').removeClass('fixed');
    }
});

// 戻るボタン
$('.close-btn p').on('click', function() {
    history.go(-1);
});

// メッセージがあれば最下部までスクロール
$(function() {
    if($('.msg.scroll li').length) {
        $('.inner').scrollTop($('.inner')[0].scrollHeight - 600);
    }
});

// コメント書き込み欄がフォーカスされるとアクティブに
$('.active-text').focus(function() {
    $('.comment-btn').addClass('active');
});

// コメントnullの送信キャンセル
$('.comment-wright').submit(function() {
    if($('.active-text').val() == '') {
        alert('コメントが空欄です。送信を中断します。');
        return false;
    }
});

// 削除アラート
$('.destroy').on('click', function() {
    confirm("本当に削除しますか？");
});

// 検索ボタンのアクティブ化
$('.search-box').change(function() {
    $('.search-btn').addClass('active');
});

// ダウンロードのモーダル
$('.download-btn').on('click', function() {
    $('.download, .download-btn').toggleClass('active');
});
$('.download-close').on('click', function() {
    $('.download, .download-btn').toggleClass('active');
});

// ダウンロード期間が入力されたらボタンのアクティブ化
$('.download .input-date').change(function() {
    if ($(this).data('data-input') == 'on') {
        $('.submit-label').addClass('active')
    } else {
        $('.input-date').data('data-input', 'on');
    }
});

// フィルタボタンアクション
$('.th-filter-btn span').on('click', function() {
    $('.table-filter').toggleClass('active');
});
$(document).click(function(event) {
    if(!$(event.target).closest('.table-filter, .th-filter-btn span').length) {
        $('.table-filter').removeClass('active');
    }
});

// ログアウト確認
$('.logout').on('click', function() {
    $('.logout-modal').addClass('active')
});
$('.logout-buttons p').on('click', function() {
    $('.logout-modal').removeClass('active')
});

// セレクト開閉
$('.select-title').on('click', function() {
    var name = $(this).data('name');
    $('.select-buttons.' + name).toggleClass('active');
});
$(document).click(function(event) {
    if(!$(event.target).closest('.select-title').length) {
        $('.select-buttons').removeClass('active');
    }
});

// レポートのエリア絞込み
$('.select-button').on('click', function() {
    var name = $(this).data('name');
    var select = $(this).data(name);
    if (name == 'shop') {
        var id = $(this).data('value');
        $('.select-button.active.na').remove();
        $('.select-input.' + name).val(id);
        $('.datepickers').addClass('active');
        if ($('.input-date.from').hasClass('on') && $('.input-date.to').hasClass('on')) {
            $('.submit-label').addClass('active');
        }
    } else {
        $('.select-input.' + name).val(select);
    }
    $('.' + name + ' .title-span').text(select);
    $('.select-buttons.' + name).toggleClass('active');
    if (name !== 'group') {
        $('.select-title.' + name).addClass('on');
    }
    if (name == 'area') {
        $('.area .select-button').removeClass('selected');
        $(this).addClass('selected');
        $('.group .select-button').removeClass('selected');
        $('.group .title-span').text('グループ選択');
        $('.shop .title-span').text('店舗選択');
        $('.select-input.group').val();
        $('.shop .select-button').removeClass('active');
        $('[data-belong="' + select + '"]').addClass('active');
        $('.datepickers').addClass('active');
        if ($(this).data('area') == '全店') {
            $('.shop .select-button').removeClass('active');
            $('.shop .select-button').addClass('active');
        }
        if ($('.input-date.from').hasClass('on') && $('.input-date.to').hasClass('on')) {
            $('.submit-label').addClass('active');
        }
    } else if (name == 'group') {
        var area = $('.area .select-button.selected').data('area');
        var group = $(this).data('group');
        if ($('.select-input.area').val() == '') {
            $('.msg').html('<li>エリア未選択</li>');
        } else if ($('.select-input.area').val() == '全店') {
            $('.shop .select-button').removeClass('active');
            $('.shop .select-button').addClass('active');
        } else {
            if ($('.select-input.area').val() !== '') {
                area = $('.select-input.area').val();
                $('.datepickers').addClass('active');
            }
            $('.group .select-button').removeClass('selected');
            $(this).addClass('selected');
            $('.shop .select-button').removeClass('active');
            $('[data-grouping="' + area + group + '"]').addClass('active');
        }
    }
    if ($('.select-title.area').data('areaPoint') == 1 && $('.input-date.from').data('fromPoint') == 2 && $('.input-date.to').data('toPoint') == 2) {
        $('.submit-btn').addClass('active');
    }
});

$('.report .input-date').change(function() {
    $(this).addClass('on');
    if ($('.select-title.area').hasClass('on') || $('.select-title.shop').hasClass('on')) {
        if ($('.input-date.from').hasClass('on') && $('.input-date.to').hasClass('on')) {
            $('.submit-label').addClass('active');
        }
    }
});

$('.select-title.shop').on('click', function() {
    if (!$('.shop .select-button').hasClass('active')) {
        var p = '<p class="select-button active na">該当なし</p>';
        $('.select-buttons.shop').append(p);
    }
});


// レポート凡例クリックイベント
$('.legend1').on('click', function() {
    var ancestor = $(this).parent().parent().parent().parent().data('section');
    var parent = $(this).parent().parent().parent().data('items');
    var path = '.' + ancestor + ' .' + parent + ' ';
    if ($(path + '.legend1').hasClass('off') == false && $(path + '.legend2').hasClass('off')) {
        legendReset(path);
    } else {
        $(path + '.legend1').removeClass('off');
        $(path + '.legend2').removeClass('off');
        $(path + '.legend2').addClass('off');
        $(path + '.chart-bar').removeClass('hidden');
        $(path + '.chart-bar').addClass('hidden');
        $(path + '.bar1').removeClass('reduction');
        $(path + '.bar1').addClass('restate');
        $(path + '.bar2').removeClass('restate');
    }
});
$('.legend2').on('click', function() {
    var ancestor = $(this).parent().parent().parent().parent().data('section');
    var parent = $(this).parent().parent().parent().data('items');
    var path = '.' + ancestor + ' .' + parent + ' ';
    if ($(path + '.legend1').hasClass('off')) {
        legendReset(path);
    } else {
        $(path + '.legend2').removeClass('off');
        $(path + '.legend1').removeClass('off');
        $(path + '.legend1').addClass('off');
        $(path + '.chart-bar').removeClass('hidden');
        $(path + '.chart-bar').addClass('hidden');
        $(path + '.bar1').addClass('reduction');
        $(path + '.bar2').addClass('restate');
    }
});
function legendReset(path) {
    $(path + '.legend1').removeClass('off');
    $(path + '.legend2').removeClass('off');
    $(path + '.chart-bar').removeClass('hidden');
    $(path + '.bar1').removeClass('reduction');
    $(path + '.bar1').removeClass('restate');
    $(path + '.bar2').removeClass('restate');
};


// リロード
// $(function(){
//     setTimeout(() => {
//        location.reload();
//    }, 10000); //5秒
//  });

// ローディング処理
$(window).on('load', function () {
    $('.loading').addClass('loading-end');
    setTimeout(function() {
        $('.loading').remove();
    }, 500);
});