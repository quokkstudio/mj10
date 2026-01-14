/*
 * Copyright (C) 2025 NHN COMMERCE. - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source and binary forms via any medium
 * is strictly prohibited.
 */

$(document).ready(function () {
    var $googleLoginBtn = $('.js_btn_google_login');

    if ($googleLoginBtn.length > 0) {
        $googleLoginBtn.click(function () {
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "../member/google/google_login.php";
            form.target = "googleLogin";

            var inputType = document.createElement("input");
            inputType.type = "hidden";
            inputType.name = "googleType";
            inputType.value = $googleLoginBtn.data('google-type');
            form.appendChild(inputType);

            var inputUrl = document.createElement("input");
            inputUrl.type = "hidden";
            inputUrl.name = "returnUrl";
            inputUrl.value = $googleLoginBtn.data('return-url');
            form.appendChild(inputUrl);

            form.style.display = "none";
            document.body.appendChild(form);

            var win = window.open("", "googleLogin", "width=630, height=560, resizable=no, scrollbars=no");
            form.submit();
            win.focus();
            document.body.removeChild(form);

            return win;
        });
    }

    if (typeof googleProfile !== 'undefined') {
        if (googleProfile.id){
            $('input[name="memId"]').filter(function() {
                return !this.value.trim();
            }).val(googleProfile.id).addClass('ignore');
        }
        if (googleProfile.name){
            $('input[name="memNm"]').filter(function(){
                return !this.value.trim();
            }).val(googleProfile.name);
        }
        if (googleProfile.email){
            $('input[name="email"]').filter(function() {
                return !this.value.trim();
            }).val(googleProfile.email);
        }

        var $memPw = $('input[name="memPw"]');
        if ($memPw.length > 0) {
            $memPw.addClass('ignore');
        }
        var $memPwRe = $('input[name="memPwRe"]');
        if ($memPwRe.length > 0) {
            $memPwRe.addClass('ignore');
        }
    }

    var $snsConnectBtn = $('.js_btn_sns_connect');
    var $disconnectBtn = $('.js_btn_sns_disconnect');

    // 공통 팝업 생성 함수
    function openGooglePopup(googleType) {
        var url = '../member/google/google_login.php?googleType=' + googleType;
        var win = gd_popup({
            url: url,
            target: "googleLogin",
            width: 630,
            height: 560,
            resizable: "no",
            scrollbars: "no"
        });
        win.focus();
        return win;
    }

    if ($snsConnectBtn.length > 0) {
        $snsConnectBtn.click(function () {
            if ($(this).data('sns') == 'google') {
                return openGooglePopup('connect');
            }
        });
    }

    if (typeof snsConnection !== 'undefined' && $disconnectBtn.length > 0) {
        $disconnectBtn.click(function () {
            if ($(this).data('sns') == 'google') {
                if (confirm(__('계정 연결을 해제하시겠습니까?'))) {
                    return openGooglePopup('disconnect');
                }
            }
        });
    }
});

function confirmJoin(message, location) {
    if (confirm(message)) {
        window.location.href = location;
    }
}
