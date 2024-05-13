const cards = document.querySelectorAll('.s-card');
function handleServiceCardsClicks() {
	if (!cards) {
		// console.log("Brak elementów .s-card");
		return;
	}

	cards.forEach((card) => {
		card.addEventListener('click', (event) => {
			let label = event.target.innerText;

			// console.log(`${label}`);

			let serviceGroup = event.target.closest('.services__i');
			let serviceHeading = serviceGroup.querySelector('.services__h');
			let serviceLabel =
				serviceHeading.innerText == 'Usługi powiązane'
					? 'Inne'
					: serviceHeading.innerText;

			/* Wait for select element */
			waitForElement('#form-project-type', (selectElement) => {
				Array.from(selectElement.options).forEach(function (optionElement) {
					let isValueMatch = optionElement.value === serviceLabel;
					optionElement.selected = isValueMatch;
				});
			});

			/* Wait for textarea element */
			waitForElement('#form-comments', (textareaElement) => {
				textareaElement.value = `Dotyczy usługi: ${label}`;
			});
		});
	});
}

handleServiceCardsClicks();

document.addEventListener('DOMContentLoaded', () => {
	const button = document.querySelector('.services--btn__expand');
	let currentIndex = 0;
	let hiddenGroups = document.querySelectorAll('.services__hidden');

	button.addEventListener('click', function () {
		if (currentIndex < hiddenGroups.length) {
			hiddenGroups[currentIndex].classList.add('services__active');
			currentIndex++;
		}

		if (currentIndex >= hiddenGroups.length) {
			button.style.display = 'none';
		}
	});
});
