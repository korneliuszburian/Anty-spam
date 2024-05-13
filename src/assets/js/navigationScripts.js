const nav = document.getElementsByClassName('n')[0];

function navOffset() {
	if (nav) {
		document.body.style.paddingTop = `${nav.offsetHeight}px`;
	}
}
navOffset(), window.addEventListener('resize', navOffset);

let navTop = nav.offsetTop;
window.onscroll = function () {
	window.scrollY > navTop + 100
		? nav.classList.add('n--c')
		: nav.classList.remove('n--c');
};

if (nav && nav.clientHeight) {
	document.documentElement.style.setProperty(
		'--navHeight',
		`${nav.clientHeight}px`
	);
}

document.body.addEventListener('click', handleBodyClick);

function handleBodyClick(event) {
	const menuArrow =
		event.target.closest('.menu__arrow') ?? event.target.closest('.menu-arrow');
	const zeroLevel = event.target.closest('.zero-level');
	const menuItemWithChildren = event.target.closest('.menu-item-has-children');

	if (menuArrow && menuItemWithChildren) {
		event.preventDefault();
		toggleSubMenus(menuItemWithChildren);
	} else if (zeroLevel) {
		const parentLink = zeroLevel.closest('a');
		if (parentLink) {
			window.location.href = parentLink.getAttribute('href');
		}
	} else {
		closeAllActiveMenus();
	}
}

function closeAllActiveMenus() {
	document
		.querySelectorAll('.menu-item-has-children.active')
		.forEach((menu) => {
			menu.classList.remove('active');
		});
}

function toggleSubMenus(currentElem) {
	const siblings = currentElem.parentElement.querySelectorAll(
		'.menu-item-has-children'
	);
	siblings.forEach((sib) => {
		if (sib !== currentElem) {
			sib.classList.remove('active');
		}
	});
	currentElem.classList.toggle('active');
}
