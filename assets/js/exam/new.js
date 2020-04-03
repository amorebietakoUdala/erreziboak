import '../../css/exam/new.scss';

import $ from 'jquery';

$(document).ready(function(){
    console.log('Exam new');
    $('.js-back').on('click', function(e) {
        e.preventDefault();
        var url = e.currentTarget.dataset.url;
        document.location.href = url;
    });    
});