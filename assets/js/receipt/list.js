import '../../css/receipt/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'tableexport.jquery.plugin/tableExport';
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import {createAlert} from '../common/alert';
const app_base = '/erreziboak';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js'

$(document).ready(function(){
    console.log('Receipt List OnReady!');
    Routing.setRoutingData(routes);
    console.log('Routes loaded!!!');
	var current_locale = $('html').attr("lang");

    $('#taula').bootstrapTable({
        cache : false,
        showExport: true,
        exportTypes: ['excel'],
        exportDataType: 'all',
        exportOptions: {
            fileName: "receipts",
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

	$(document).on('click','#js-autoPay',function(e){
        e.preventDefault();
        createAlert(e, app_base + Routing.generate('receipt_pay', { numeroRecibo: $(e.currentTarget).data('numerorecibo'), dni: $(e.currentTarget).data('dni'), email: $(e.currentTarget).data('email'), _locale: current_locale  } ));
    });

    if ($('#js-autoPay').length > 0) { 
        $('#js-autoPay')[0].click();
    }

    $(document).on('click','.js-btnPay',function(e){
        e.preventDefault();
        createAlert(e, app_base + Routing.generate('receipt_pay', { numeroRecibo: $(e.currentTarget).data('numerorecibo'), dni: $(e.currentTarget).data('dni'), email: $(e.currentTarget).data('email'), _locale: current_locale  } ));
    });

});

