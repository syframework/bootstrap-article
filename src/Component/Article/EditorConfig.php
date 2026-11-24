<?php
namespace Sy\Bootstrap\Component\Article;

/**
 * CKEditor configuration for the article editor.
 *
 * `styles` is false on purpose: HtmlSanitizer removes inline styles, so the
 * editor must not offer content that the save path would silently discard.
 * Layout is expressed with Bootstrap classes.
 */
class EditorConfig {

	public const ALLOWED_CONTENT = [
		'$1' => [
			'elements'   => ['@dtd' => true],
			'attributes' => true,
			'styles'     => false,
			'classes'    => true,
		],
	];

	/**
	 * @param int|string $id
	 * @param string     $lang
	 * @param string     $csrf
	 * @param string     $content Sanitized initial HTML
	 * @param bool       $editable
	 * @param array      $urls               load, save, image_browse, image_upload, file_browse, file_upload
	 * @param array      $assets             engine, ckeditorJs, ckeditorRoot, plugins
	 * @param array      $componentAllowlist Components the project authorizes
	 * @return array
	 */
	public static function build($id, $lang, $csrf, $content, $editable, array $urls, array $assets, array $componentAllowlist = []) {
		return [
			'element-id' => 'article-content',
			'mode'       => 'inline',
			'container'  => true,
			'editable'   => $editable,
			'content'    => $content,
			'load-url'   => $urls['load'],
			'load-data'  => ['id' => $id, 'lang' => $lang],
			'save-url'   => $urls['save'],
			'save-data'  => ['id' => $id, 'lang' => $lang],
			'csrf'       => $csrf,
			'upload'     => [
				'image' => [
					'browse' => $urls['image_browse'],
					'upload' => $urls['image_upload'],
					'mode'   => 'ajax',
					'window' => ['width' => 400, 'height' => 400],
				],
				'file' => [
					'browse' => $urls['file_browse'],
					'upload' => $urls['file_upload'],
					'mode'   => 'ajax',
					'window' => ['width' => 400, 'height' => 400],
				],
			],
			'allowed-content' => self::ALLOWED_CONTENT,
			'templates'       => 'bootstrap-article',
			'templates-files' => [
				$assets['ckeditorRoot'] . '/templates/article/js/' . $lang . '.js',
			],
			'extra-plugins'  => ['sourcedialog', 'tableresize', 'uploadimage', 'uploadfile'],
			'remove-plugins' => ['about', 'exportpdf', 'bidi', 'font', 'forms', 'language', 'pagebreak', 'newpage', 'wsc', 'scayt', 'flash', 'smiley'],
			'components'     => [
				'enabled'   => $componentAllowlist !== [],
				'allowlist' => $componentAllowlist,
			],
			'config'         => [
				'toolbar' => [
					['name' => 'document', 'items' => ['Sourcedialog', '-', 'Save', '-', 'Templates']],
					['name' => 'clipboard', 'items' => ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']],
					['name' => 'basicstyles', 'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat']],
					'/',
					['name' => 'paragraph', 'items' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']],
					['name' => 'links', 'items' => ['Link', 'Unlink', 'Anchor']],
					['name' => 'insert', 'items' => ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe']],
					'/',
					['name' => 'styles', 'items' => ['Styles', 'Format']],
					['name' => 'colors', 'items' => ['TextColor', 'BGColor']],
					['name' => 'tools', 'items' => ['ShowBlocks']],
				],
				'justifyClasses'           => ['text-left', 'text-center', 'text-right', 'text-justify'],
				'disallowedContent'        => 'script; *[on*]; *{*}; img{width,height}',
				'templates_replaceContent' => false,
				'title'                    => false,
				'startupFocus'             => true,
				'linkShowAdvancedTab'      => false,
				'clipboard_handleImages'   => false,
			],
			'autosave'     => ['interval' => 60000, 'on-blur' => true, 'on-dialog-close' => true],
			'guard-unload' => true,
			'assets'       => [
				'engine'        => $assets['engine'],
				'ckeditor-js'   => $assets['ckeditorJs'],
				'ckeditor-root' => $assets['ckeditorRoot'],
				'plugins'       => $assets['plugins'],
			],
		];
	}

}
