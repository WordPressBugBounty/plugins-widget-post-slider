(function ($) {
	'use strict';

	var prevArrow = '<div class="slick-prev" aria-label="Previous"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="15 18 9 12 15 6"></polyline></svg></div>';
	var nextArrow = '<div class="slick-next" aria-label="Next"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg></div>';

	$(function () {
		$('.sp-widget-post-slider-section').each(function () {
			var $slider = $(this);
			if ($slider.hasClass('slick-initialized')) {
				return;
			}
			$slider.slick({
				dots: false,
				infinite: true,
				slidesToShow: 1,
				slidesToScroll: 1,
				autoplay: true,
				speed: 600,
				autoplaySpeed: 4000,
				arrows: true,
				prevArrow: prevArrow,
				nextArrow: nextArrow
			});
		});
	});
}(jQuery));
