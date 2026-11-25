(function () {

<!-- BEGIN UPDATE_BLOCK -->
	window.SY_CKEDITOR_ROOT = {CKEDITOR_ROOT};

	const elementId = '{ELEMENT_ID}';
	const startButton = document.getElementById('btn-article-update-start');
	const stopButton = document.getElementById('btn-article-update-stop');
	let savedDuringSession = false;

	function instance() {
		return window.SyWysiwyg ? window.SyWysiwyg.get(elementId) : null;
	}

	function showStart() {
		if (!startButton || !stopButton) return;
		startButton.classList.remove('d-none');
		stopButton.classList.add('d-none');
		stopButton.disabled = false;
	}

	function showStop() {
		if (!startButton || !stopButton) return;
		startButton.classList.add('d-none');
		stopButton.classList.remove('d-none');
		stopButton.disabled = false;
	}

	document.addEventListener('wysiwyg:ready', function (event) {
		if (event.detail.elementId !== elementId) return;

		const editor = event.detail.editor;
		const writer = editor.dataProcessor.writer;

		['p', 'div'].forEach(function (tag) {
			writer.setRules(tag, {
				indent: true,
				breakBeforeOpen: true,
				breakAfterOpen: true,
				breakBeforeClose: true,
				breakAfterClose: true
			});
		});

		editor.dataProcessor.htmlFilter.addRules({
			elements: {
				img: function (element) {
					element.addClass('img-fluid');
				}
			}
		});

		editor.on('insertElement', function (event) {
			if (event.data.getName() !== 'img') return;
			event.data.addClass('img-fluid');
		});
	});

	document.addEventListener('wysiwyg:error', function (event) {
		if (event.detail.elementId !== elementId) return;

		const message = event.detail.message || 'Unable to save the article';
		alert((new DOMParser).parseFromString(message, 'text/html').documentElement.textContent);

		if (event.detail.destroyed) showStart();
	});

	document.addEventListener('wysiwyg:start', function (event) {
		if (event.detail.elementId !== elementId) return;
		savedDuringSession = false;
	});

	document.addEventListener('wysiwyg:saved', function (event) {
		if (event.detail.elementId !== elementId) return;
		savedDuringSession = true;
	});

	document.addEventListener('wysiwyg:stopped', function (event) {
		if (event.detail.elementId !== elementId) return;

		// A retryable save failure keeps the editor open: an earlier autosave
		// must not reload and discard the unsaved changes.
		if (!event.detail.saved && event.detail.dirty) {
			showStop();
			return;
		}

		// The source slots are saved, not the rendered components: reload so
		// ComponentRenderer rebuilds the components from the server. This is
		// also needed when an autosave already persisted the content and the
		// explicit stop found nothing left to save.
		if (event.detail.saved || savedDuringSession) {
			location.reload();
			return;
		}
		showStart();
	});

	if (startButton) {
		startButton.addEventListener('click', function (event) {
			event.preventDefault();

			const editor = instance();
			if (!editor || editor.isStarted()) return;

			startButton.classList.add('d-none');
			stopButton.classList.remove('d-none');
			stopButton.disabled = true;

			editor.start().then(function () {
				if (editor.isStarted()) {
					showStop();
				} else {
					showStart();
				}
			}).catch(function () {
				showStart();
			});
		});
	}

	if (stopButton) {
		stopButton.addEventListener('click', function (event) {
			event.preventDefault();

			const editor = instance();
			if (!editor) return;

			stopButton.disabled = true;
			editor.stop({save: true}).then(function (result) {
				if (result && result.saved) showStart();
				if (result && !result.saved && result.dirty) showStop();
				if (result && !result.saved && !result.dirty) showStart();
			}).catch(function () {
				showStart();
			});
		});
	}

	const updateModal = document.getElementById('update-article-modal');

	if (updateModal) {
		if (updateModal.querySelector('div.alert')) {
			const modal = new bootstrap.Modal('#update-article-modal');
			modal.show();
		}

		updateModal.addEventListener('shown.bs.modal', function () {
			const input = updateModal.querySelector('input[type="text"]');
			if (input) input.focus();
		});
	}
<!-- END UPDATE_BLOCK -->

})();
