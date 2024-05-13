document.addEventListener('DOMContentLoaded', () => {
	const dialog = document.getElementById('ccd');

	/** utilities functions */
	const isCookieConsent = () => !getCookie('cookie_consent') == '';
	const isMarketingConsent = () => getCookie('cookie_consent') == 2;

	const showCookieBar = () => {
		dialog.showModal();
		document.documentElement.classList.add('stop-scrolling');
	};

	const removeCookiesBar = () => {
		document.documentElement.classList.remove('stop-scrolling');
		dialog.close();
		dialog.remove();
	};

	const toggleDetailsCookiesBar = () => {
		let cookiesBarDetails = document.querySelector('.ccd__set');
		cookiesBarDetails.classList.toggle('ccd__set--active');
	};

	/** business logic **/
	const checkCookieConsent = (debug = false) => {
		const gtagInit = () => runGtag(debug);

		if (
			dialog &&
			!isCookieConsent() &&
			window.location.href != ga4.policy_page_url
		) {
			/* dostosuj */
			let settingsButton = document.getElementById('settings-button');
			settingsButton.addEventListener('click', toggleDetailsCookiesBar);

			/* dostosuj - zapisz */
			let saveSettingsButton = document.getElementById('save-settings');
			saveSettingsButton.addEventListener('click', () => {
				if (document.querySelector('#marketing').checked) {
					setCookie('cookie_consent', 2, 365);
					gtagInit();
				}

				setCookie('cookie_consent', 0, 365);
				removeCookiesBar();
			});

			/* zgoda na wszystko */
			let acceptAllButton = document.getElementById('accept-all');
			acceptAllButton.addEventListener('click', () => {
				setCookie('cookie_consent', 2, 365);
				removeCookiesBar();
				if (debug)
					console.log('Zgoda na wszystko, cookie_consent: 2, gtagInit()');
				gtagInit();
			});

			/* odrzuć wszystko */
			// let declineAllButton = document.getElementById('decline-all');
			// declineAllButton.addEventListener('click', () => {
			// 	removeCookiesBar();
			// 	setCookie('cookie_consent', 0, 365);
			// 	if (debug) console.log('Brak zgody, cookie_consent: 0');
			// });

			showCookieBar();
		} else {
			if (isMarketingConsent()) {
				if (debug) console.log('Zgoda już udzielona, gtagInit()');
				gtagInit();
			} else {
				if (debug && ga4.policy_page_url) {
					console.log(
						'(wyjątek) Zgoda nie udzielona, strona polityki prywatności'
					);
				} else if (debug) console.log('Zgoda już udzielona, bez marketingowej');
			}
		}
	};

	checkCookieConsent(ga4.data.debug ?? false);

	/** block dialog escaping **/
	if (dialog) {
		dialog.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') event.preventDefault();
		});
	}

	/** jeśli już jest **/
	// if ((isCookieConsent())) {
	//   gtagInit();

	//   dialog.remove();
	// }

	// cookiesBar.remove();

	// document.querySelectorAll(".cct").forEach((el) => {
	//   el.addEventListener("keydown", (event) => {
	//     if (event.key === "Enter" || event.key === " ") {
	//       event.preventDefault();
	//       el.click();
	//     }
	//   });
	// });

	// if (getCookie("newsletter") !== "accept") {
	//   window.setTimeout(() => {
	//     document.getElementsByClassName("newsletter-opener")[0].click();
	//   }, 3000);
	// }
});
