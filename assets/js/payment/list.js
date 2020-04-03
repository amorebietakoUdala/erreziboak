import '../../css/payment/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'tableexport.jquery.plugin/tableExport';
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import 'pc-bootstrap4-datetimepicker';
import {createAlert} from '../common/alert';
const app_base = '/erreziboak';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

$(document).ready(function(){
    console.log('Payment List OnReady!');
    Routing.setRoutingData(routes);
    console.log('Routes loaded!!!');
	var current_locale = $('html').attr("lang");

    $('#taula').bootstrapTable({
        cache : false,
        showExport: true,
        exportTypes: ['excel'],
        exportDataType: 'all',
        exportOptions: {
            fileName: "payments",
            ignoreColumn: ['options']
        },
        showColumns: false,
        pagination: true,
        search: true,
        striped: true,
        sortStable: true,
        pageSize: 10,
        pageList: [10,25,50,100],
        sortable: true,
       locale: $('html').attr('lang')+'-'+$('html').attr('lang').toUpperCase(),
   });
    var $table = $('#taula');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val(),
            });
        });
    });
	$.extend(true, $.fn.datetimepicker.defaults, {
	  icons: {
		time: 'fa fa-clock-o',
		date: 'fa fa-calendar',
		up: 'fa fa-arrow-up',
		down: 'fa fa-arrow-down',
		previous: 'fa fa-chevron-left',
		next: 'fa fa-chevron-right',
		today: 'fa fa-calendar-check-o',
		clear: 'fa fa-trash',
		close: 'fa fa-times'
	  }
	});	
	$('#payment_type_form_date_from').datetimepicker({
		locale: $('html').attr('lang')+'-'+$('html').attr('lang'),
		format: 'YYYY-MM-DD HH:mm',
	});
	$('#payment_type_form_date_to').datetimepicker({
		locale: $('html').attr('lang')+'-'+$('html').attr('lang'),
		format: 'YYYY-MM-DD HH:mm',
	});
});

