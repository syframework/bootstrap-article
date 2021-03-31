(function() {
	$('[data-toggle=offcanvas]').on('touchstart click', function(e) {
		$('.offcanvas-container').toggleClass('active');
		e.stopPropagation();
		e.preventDefault();
	});

	$('.offcanvas-right').on('touchstart click', function(e) {
		if ($('.offcanvas-container').hasClass('active')) {
			$('.offcanvas-container').toggleClass('active');
			e.preventDefault();
		}
	});
})();