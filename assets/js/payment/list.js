import '../../css/payment/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'tableexport.jquery.plugin/tableExport.min';
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';
import tempusDominus from '@eonasdan/tempus-dominus';
import customDateFormat from '@eonasdan/tempus-dominus/dist/plugins/customDateFormat';
const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';


$(document).ready(function(){
    Routing.setRoutingData(routes);
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
        locale: current_locale+'-'+current_locale.toUpperCase(),
   });
    var $table = $('#taula');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val(),
            });
        });
    });
    
    $('.js-datetime').each((i,v) => {
        tempusDominus.extend(customDateFormat);
        new tempusDominus.TempusDominus(v,{
            display: {
              buttons: {
                close: true,
              },
              components: {
                useTwentyfourHour: true,
                decades: false,
                year: true,
                month: true,
                date: true,
                hours: true,
                minutes: true,
                seconds: false,
              },
            },
            localization: {
              locale: current_locale,
              dayViewHeaderFormat: { month: 'long', year: 'numeric' },
              format: 'yyyy-MM-dd hh:mm',
            },
        });
    });
});

