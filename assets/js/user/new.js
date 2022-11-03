import '../../css/user/new.scss';

import $ from 'jquery';

$(document).ready(function(){
   // We do this to avoid autocompletion from the browser. We put readonly by default on html and remove it here
   if($('#user_password_first').length > 0) {
      $('#user_password_first').removeAttr('readonly');
   }
   if($('#user_password_second').length > 0) {
      $('#user_password_second').removeAttr('readonly');
   }
});