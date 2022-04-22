import '../../css/account/list.scss';

import $ from 'jquery';
import 'bootstrap-table';
import 'tableexport.jquery.plugin/tableExport.min';
import 'bootstrap-table/dist/extensions/export/bootstrap-table-export'
import 'bootstrap-table/dist/locale/bootstrap-table-es-ES';
import 'bootstrap-table/dist/locale/bootstrap-table-eu-EU';

import {createConfirmationAlert} from '../common/alert';

$(document).ready(function(){
    $('#taula').bootstrapTable({
        cache : false,
        showExport: true,
        exportTypes: ['excel'],
        exportDataType: 'all',
        exportOptions: {
            fileName: "account_checks",
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
        locale: $('html').attr('lang')+'-'+$('html').attr('lang').toUpperCase()
   });
    var $table = $('#taula');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    });
});

