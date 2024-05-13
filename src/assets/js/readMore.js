function initReadMore(readMoreCandidates) {
	readMoreCandidates = document.querySelectorAll('.wy > p');

	readMoreCandidates.forEach((el) => {
		const containsMoreComment = el.innerHTML.includes('<!--more-->');
		const hasMoreIdChild = el.querySelector('[id^="more-"]') !== null;

		if (!(containsMoreComment || hasMoreIdChild)) {
			return;
		}

		const readMoreTemplate = document.getElementById(
			'readMoreContainerTemplate'
		);
		const readMoreClone = readMoreTemplate.content.cloneNode(true);

		el.parentNode.insertBefore(readMoreClone, el.nextSibling);
		const readMoreElement = el.nextElementSibling;
		const readMoreButton = readMoreElement.querySelector('.rm__btn');

		el.remove();

		const followingElements = [];
		let nextElement = readMoreElement.nextElementSibling;

		document.addEventListener('DOMContentLoaded', () => {
			const menuItems = document.querySelectorAll('.rm--vis > *, .rm--hid > *');

			const resetAndTriggerAnimations = () => {
				let currentDelay = 1;
				let currentList = null;

				menuItems.forEach((item) => {
					if (item.parentElement !== currentList) {
						currentList = item.parentElement;
						currentDelay = 1;
					}

					const animationDelay = currentDelay.toFixed(1);
					item.style.setProperty('--animation-delay', `${animationDelay}s`);

					currentDelay += 0.2;

					item.style.animation = 'none';
					void item.offsetWidth;
					item.style.animation = null;
				});
			};

			setTimeout(() => {
				resetAndTriggerAnimations();
			}, 10);
		});

		while (nextElement) {
			followingElements.push({
				element: nextElement,
				height: nextElement.offsetHeight,
			});
			nextElement.classList.add('rm--hid');
			nextElement = nextElement.nextElementSibling;
		}

		readMoreButton.addEventListener('click', () => {
			readMoreElement.remove();

			followingElements.forEach(({ element, height }) => {
				element.classList.replace('rm--hid', 'rm--vis');
				element.style.maxHeight = height + 'px';
			});
		});
	});
}
