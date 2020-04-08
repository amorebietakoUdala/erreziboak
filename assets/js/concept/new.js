import '../../css/concept/new.scss';

import $ from 'jquery';
import 'select2';

$(document).ready(function(){
	$('.js-back').on('click',function(e){
		e.preventDefault();
		var url = e.currentTarget.dataset.url;
		document.location.href=url;
	});
    

    var selected_entity = $('.js-entity-select').val();
    var selected_suffix = $('.js-suffix-select').val();
    var selected_accountingConcept = $('.js-accountingConcept-select').val();
    console.log(selected_entity);
    console.log(selected_suffix);
    console.log(selected_accountingConcept);

    $('.js-entity-select').select2();
    $('.js-suffix-select').select2();
    $('.js-accountingConcept-select').select2();
    
    if ( $('.js-entity-select').val() === null || $('.js-entity-select').val() === '' ) {
        $('.js-suffix-select-div').hide();
    }
    if ( $('.js-suffix-select').val() === null || $('.js-suffix-select').val() === '') {
        $('.js-accountingConcept-select-div').hide();
    }
    $('.js-entity-select').on('change', function(e) {
        console.log('Entity change');
        $.ajax({
            url: $('.js-entity-select').data('suffix-select-url'),
            data: {
                entity: $('.js-entity-select').val()
            },
            success: function (json) {
                var json_data = JSON.parse(json);
                if ( json_data.length === 0 ) {
                    $('.js-suffix-select').empty();
                    $('.js-suffix-select-div').hide();
                } else {
                    $('.js-suffix-select-div').show();
                }
                
                $.each(json_data, function (key, entry) {
                  $('.js-suffix-select').append($('<option></option>').attr('value', entry.concepto_c60).text(entry.concepto_c60+'-'+entry.codigo+'-'+entry.descripcion));
                });
                $(".js-suffix-select").val(selected_suffix);
            }
        });
    });
    
        $('.js-suffix-select').on('change', function(e) {
       console.log('Suffix change');
       console.log($('.js-suffix-select').data('accounting-concept-select-url'));
        $.ajax({
            url: $('.js-suffix-select').data('accounting-concept-select-url'),
            data: {
                suffix: $('.js-suffix-select').val()
            },
            success: function (json) {
                console.log(json);
                var json_data = JSON.parse(json);
                if ( json_data.length === 0 ) {
                    $('.js-accountingConcept-select-div').hide();
                } else {
                    $('.js-accountingConcept-select-div').show();
                }
                console.log(json_data);
                $('.js-accountingConcept-select').empty();
                $.each(json_data, function (key, entry) {
                  $('.js-accountingConcept-select').append($('<option></option>').attr('value', entry.valor_actual).text(entry.valor_actual+'-'+entry.nombre));
                });
                $(".js-accountingConcept-select").val(selected_accountingConcept);
            }
        });
    });
});