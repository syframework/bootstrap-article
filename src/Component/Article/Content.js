$(function() {
<!-- BEGIN UPDATE_BLOCK -->
	var changed = false;
	var content = '';
	var csrf = "{CSRF}";

	CKEDITOR.dtd.$removeEmpty['span'] = false;
	CKEDITOR.dtd.$removeEmpty['i'] = false;
	CKEDITOR.plugins.addExternal('sycomponent', '{CKEDITOR_ROOT}/plugins/sycomponent/');
	CKEDITOR.plugins.addExternal('sywidget', '{CKEDITOR_ROOT}/plugins/sywidget/');

	function save(reload) {
		$.post("{URL}", {
			id: "{ID}",
			lang: "{LANG}",
			csrf: csrf,
			content: CKEDITOR.instances['article-content'].getData().replace(/<p[^>]*>\s*&nbsp;\s*<\/p>/gm, '')
		}, function(res) {
			if (res.status === 'ko') {
				alert($('<p/>').html(res.message).text());
				if (res.csrf) {
					csrf = res.csrf;
					changed = true;
					$('#btn-article-update-stop').removeAttr('disabled');
				} else {
					CKEDITOR.instances['article-content'].destroy();
					$('#article-content').attr('contenteditable', false);
					$('#btn-article-update-start').show();
					$('#btn-article-update-stop').addClass('d-none');
				}
			} else if (reload) {
				CKEDITOR.instances['article-content'].destroy();
				$('#article-content').attr('contenteditable', false);
				$('#btn-article-update-start').show();
				$('#btn-article-update-stop').addClass('d-none');
				$('#article-content').html(res.content);
			}
			if (res.status === 'ok') {
				content = res.content;
			}
			changed = false;
		}, 'json');
	}

	$('#btn-article-update-start').click(function(e) {
		e.preventDefault();
		if (CKEDITOR.instances['article-content']) return;
		content = $('#article-content').html();
		var editor = CKEDITOR.inline('article-content', {
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
			filebrowserImageBrowseUrl: '{IMG_BROWSE}',
			filebrowserImageUploadUrl: '{IMG_UPLOAD_AJAX}',
			filebrowserBrowseUrl: '{FILE_BROWSE}',
			filebrowserUploadUrl: '{FILE_UPLOAD_AJAX}',
			filebrowserWindowWidth: 400,
			filebrowserWindowHeight: 400,
			imageUploadUrl: '{IMG_UPLOAD_AJAX}',
			uploadUrl: '{FILE_UPLOAD_AJAX}',
			extraPlugins: 'sourcedialog,sycomponent,sywidget,tableresize,embedbase,embed,autoembed,uploadimage,uploadfile',
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
			removePlugins: 'about,bidi,font,forms,language,pagebreak,newpage,wsc,scayt,flash,smiley',
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
							div: function(el) {
								if (el.hasClass('google-auto-placed')) el.remove();
							},
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

		$('#article-content').attr('contenteditable', true);
		$(this).hide();
		$('#btn-article-update-stop').removeClass('d-none');
		$('#btn-article-update-stop').removeAttr('disabled');
	});

	$('#btn-article-update-stop').click(function(e) {
		e.preventDefault();
		if (changed) {
			$('#btn-article-update-stop').attr('disabled', 'disabled');
			save(true);
		} else {
			CKEDITOR.instances['article-content'].destroy();
			$('#article-content').attr('contenteditable', false);
			$('#btn-article-update-start').show();
			$('#btn-article-update-stop').addClass('d-none');
			$('#article-content').html(content);
		}
	});

	setInterval(function() {
		if (changed) save();
	}, 60000);

	$('#update-article-modal').has('div.alert').modal('show');
<!-- END UPDATE_BLOCK -->
<!-- BEGIN DELETE_BLOCK -->
	$('#btn-article-delete').click(function(e) {
		e.preventDefault();
		if (confirm($('<div />').html("{CONFIRM_DELETE}").text())) {
			$('#{DELETE_FORM_ID}').submit();
		}
	});
<!-- END DELETE_BLOCK -->
});
