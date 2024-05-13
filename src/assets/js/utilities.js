var readMoreCandidates;

function waitForSplide(callback, element) {
	if (splideLoaded && document.querySelector(element)) {
		callback();
	} else {
		setTimeout(() => waitForSplide(callback, element), 100);
	}
}

function waitForElement(
	selector,
	callback,
	maxAttempts = 10,
	interval = 100,
	attempts = 0
) {
	const element = document.querySelector(selector);
	if (element) {
		callback(element);
	} else if (attempts < maxAttempts) {
		setTimeout(() => {
			waitForElement(selector, callback, maxAttempts, interval, attempts + 1);
		}, interval);
	} else {
		console.log(`Failed to find element: ${selector}`);
	}
}

function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
	let expires = 'expires=' + d.toUTCString();
	document.cookie = cname + '=' + cvalue + ';' + expires + ';path=/';
}

function getCookie(cname) {
	let name = cname + '=';
	let ca = document.cookie.split(';');
	for (let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return '';
}

function changeFontSize(event) {
	event.preventDefault();
	var newSize = event.target.getAttribute('data-font-size');
	document.body.style.fontSize = newSize;
	setCookie('font_size', newSize, 365);
}

document.addEventListener('DOMContentLoaded', (event) => {
	var savedFontSize = getCookie('font_size');
	if (savedFontSize) {
		document.body.style.fontSize = savedFontSize;
	}
});
