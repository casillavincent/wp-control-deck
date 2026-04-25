(function () {
	'use strict';

	function getStatus(length, minimum, maximum) {
		if (length >= minimum && length <= maximum) {
			return 'is-good';
		}

		if (length > 0) {
			return 'is-warning';
		}

		return '';
	}

	function updateCounter(input, output) {
		var type = input.getAttribute('data-wpseo-console-counter');
		var length = input.value.length;
		var minimum = 'title' === type ? 50 : 120;
		var maximum = 'title' === type ? 60 : 160;

		output.className = 'wpseo-console-counter ' + getStatus(length, minimum, maximum);
		output.textContent = length + ' characters. Recommended: ' + minimum + '-' + maximum + '.';
	}

	function updatePreview(wrapper) {
		var title = wrapper.querySelector('#_wpseo_console_title');
		var description = wrapper.querySelector('#_wpseo_console_description');
		var previewTitle = wrapper.querySelector('[data-wpseo-console-preview-title]');
		var previewDescription = wrapper.querySelector('[data-wpseo-console-preview-description]');
		var fallbackTitle = document.querySelector('#title');

		if (previewTitle && title) {
			previewTitle.textContent = title.value || (fallbackTitle ? fallbackTitle.value : '');
		}

		if (previewDescription && description) {
			previewDescription.textContent = description.value;
		}
	}

	function initialize(wrapper) {
		var counterInputs = wrapper.querySelectorAll('[data-wpseo-console-counter]');

		counterInputs.forEach(function (input) {
			var output = wrapper.querySelector('[data-wpseo-console-counter-output="' + input.getAttribute('data-wpseo-console-counter') + '"]');

			if (!output) {
				return;
			}

			input.addEventListener('input', function () {
				updateCounter(input, output);
				updatePreview(wrapper);
			});

			updateCounter(input, output);
		});

		wrapper.addEventListener('input', function (event) {
			if ('_wpseo_console_title' === event.target.id || '_wpseo_console_description' === event.target.id) {
				updatePreview(wrapper);
			}
		});

		updatePreview(wrapper);
	}

	function initializeImageSelect(wrapper) {
		var input = wrapper.querySelector('[data-wpseo-console-image-input]');
		var preview = wrapper.querySelector('[data-wpseo-console-image-preview]');
		var selectButton = wrapper.querySelector('[data-wpseo-console-image-button]');
		var removeButton = wrapper.querySelector('[data-wpseo-console-image-remove]');
		var frame;

		if (!input || !preview || !selectButton || !removeButton || !window.wp || !window.wp.media) {
			return;
		}

		function renderPreview(url) {
			preview.innerHTML = '';

			if (!url) {
				return;
			}

			var image = document.createElement('img');
			image.alt = '';
			image.src = url;
			preview.appendChild(image);
		}

		selectButton.addEventListener('click', function () {
			if (frame) {
				frame.open();
				return;
			}

			frame = window.wp.media({
				title: 'Select Open Graph Image',
				button: {
					text: 'Use this image'
				},
				library: {
					type: 'image'
				},
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();

				input.value = attachment.url || '';
				renderPreview(input.value);
			});

			frame.open();
		});

		removeButton.addEventListener('click', function () {
			input.value = '';
			renderPreview('');
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-wpseo-console-preview]').forEach(initialize);
		document.querySelectorAll('[data-wpseo-console-image-select]').forEach(initializeImageSelect);
	});
}());
