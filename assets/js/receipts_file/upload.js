import $ from 'jquery';

$(document).ready(function(){
    $('.js-receiptsType').on('change',function (e) {
        if ($('.js-receiptsType').val() === 'AU') {
            $('.js-receiptsFinishStatus').val('P');
        }
        if ($('.js-receiptsType').val() === 'ID') {
            $('.js-receiptsFinishStatus').val('P');
        }
        if ($('.js-receiptsType').val() === 'RB') {
            $('.js-receiptsFinishStatus').val('P');
        }
    });
});