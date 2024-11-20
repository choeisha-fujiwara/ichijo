$(function() {
    $('.checkbox-label input').on('click', function() {
        var options = '.' + $(this).parent().data('options');
        $(options).toggleClass('active');
        $('.option01').hasClass('active')
         ? $('.option01.required input:first-of-type').attr('required', true)
         : $('.option01.required input:first-of-type').attr('required', false);
         $(this).parent().toggleClass('checked');
    });
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
