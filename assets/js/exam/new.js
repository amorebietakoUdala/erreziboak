import '../../css/exam/new.scss';

import $ from 'jquery';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import getAppBase from '../common/app_base';



$(document).ready(function(){
    console.log('Exam new');
    Routing.setRoutingData(routes);
    $('.js-back').on('click', function(e) {
        e.preventDefault();
        var url = e.currentTarget.dataset.url;
        document.location.href = url;
    });
    $('.js-category').on('change',function(e) {
        var url = getAppBase() + Routing.generate('api_category', { id: $(e.currentTarget).val()});
        var price = null;
        $.ajax({
            url: url,
            success: function (json) {
                var category = JSON.parse(json);
                var serviceURL = category.data.concept.service_url;
                if (serviceURL !== undefined) {
                    $.ajax({
                        url: serviceURL,
                        success: function (price) {
                            console.log(price);
                            $('.js-quantity').text(price);
                        }
                    });
                } else {
                    price = category.data.concept.unitary_price;
                    $('.js-quantity').text(price);
                }
            }
        });
    })
});
$(function () {
    // Forces getting price for the first category on load
    $(".js-category").change();
});
