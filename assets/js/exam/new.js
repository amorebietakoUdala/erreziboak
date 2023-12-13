import $ from 'jquery';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import getAppBase from '../common/app_base';

$(document).ready(function(){
    Routing.setRoutingData(routes);
    $('.js-category').on('change',function(e) {
        const id = $(e.currentTarget).val();
        if ( id != "" ) {
            var url = getAppBase() + Routing.generate('api_category', { id: $(e.currentTarget).val()});
            var price = null;
            $.ajax({
                url: url,
                success: function (category) {
                    var serviceURL = category.data.concept.serviceURL;
                    if (serviceURL !== undefined) {
                        $.ajax({
                            url: serviceURL,
                            success: function (price) {
                                $('.js-quantity').text(price);
                            }
                        });
                    } else {
                        price = category.data.concept.unitaryPrice;
                        $('.js-quantity').text(price);
                    }
                }
            });
        }
    })
});
$(function () {
    // Forces getting price for the first category on load
    $(".js-category").change();
});
