$(function() {
    $('.checkbox-label input').on('click', function() {
        var options = '.' + $(this).parent().data('options');
        $(options).toggleClass('active');
        /*「餃子の味について」が必須の場合
        $(options).hasClass('active')
        ? $(options + ' input:first-of-type').attr('required', true)
        : $(options + ' input:first-of-type').attr('required', false);
        */
        //「餃子の味について」が任意の場合
        $('.option01').hasClass('active')
        ? $('.option01.required input:first-of-type').attr('required', true)
        : $('.option01.required input:first-of-type').attr('required', false);
        //
        $(this).parent().toggleClass('checked');
    });
});

/*「本日ご注文メニュー」が必須の場合に有効化（有効化の場合、全チェックボックスにrequired追加）
$(function() {
    var checkboxes = $('.checkbox-label input');
    checkboxes.on('change', () => {
        var checked = checkboxes.filter(':checked');
        checked.length > 0
        ? checkboxes.attr('required', false)
        : checkboxes.attr('required', true);
    });
    var submit = $('.submit-btn');
    var msg = '1つ以上選択してください。';
    submit.on('click', () => {
        checkboxes.on('invalid', (e) => {
            var isInvalid = e.target.validity.valueMissing;
            isInvalid 
            ? e.target.setCustomValidity(msg)
            : e.target.setCustomValidity('');
        });
    });
});
*/