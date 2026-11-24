<?php
namespace Sy\Test\Component\Article;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Component\Article\EditorConfig;

class ArticleEditorConfigTest extends TestCase {

	public function testAllowedContentPreset() {
		$this->assertSame([
			'$1' => [
				'elements'   => ['@dtd' => true],
				'attributes' => true,
				'styles'     => false,
				'classes'    => true,
			],
		], EditorConfig::ALLOWED_CONTENT);
	}

	public function testStylesAreDisabled() {
		$this->assertFalse(EditorConfig::ALLOWED_CONTENT['$1']['styles']);
	}

	public function testSvgIsNotAllowed() {
		$this->assertArrayNotHasKey('svg', EditorConfig::ALLOWED_CONTENT);
		$this->assertArrayNotHasKey('path', EditorConfig::ALLOWED_CONTENT);
	}

	public function testBuildMapsIdentityAndAssets() {
		$options = EditorConfig::build(
			42,
			'fr',
			'csrf',
			'<p>safe</p>',
			true,
			[
				'load'         => '/api/article',
				'save'         => '/api/article',
				'image_browse' => '/editor/article/browse',
				'image_upload' => '/editor/article/upload',
				'file_browse'  => '/editor/article/browse',
				'file_upload'  => '/editor/article/upload',
			],
			[
				'engine'        => '/assets/js/wysiwyg.js',
				'ckeditorJs'    => '/ckeditor.js',
				'ckeditorRoot'  => '/assets/ckeditor',
				'plugins'       => '/assets/js/plugins',
			]
		);

		$this->assertSame('article-content', $options['element-id']);
		$this->assertSame(['id' => 42, 'lang' => 'fr'], $options['save-data']);
		$this->assertSame(['id' => 42, 'lang' => 'fr'], $options['load-data']);
		$this->assertSame('/assets/js/wysiwyg.js', $options['assets']['engine']);
		$this->assertSame(EditorConfig::ALLOWED_CONTENT, $options['allowed-content']);
		$this->assertSame('/assets/ckeditor/templates/article/js/fr.js', $options['templates-files'][0]);
		$this->assertTrue($options['editable']);
		$this->assertArrayNotHasKey('CKEDITOR', $options);
	}

}
