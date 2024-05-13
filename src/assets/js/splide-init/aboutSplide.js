document.querySelectorAll('.notes-slider').forEach(function (slider) {
	var splideInstance = new Splide(slider, {
		type: 'loop',
		perPage: 2,
		focus: 'center',
		autoWidth: true,
		arrows: false,
		pagination: false,
		padding: {
			right: '2rem',
		},
		breakpoints: {
			1081: {
				destroy: true,
			},
			767: {
				perPage: 2,
			},
		},
		mediaQuery: 'min',
	}).mount();

	document
		.getElementById('splide__about--next')
		.addEventListener('click', function () {
			splideInstance.go('+1');
		});

	document
		.getElementById('splide__about--prev')
		.addEventListener('click', function () {
			splideInstance.go('-1');
		});
});
