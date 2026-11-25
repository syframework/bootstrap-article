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
				'load'             => '/api/article',
				'save'             => '/api/article',
				'image_browse'     => '/editor/article/browse',
				'image_upload'     => '/editor/article/upload',
				'file_browse'      => '/editor/article/browse',
				'file_upload'      => '/editor/article/upload',
				'component_render' => '/editor/article/render',
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
		$this->assertNotContains('sycomponent', $options['extra-plugins']);
		$this->assertNotContains('Sycomponent', $this->toolbarItems($options, 'insert'));
		$this->assertTrue($options['editable']);
		$this->assertFalse($options['components']['enabled']);
		$this->assertSame([], $options['components']['allowlist']);
		$this->assertSame('/editor/article/render', $options['components']['render-url']);
		$this->assertArrayNotHasKey('CKEDITOR', $options);
	}

	public function testComponentsAreEnabledWhenTheProjectAllowsThem() {
		$options = EditorConfig::build(
			42,
			'fr',
			'csrf',
			'<p>safe</p>',
			true,
			[
				'load'             => '/api/article',
				'save'             => '/api/article',
				'image_browse'     => '/editor/article/browse',
				'image_upload'     => '/editor/article/upload',
				'file_browse'      => '/editor/article/browse',
				'file_upload'      => '/editor/article/upload',
				'component_render' => '/editor/article/render',
			],
			[
				'engine'        => '/assets/js/wysiwyg.js',
				'ckeditorJs'    => '/ckeditor.js',
				'ckeditorRoot'  => '/assets/ckeditor',
				'plugins'       => '/assets/js/plugins',
			],
			['Project\\Component\\Article\\Map']
		);

		$this->assertTrue($options['components']['enabled']);
		$this->assertContains('sycomponent', $options['extra-plugins']);
		$this->assertContains('Sycomponent', $this->toolbarItems($options, 'insert'));
		$this->assertSame(['Project\\Component\\Article\\Map'], $options['components']['allowlist']);
		$this->assertSame('/editor/article/render', $options['components']['render-url']);
	}

	private function toolbarItems(array $options, $group) {
		foreach ($options['config']['toolbar'] as $item) {
			if (is_array($item) and ($item['name'] ?? null) === $group) return $item['items'];
		}
		return [];
	}

}
