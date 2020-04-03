import '../../css/payment/show.scss';

import $ from 'jquery';

$(document).ready(function(){
	$('#payment_type_form_back').on('click', function(e){
	    e.preventDefault();
	    window.location.href=e.currentTarget.dataset.url;
	});
});