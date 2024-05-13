document.addEventListener('DOMContentLoaded', function () {
	const mh = document.getElementsByClassName('n-hb')[0];
	const nav_wr = document.getElementsByClassName('n__wr')[0];

	function toggleMenu() {
		if (window.innerWidth <= 1024) {
			mh.classList.toggle('n-hb--a');
			if (!nav_wr.classList.contains('n__wr--a')) {
				nav_wr.classList.add('n__wr--a');
			} else {
				nav_wr.classList.remove('n__wr--a');
			}
		}
	}

	if (mh) {
		mh.addEventListener('click', toggleMenu);

		window.addEventListener('resize', function () {
			if (window.innerWidth > 1024 && mh.classList.contains('n-hb--a')) {
				mh.classList.remove('n-hb--a');
				nav_wr.classList.remove('n__wr--a');
			}
		});
	}
});

document.addEventListener('DOMContentLoaded', () => {
	const setAnimationDelays = () => {
		const menuItems = document.querySelectorAll('.n__nav--wr > ul > li');
		menuItems.forEach((item, index) => {
			item.style.animationDelay = `${index * 0.1}s`;
		});
	};

	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.type === 'attributes' && mutation.attributeName === 'open') {
				if (mutation.target.hasAttribute('open')) {
					setAnimationDelays();
				}
			}
		});
	});

	const menuToggleElement = document.querySelector('.n--item');
	if (menuToggleElement) {
		observer.observe(menuToggleElement, { attributes: true });
	}
});

document.addEventListener('DOMContentLoaded', () => {
	const hamburger_button = document.querySelector('.n-hb');
	const expandable_elements = document.querySelectorAll('.n--exp');

	function toggleNav() {
		expandable_elements.forEach((element) => {
			const isOpen = element.hasAttribute('open');
			if (isOpen) {
				element.removeAttribute('open');
			} else {
				element.setAttribute('open', '');
			}
		});
	}

	hamburger_button.addEventListener('click', toggleNav);
});
