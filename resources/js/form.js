// チェックボックス
$('.checkbox-label input').on('click', function() {
    $(this).parent().toggleClass('checked');
});

// 炒飯選択挙動
$('.checkbox-label.fried-rice input').on('click', function() {
    var options = '.' + $(this).parent().data('options');
    $(options).toggleClass('active');
    if ($('.option01').hasClass('active')) {
        $('.option01.required input:first-of-type').attr('required', true);
        $('input.fried-rice').val('');
        $('input.fried-rice').prop('checked', false);
    } else {
        $('.option01.required input:first-of-type').attr('required', false);
        $('.option01.required input:first-of-type').prop('checked', false);
        $('input.fried-rice').val('未選択');
        $('input.fried-rice').prop('checked', true);
        $('textarea.fried-rice').val('');
    }
});

// 餃子選択挙動
$('.checkbox-label.gyoza input').on('click', function() {
    var options = '.' + $(this).parent().data('options');
    $(options).toggleClass('active');
    if ($('.option02').hasClass('active')) {
        $('.option02.required input:first-of-type').attr('required', true);
        $('input.gyoza').val('');
        $('input.gyoza').prop('checked', false);
    } else {
        $('.option02.required input:first-of-type').attr('required', false);
        $('.option02.required input:first-of-type').prop('checked', false);
        $('input.gyoza').val('未選択');
        $('input.gyoza').prop('checked', true);
    }
});

// 二重送信防止
$('.submit-btn').click(function() {
    $('.send-modal').addClass('active');
    $('.submit-btn').prop('disabled', true);
    $('form').submit();
});

$(window).on('load', function () {
    // 餃子と炒飯の未選択チェック
    if ($('input.gyoza').hasClass('err')) {
        $('input.gyoza').val('');
        $('input.gyoza').prop('checked', false);
    }
    if ($('input.fried-rice').hasClass('err')) {
        $('input.fried-rice').val('');
        $('input.fried-rice').prop('checked', false);
    }

    $('.loading').addClass('loading-end');
    setTimeout(function() {
        $('.loading').remove();
    }, 500);
});
