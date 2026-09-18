(function () {
	'use strict';

	function initWidget(widget) {
		if (widget.dataset.mfwReady === '1') {
			return;
		}

		widget.dataset.mfwReady = '1';

		var launcher = widget.querySelector('.mfw-launcher');
		var panel = widget.querySelector('.mfw-panel');

		if (!launcher || !panel) {
			return;
		}

		function open() {
			widget.classList.add('is-open');
			launcher.setAttribute('aria-expanded', 'true');
			panel.setAttribute('aria-hidden', 'false');
		}

		function close() {
			widget.classList.remove('is-open');
			launcher.setAttribute('aria-expanded', 'false');
			panel.setAttribute('aria-hidden', 'true');
		}

		function isOpen() {
			return widget.classList.contains('is-open');
		}

		launcher.addEventListener('mouseenter', open);
		launcher.addEventListener('focus', open);

		launcher.addEventListener('click', function (event) {
			event.stopPropagation();
			open();
		});

		panel.addEventListener('mouseenter', open);

		document.addEventListener('click', function (event) {
			if (isOpen() && !widget.contains(event.target)) {
				close();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (isOpen() && 'Escape' === event.key) {
				close();
			}
		});

		var delay = parseInt(widget.dataset.autoOpen, 10);

		if (delay > 0 && !hasAutoOpened()) {
			window.setTimeout(function () {
				open();
				markAutoOpened();
			}, delay * 1000);
		}
	}

	function hasAutoOpened() {
		try {
			return '1' === window.sessionStorage.getItem('mfwAutoOpened');
		} catch (error) {
			return true;
		}
	}

	function markAutoOpened() {
		try {
			window.sessionStorage.setItem('mfwAutoOpened', '1');
		} catch (error) {
			return;
		}
	}

	function boot() {
		document.querySelectorAll('[data-mfw]').forEach(initWidget);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	window.mfwInit = boot;
})();
