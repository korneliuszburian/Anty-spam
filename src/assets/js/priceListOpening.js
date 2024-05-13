function toggleSummaryText(summaryElement) {
	let toggleTextElement = summaryElement.querySelector('.toggle-text');
	if (toggleTextElement) {
		if (toggleTextElement.textContent.trim() === 'Rozwiń') {
			toggleTextElement.textContent = 'Zwiń';
		} else {
			toggleTextElement.textContent = 'Rozwiń';
		}
	}
}

document.addEventListener('click', function (event) {
	if (
		event.target.nodeName === 'SUMMARY' ||
		event.target.parentElement.nodeName === 'SUMMARY'
	) {
		event.preventDefault();
		const detailsElement = event.target.closest('.details');
		if (detailsElement) {
			const isOpen = detailsElement.classList.contains('open');
			detailsElement.classList.toggle('open', !isOpen);
			toggleSummaryText(event.target);
		}
	}
});

document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('summary').forEach(function (summaryElement) {
		if (summaryElement.querySelector('.toggle-text')) {
			summaryElement
				.querySelector('.prl__cat-btn')
				.addEventListener('click', function () {
					const detailsElement = summaryElement.closest('.details');
					if (detailsElement) {
						const isOpen = detailsElement.classList.contains('open');
						detailsElement.classList.toggle('open', !isOpen);
						toggleSummaryText(summaryElement);
					}
				});
		}
	});
});
