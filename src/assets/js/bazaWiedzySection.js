document.addEventListener('DOMContentLoaded', function () {
	var buttons = document.querySelectorAll('.bg__term-btn');
	var titleElement = document.querySelector('.bg__title');
	var noElements = document.querySelector('.bg__none');
	var items = document.querySelectorAll('.bgi');
	var elementToScrollTo = document.getElementById('wpisy');

	buttons.forEach(function (button) {
		button.addEventListener('click', function () {
			var termId = this.getAttribute('data-term-id');
			var buttonText = this.textContent;

			if (titleElement) {
				titleElement.textContent = buttonText;
			}

			buttons.forEach(function (btn) {
				btn.classList.remove('bg__term-btn--active');
			});

			this.classList.add('bg__term-btn--active');

			var hasActiveItems = false;

			items.forEach(function (item) {
				var terms = item.getAttribute('data-terms');

				if (terms.includes(termId) || termId === '0') {
					item.classList.add('bgi--active');
					hasActiveItems = true;
					elementToScrollTo.scrollIntoView({
						behavior: 'smooth',
						block: 'start',
						inline: 'nearest',
					});
				} else {
					item.classList.remove('bgi--active');
				}
			});

			if (!hasActiveItems) {
				noElements.classList.remove('d-none');
				noElements.classList.add('d-flex');
			} else {
				noElements.classList.remove('d-flex');
				noElements.classList.add('d-none');
			}
		});
	});
});
