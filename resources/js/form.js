$('.checkbox-label input').on('click', function() {
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
    $(this).parent().toggleClass('checked');
});


// 二重送信防止
$('.submit-btn').click(function() {
    $('.send-modal').addClass('active');
    $('.submit-btn').prop('disabled', true);
    $('form').submit();
});

$(window).on('load', function () {
    $('.loading').addClass('loading-end');
    setTimeout(function() {
        $('.loading').remove();
    }, 500);
});
