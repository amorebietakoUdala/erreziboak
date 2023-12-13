import '../../css/concept/edit.css';

import $ from 'jquery';
import 'select2';

$(document).ready(function(){
    var selected_suffix = $('.js-suffix-select').val();
    var selected_accountingConcept = $('.js-accountingConcept-select').val();

    let locale = global.locale ?? document.getElementsByTagName("html")[0].getAttribute('lang');
    const options={
        theme: "bootstrap-5",
        language: locale,
    }
    $('.js-entity-select').select2(options);
    $('.js-suffix-select').select2(options);
    $('.js-accountingConcept-select').select2(options);
    
    if ( $('.js-entity-select').val() === null || $('.js-entity-select').val() === '' ) {
        $('.js-suffix-select-div').hide();
    }
    if ( $('.js-suffix-select').val() === null || $('.js-suffix-select').val() === '') {
        $('.js-accountingConcept-select-div').hide();
    }
    $('.js-entity-select').on('change', function(e) {
        $.ajax({
            url: $('.js-entity-select').data('suffix-select-url'),
            data: {
                entity: $('.js-entity-select').val()
            },
            success: function (json) {
                console.log(json);
                if ( json.length === 0 ) {
                    $('.js-suffix-select').empty();
                    $('.js-suffix-select-div').hide();
                } else {
                    $('.js-suffix-select-div').show();
                }
                
                $.each(json, function (key, entry) {
                  $('.js-suffix-select').append($('<option></option>').attr('value', entry.conceptoC60).text(entry.conceptoC60+'-'+entry.codigo+'-'+entry.descripcion));
                });
                $(".js-suffix-select").val(selected_suffix);
            }
        });
    });
    
        $('.js-suffix-select').on('change', function(e) {
        $.ajax({
            url: $('.js-suffix-select').data('accounting-concept-select-url'),
            data: {
                suffix: $('.js-suffix-select').val()
            },
            success: function (json) {
                if ( json.length === 0 ) {
                    $('.js-accountingConcept-select-div').hide();
                } else {
                    $('.js-accountingConcept-select-div').show();
                }
                $('.js-accountingConcept-select').empty();
                $.each(json, function (key, entry) {
                  $('.js-accountingConcept-select').append($('<option></option>').attr('value', entry.valorActual).text(entry.valorActual+'-'+entry.nombre));
                });
                $(".js-accountingConcept-select").val(selected_accountingConcept);
            }
        });
    });
});