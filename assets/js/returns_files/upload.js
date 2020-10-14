import '../../css/returns_files/upload.scss';

import $ from 'jquery';

$(document).ready(function(){
    $('.js-receiptsType').on('change',function (e) {
        console.log('ReceiptsType Changed!');
        if ($('.js-receiptsType').val() === 'AU') {
            $('.js-receiptsFinishStatus').val('V');
        }
        if ($('.js-receiptsType').val() === 'ID') {
            $('.js-receiptsFinishStatus').val('P');
        }
        if ($('.js-receiptsType').val() === 'RB') {
            $('.js-receiptsFinishStatus').val('P');
        }
    });
});