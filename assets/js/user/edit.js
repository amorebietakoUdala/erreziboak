import '../../css/user/edit.scss';

import $ from 'jquery';

$(document).ready(function(){
    $('.js-save').on('click',function (e) {
        console.log('saveButtonClicked!');
        $(document.user).attr('action', $(e.currentTarget).data("url"));
        document.user.submit();
    });
    // We do this to avoid autocompletion from the browser. We put readonly by default on html and remove it here
    if($('#user_password_first').length > 0) {
        $('#user_password_first').removeAttr('readonly');
    }
    if($('#user_password_second').length > 0) {
        $('#user_password_second').removeAttr('readonly');
    }
});