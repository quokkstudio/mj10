 /**
 * Created by LeeNamJu on 2015-11-03.
 */
var passwordListLayer = {
    element: $('#lyPassword'),
    btnEl: $('#lyPassword').find('.js_submit'),
    inputEl: $('#lyPassword').find('input[name=writerPw]'),
    show: function () {
        this.element.removeClass('dn');
        this.element.currentCenter();
        $('#layerDim').removeClass('dn');
        $('#scroll-left, #scroll-right').addClass('dim');
        $('.password_layer .ly_cont .text').focus();
    },
    close: function () {
        $('.password_layer .ly_close').trigger('click');
    }
}
