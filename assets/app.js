import './bootstrap.js';
import $ from 'jquery';

import './bootstrap';

import 'bootstrap';
import 'popper.js';

import './css/app.css';

$(document).ready(function(){
	$('.js-back').on('click',function(e){
		e.preventDefault();
		var url = e.currentTarget.dataset.url;
		document.location.href=url;
	});
});