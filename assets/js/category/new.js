import '../../css/category/new.scss';

import $ from 'jquery';

$(document).ready(function(){
	$('.js-back').on('click',function(e){
		e.preventDefault();
		var url = e.currentTarget.dataset.url;
		document.location.href=url;
	});
});