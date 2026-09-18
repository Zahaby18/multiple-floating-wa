(function () {
	'use strict';

	function initWidget(widget) {
		if (widget.dataset.mfwReady === '1') {
			return;
		}

		widget.dataset.mfwReady = '1';

		var launcher = widget.querySelector('.mfw-launcher');
		var panel = widget.querySelector('.mfw-panel');
		var closeBtn = widget.querySelector('.mfw-close');
		var hoverCapable = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

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

		launcher.addEventListener('click', function (event) {
			event.stopPropagation();

			if (widget.classList.contains('is-open')) {
				close();
			} else {
				open();
			}
		});

		if (hoverCapable) {
			launcher.addEventListener('mouseenter', open);
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', function (event) {
				event.stopPropagation();
				close();
			});
		}

		document.addEventListener('click', function (event) {
			if (!widget.classList.contains('is-open')) {
				return;
			}

			if (!widget.contains(event.target)) {
				close();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && widget.classList.contains('is-open')) {
				close();
			}
		});

		var delay = parseInt(widget.dataset.autoOpen, 10);

		if (delay > 0 && !sessionStorage.getItem('mfwAutoOpened')) {
			window.setTimeout(function () {
				open();
				sessionStorage.setItem('mfwAutoOpened', '1');
			}, delay * 1000);
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
