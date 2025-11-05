/* JS Document */

/******************************

[Table of Contents]

1. Vars and Inits
2. Set Header
3. Init Menu
4. Init Date Picker
5. Init Booking Slider


******************************/

$(document).ready(function()
{
	"use strict";

	/* 

	1. Vars and Inits

	*/

	var header = $('.header');
	var ctrl = new ScrollMagic.Controller();

	setHeader();

	$(window).on('resize', function()
	{
		setHeader();

		setTimeout(function()
		{
			$(window).trigger('resize.px.parallax');
		}, 375);
	});

	$(document).on('scroll', function()
	{
		setHeader();
	});

	initMenu();
	initDatePicker();
	initBookingSlider();
	prefillFromQuery();

	/* 

	2. Set Header

	*/

	function setHeader()
	{
		if($(window).scrollTop() > 91)
		{
			header.addClass('scrolled');
		}
		else
		{
			header.removeClass('scrolled');
		}
	}

	/* 

	3. Init Menu

	*/

	function initMenu()
	{
		if($('.menu').length)
		{
			var menu = $('.menu');
			var hamburger = $('.hamburger');
			var close = $('.menu_close');

			hamburger.on('click', function()
			{
				menu.toggleClass('active');
			});

			close.on('click', function()
			{
				menu.toggleClass('active');
			});
		}
	}

	/* 

	4. Init Date Picker

	*/

	function initDatePicker()
	{
		if($('.datepicker').length)
		{
			var datePickers = $('.datepicker');
			datePickers.each(function()
			{
				var dp = $(this);
				// Uncomment to use date as a placeholder
				// var date = new Date();
				// var dateM = date.getMonth() + 1;
				// var dateD = date.getDate();
				// var dateY = date.getFullYear();
				// var dateFinal = dateM + '/' + dateD + '/' + dateY;
				var placeholder = dp.data('placeholder');
				dp.val(placeholder);
				dp.datepicker();
			});
		}	
	}

	/* 

	5. Init Booking Slider

	*/

	function initBookingSlider()
	{
		if($('.booking_slider').length)
		{
			var bookingSlider = $('.booking_slider');
			bookingSlider.owlCarousel(
			{
				items:3,
				autoplay:true,
				autoplayHoverPause:true,
				loop:false,
				smartSpeed:1200,
				dots:false,
				nav:false,
				margin:30,
				responsive:
				{
					0:{items:1},
					768:{items:2},
					992:{items:3}
				}
			});
		}
	}

});

/*
 Prefill booking form fields from URL query parameters
 Supports checkIn, checkOut (mm/dd/yyyy or yyyy-mm-dd) and guests
*/
function prefillFromQuery()
{
	var params = new URLSearchParams(window.location.search);
	var checkIn = params.get('checkIn');
	var checkOut = params.get('checkOut');
	var guests = params.get('guests');
	var roomType = params.get('roomType');

	function normalizeDate(d)
	{
		if(!d) return '';
		var mmddyyyy = /^([0-1]?\d)\/([0-3]?\d)\/(\d{4})$/;
		var iso = /^(\d{4})-(\d{2})-(\d{2})$/;
		var m;
		if((m = d.match(mmddyyyy)))
		{
			var mm = m[1].padStart(2,'0');
			var dd = m[2].padStart(2,'0');
			return m[3] + '-' + mm + '-' + dd;
		}
		if(d.match(iso))
		{
			return d;
		}
		return d; // fallback
	}

	if(checkIn){ $('#checkIn').val(normalizeDate(checkIn)); }
	if(checkOut){ $('#checkOut').val(normalizeDate(checkOut)); }
	if(guests){ $('#guests').val(guests); }
	if(roomType){
		var setVal = function(){ var $sel = $('#roomType'); if($sel.length){ $sel.val(roomType); } };
		setVal();
		setTimeout(setVal, 300);
		setTimeout(setVal, 800);
	}
}