(function () {

<!-- BEGIN UPDATE_BLOCK -->
	var changed = false;
	var content = '';
	var csrf = "{CSRF}";

	CKEDITOR.dtd.$removeEmpty['span'] = false;
	CKEDITOR.dtd.$removeEmpty['i'] = false;

	function save(reload) {
		// Define the data to be sent in the POST request
		const data = {
			id: "{ID}",
			lang: "{LANG}",
			csrf: csrf,
			content: CKEDITOR.instances['article-content'].getData().replace(/<p[^>]*>\s* \s*<\/p>/gm, '')
		};

		// Set up the fetch request
		fetch("{URL}", {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded"
			},
			body: Object.keys(data).map(key => `${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`).join("&")
		})
		.then(response => response.json())
		.then(res => {
			if (res.status === 'ko') {
				alert((new DOMParser).parseFromString(res.message, 'text/html').documentElement.textContent);
				if (res.csrf) {
					csrf = res.csrf;
					changed = true;
					document.getElementById('btn-article-update-stop').removeAttribute('disabled');
				} else {
					CKEDITOR.instances['article-content'].destroy();
					document.getElementById('article-content').setAttribute('contenteditable', 'false');
					document.getElementById('btn-article-update-start').style.display = 'block';
					document.getElementById('btn-article-update-stop').classList.add('d-none');
				}
			} else if (reload) {
				CKEDITOR.instances['article-content'].destroy();
				document.getElementById('article-content').setAttribute('contenteditable', 'false');
				document.getElementById('btn-article-update-start').style.display = 'block';
				document.getElementById('btn-article-update-stop').classList.add('d-none');
				document.getElementById('article-content').innerHTML = res.content;
			}
			if (res.status === 'ok') {
				content = res.content;
			}
			changed = false;
		})
		.catch(error => {
			console.error('Error: Unable to make the POST request.', error);
		});
	}

	// $('#btn-article-update-start').click(function(e) {
	document.getElementById('btn-article-update-start').addEventListener('click', function (e) {
		e.preventDefault();
		if (CKEDITOR.instances['article-content']) return;

		const params = {
			id: "{ID}",
			lang: "{LANG}",
			ts: new Date().getTime()
		};
		const url = new URL('{URL}', window.location.origin);
		Object.entries(params).forEach(([key, value]) => {
			url.searchParams.set(key, value);
		});

		fetch(url.href)
			.then(response => response.json())
			.then(res => {
				if (res.status === 'ok') {
					content = res.content;
					document.getElementById('article-content').innerHTML = res.content;

					const editor = CKEDITOR.inline('article-content', {
						toolbar: [
							{ name: 'document', items: [ 'Sourcedialog', '-', 'Save', '-', 'Templates' ] },
							{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
							{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
							'/',
							{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
							{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
							{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe' ] },
							'/',
							{ name: 'styles', items: [ 'Styles', 'Format' ] },
							{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
							{ name: 'tools', items: [ 'ShowBlocks' ] },
						],
						title: false,
						startupFocus: true,
						templates_replaceContent: false,
						linkShowAdvancedTab: false,
						clipboard_handleImages: false,
						filebrowserImageBrowseUrl: '{IMG_BROWSE}',
						filebrowserImageUploadUrl: '{IMG_UPLOAD_AJAX}',
						filebrowserBrowseUrl: '{FILE_BROWSE}',
						filebrowserUploadUrl: '{FILE_UPLOAD_AJAX}',
						filebrowserWindowWidth: 400,
						filebrowserWindowHeight: 400,
						imageUploadUrl: '{IMG_UPLOAD_AJAX}',
						uploadUrl: '{FILE_UPLOAD_AJAX}',
						extraPlugins: 'sourcedialog,tableresize,uploadimage,uploadfile',
						allowedContent: {
							$1: {
								elements: CKEDITOR.dtd,
								attributes: true,
								styles: true,
								classes: true
							},
							svg: {
								attributes: true,
								styles: true,
								classes: true
							},
							path: {
								attributes: true,
								styles: true,
								classes: true
							}
						},
						justifyClasses: [ 'text-left', 'text-center', 'text-right', 'text-justify' ],
						disallowedContent: 'script; *[on*]; img{width,height}',
						removePlugins: 'about,exportpdf,bidi,font,forms,language,pagebreak,newpage,wsc,scayt,flash,smiley',
						templates: 'websyte',
						templates_files: ['{CKEDITOR_ROOT}/templates/{LANG}/article.js'],
						on: {
							instanceReady: function (ev) {
								this.dataProcessor.writer.setRules('p', {
									indent: true,
									breakBeforeOpen: true,
									breakAfterOpen: true,
									breakBeforeClose: true,
									breakAfterClose: true
								});
								this.dataProcessor.writer.setRules('div', {
									indent: true,
									breakBeforeOpen: true,
									breakAfterOpen: true,
									breakBeforeClose: true,
									breakAfterClose: true
								});
								this.dataProcessor.htmlFilter.addRules({
									elements: {
										img: function(el) {
											el.addClass('img-fluid');
										}
									},
									attributes: {
										style: function(val, el) {
											val = CKEDITOR.tools.parseCssText(val, 1);
											if (val.display in {'block': 1, 'inline-block': 1}) {
												delete val.display;
											}
											return CKEDITOR.tools.writeCssText(val) || false;
										}
									}
								});
							},
							insertElement: function(e) {
								if (e.data.getName() !== 'img') return;
								e.data.addClass('img-fluid');
								e.data.setAttribute('style', '');
							},
							dialogShow: function(e) {
								if (e.data.getName() !== 'sourcedialog') return;
								e.data.on('hide', function() {
									if (changed) save(true);
								});
							}
						}
					});

					editor.on('blur', function() {
						if (changed) save();
					});

					editor.on('change', function() {
						changed = true;
					});

					document.getElementById('article-content').contentEditable = true;
					document.getElementById('btn-article-update-start').style.display = 'none';
					document.getElementById('btn-article-update-stop').classList.remove('d-none');
					document.getElementById('btn-article-update-stop').disabled = false;
				} else {
					alert((new DOMParser).parseFromString(res.message, 'text/html').documentElement.textContent);
				}
			})
			.catch(error => console.error('Error:', error));
	});

	document.getElementById('btn-article-update-stop').addEventListener('click', function (e) {
		e.preventDefault();
		if (changed) {
			document.getElementById('btn-article-update-stop').disabled = true;
			save(true);
		} else {
			CKEDITOR.instances['article-content'].destroy();
			document.getElementById('article-content').contentEditable = false;
			document.getElementById('btn-article-update-start').style.display = 'block';
			document.getElementById('btn-article-update-stop').classList.add('d-none');
			document.getElementById('article-content').innerHTML = content;
		}
	});

	setInterval(function() {
		if (changed) save();
	}, 60000);

	const updateModal = document.getElementById('update-article-modal');

	if (updateModal.querySelector('div.alert')) {
		const bsModal = new bootstrap.Modal('#update-article-modal');
		bsModal.show();
	}

	updateModal.addEventListener('shown.bs.modal', () => {
		updateModal.querySelector('input[type="text"]').focus();
	});
<!-- END UPDATE_BLOCK -->
<!-- BEGIN DELETE_BLOCK -->
	document.getElementById('btn-article-delete').addEventListener('click', function (e) {
		e.preventDefault();
		if (confirm((new DOMParser).parseFromString('{CONFIRM_DELETE}', 'text/html').documentElement.textContent)) {
			document.getElementById('{DELETE_FORM_ID}').submit();
		}
	});
<!-- END DELETE_BLOCK -->

})();
