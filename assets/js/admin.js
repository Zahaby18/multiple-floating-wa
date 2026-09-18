(function ($) {
	'use strict';

	$(function () {
		var form = document.getElementById('mfw-form');

		if (!form) {
			return;
		}

		var rows = document.getElementById('mfw-rows');
		var template = document.getElementById('tmpl-mfw-row');
		var stage = document.querySelector('.mfw-preview-stage');
		var widget = stage ? stage.querySelector('.mfw-widget') : null;
		var launcherIcon = widget ? widget.querySelector('.mfw-launcher-icon') : null;
		var defaultIcon = window.mfwAdmin ? window.mfwAdmin.defaultIcon : '';
		var nextIndex = window.mfwAdmin ? window.mfwAdmin.nextIndex : 1;

		function fieldValue(key) {
			var field = form.querySelector('[name="mfw_settings[' + key + ']"]');

			return field ? field.value.trim() : '';
		}

		function refresh() {
			if (!widget) {
				return;
			}

			var buttonColor = fieldValue('button_color') || '#0bb3b9';
			var headerColor = fieldValue('header_color') || buttonColor;
			var panelColor = fieldValue('panel_color') || '#ffffff';
			var textColor = fieldValue('text_color') || '#222222';
			var iconColor = fieldValue('icon_color') || '#ffffff';

			widget.style.setProperty('--mfw-button', buttonColor);
			widget.style.setProperty('--mfw-header', headerColor);
			widget.style.setProperty('--mfw-panel', panelColor);
			widget.style.setProperty('--mfw-text', textColor);
			widget.style.setProperty('--mfw-icon-color', iconColor);

			var size = fieldValue('size') || 'medium';
			var position = fieldValue('position') || 'bottom-right';

			['small', 'medium', 'large'].forEach(function (value) {
				widget.classList.toggle('mfw-size-' + value, value === size);
			});

			['bottom-right', 'bottom-left'].forEach(function (value) {
				widget.classList.toggle('mfw-position-' + value, value === position);
			});

			var title = fieldValue('header_title');
			var titleEl = widget.querySelector('.mfw-panel-title');

			if (titleEl) {
				titleEl.textContent = title || 'Chat with us';
			}

			var intro = fieldValue('panel_intro');
			var introEl = widget.querySelector('.mfw-intro');

			if (intro) {
				if (!introEl) {
					introEl = document.createElement('p');
					introEl.className = 'mfw-intro';
					introEl.textContent = intro;
					var body = widget.querySelector('.mfw-panel-body');
					body.insertBefore(introEl, body.firstChild);
				} else {
					introEl.textContent = intro;
				}
			} else if (introEl) {
				introEl.parentNode.removeChild(introEl);
			}

			if (launcherIcon) {
				var iconUrl = fieldValue('icon_url');

				if (iconUrl && (iconUrl.indexOf('http') === 0 || iconUrl.indexOf('/') === 0)) {
					launcherIcon.innerHTML = '';
					var img = document.createElement('img');
					img.className = 'mfw-custom-icon';
					img.alt = '';
					img.src = iconUrl;
					launcherIcon.appendChild(img);
				} else if (launcherIcon.querySelector('img') && defaultIcon) {
					launcherIcon.innerHTML = defaultIcon;
				}
			}

			refreshItems();
		}

		function refreshItems() {
			if (!widget) {
				return;
			}

			var body = widget.querySelector('.mfw-panel-body');

			if (!body) {
				return;
			}

			var proto = body.querySelector('.mfw-item');

			if (!proto) {
				return;
			}

			var protoNode = proto.cloneNode(true);
			var labels = [];

			rows.querySelectorAll('.mfw-row').forEach(function (row, index) {
				var labelField = row.querySelector('input[name$="[label]"]');
				var label = labelField ? labelField.value.trim() : '';
				labels.push(label || 'Button ' + (index + 1));
			});

			body.querySelectorAll('.mfw-item').forEach(function (item) {
				item.parentNode.removeChild(item);
			});

			if (!labels.length) {
				labels.push('Button 1');
			}

			var fragment = document.createDocumentFragment();

			labels.forEach(function (label) {
				var item = protoNode.cloneNode(true);
				var labelEl = item.querySelector('.mfw-item-label');

				if (labelEl) {
					labelEl.textContent = label;
				}

				item.setAttribute('href', '#');
				fragment.appendChild(item);
			});

			var introEl = body.querySelector('.mfw-intro');

			if (introEl) {
				body.insertBefore(fragment, introEl.nextSibling);
			} else {
				body.appendChild(fragment);
			}
		}

		$('#mfw-add-row').on('click', function () {
			if (!template || !rows) {
				return;
			}

			var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
			nextIndex += 1;
			rows.insertAdjacentHTML('beforeend', html);
			refresh();
		});

		$(rows).on('click', '.mfw-remove', function () {
			var row = this.closest('.mfw-row');

			if (row) {
				row.parentNode.removeChild(row);
			}

			refresh();
		});

		if ($.fn.sortable) {
			$(rows).sortable({
				handle: '.mfw-drag',
				axis: 'y',
				items: '.mfw-row',
				placeholder: 'mfw-sorting',
				forcePlaceholderSize: true,
				update: refresh
			});
		}

		if ($.fn.wpColorPicker) {
			$('.mfw-color').wpColorPicker({
				change: function () {
					window.setTimeout(refresh, 60);
				},
				clear: function () {
					window.setTimeout(refresh, 60);
				}
			});
		}

		form.addEventListener('input', refresh);
		form.addEventListener('change', refresh);

		refresh();
	});
})(jQuery);
