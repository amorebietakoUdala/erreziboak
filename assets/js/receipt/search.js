import '../../css/receipt/search.scss';

import $ from 'jquery';
import { createAlert } from '../common/alert';
import getAppBase from '../common/app_base';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

$(document).ready(function() {
    Routing.setRoutingData(routes);
    var current_locale = $('html').attr("lang");

    $(document).on('click', '#js-payMultiple', function(e) {
        e.preventDefault();
        createAlert(e, getAppBase() + Routing.generate('referenciac60_pay', { referencia: $(e.currentTarget).data('referencia'), email: $(e.currentTarget).data('email'), _locale: current_locale }));
    });
    if ($('#js-payMultiple').length > 0) {
        $('#js-payMultiple')[0].click();
    }
});