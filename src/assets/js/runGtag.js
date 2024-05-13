/**
 * Rekurencja.com GA4 - Tag/Ads integration
 */

function runGtag(debug = false) {
	if (debug) {
		console.log('Run gTag');
		console.log(ga4);
	}

	if (!ga4.data.number) {
		if (debug) {
			console.log('No GA4 data');
		}
		return;
	}

	var rekga4_var_tagconfig = ga4.data.number;
	var rekga4_var_tel = `${rekga4_var_tagconfig}/${ga4.data.tel}`;
	var rekga4_var_mail = `${rekga4_var_tagconfig}/${ga4.data.mail}`;
	var rekga4_var_cf7 = `${rekga4_var_tagconfig}/${ga4.data.cf7}`;

	window.dataLayer = window.dataLayer || [];

	function gtag() {
		dataLayer.push(arguments);
	}
	gtag('js', new Date());
	gtag('config', rekga4_var_tagconfig);

	(function () {
		var po = document.createElement('script');
		po.type = 'text/javascript';
		po.async = true;
		po.src =
			'https://www.googletagmanager.com/gtag/js?id=' + rekga4_var_tagconfig;
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(po, s);
	})();

	document.addEventListener('DOMContentLoaded', function () {
		var rekga4tel = document.querySelectorAll(['a[href^="tel"]']);
		if (debug) console.log([`a[href^="tel"]:`, rekga4tel]);
		if (rekga4tel.length > 0) {
			rekga4tel.forEach(function (testx) {
				testx.addEventListener(
					'mouseover',
					(event) => {
						gtag('event', 'conversion', {
							send_to: rekga4_var_tel,
						});
						if (debug) console.log('Rekurencja - GA4 - Ads - telephone action');
					},
					false
				);
			});
		}

		var rekga4mail = document.querySelectorAll('a[href^="mailto"]');
		if (debug) console.log([`a[href^="mailto"]:`, rekga4mail]);
		if (rekga4mail.length > 0) {
			rekga4mail.forEach(function (testx) {
				testx.addEventListener(
					'mouseover',
					(event) => {
						gtag('event', 'conversion', {
							send_to: rekga4_var_mail,
						});
						if (debug) console.log('Rekurencja - GA4 - Ads - e-mail action');
					},
					false
				);
			});
		}

		var rekga4cf7 = document.querySelector('.wpcf7');
		if (debug) console.log([`.wpcf7:`, rekga4cf7]);
		if (rekga4cf7.length > 0) {
			rekga4cf7.addEventListener(
				'wpcf7submit',
				function (event) {
					gtag('event', 'conversion', {
						send_to: rekga4_var_cf7,
					});
					if (debug) console.log('Rekurencja - GA4 - Ads - cf7 post action');
				},
				false
			);
		}
	});
}
