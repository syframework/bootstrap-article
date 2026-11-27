<?php
namespace Sy\Test\Application\Editor;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Application\Editor\Article;

/**
 * The article editor endpoints: browse/upload come from `CkFile`, the component
 * preview from the shared `Render` trait (wysiwyg 1.9) and the component
 * catalogue from `Metadata` (wysiwyg 1.10).
 */
class ArticleTest extends TestCase {

	public function testUsesTheSharedEditorTraits() {
		$reflection = new \ReflectionClass(Article::class);

		$this->assertContains('Sy\Bootstrap\Application\Editor\CkFile', $reflection->getTraitNames());
		$this->assertContains('Sy\Bootstrap\Application\Editor\Render', $reflection->getTraitNames());
		$this->assertContains('Sy\Bootstrap\Application\Editor\Metadata', $reflection->getTraitNames());
	}

	public function testMetadataActionComesFromTheEngine() {
		$file = (new \ReflectionMethod(Article::class, 'metadataAction'))->getFileName();

		$this->assertStringEndsWith(
			'Application/Editor/Metadata.php',
			str_replace('\\', '/', $file)
		);
	}

	public function testRenderActionComesFromTheEngine() {
		$file = (new \ReflectionMethod(Article::class, 'renderAction'))->getFileName();

		// The 1.7.0 implementation lived in the plugin: one engine copy now.
		$this->assertStringEndsWith(
			'Application/Editor/Render.php',
			str_replace('\\', '/', $file)
		);
	}

	public function testRenderTranslatorsIncludeThePackageDirectory() {
		$instance = (new \ReflectionClass(Article::class))->newInstanceWithoutConstructor();
		// ReflectionMethod::setAccessible() is a no-op since PHP 8.1.
		$method = new \ReflectionMethod(Article::class, 'renderTranslators');

		$directories = $method->invoke($instance, 'fr');

		$this->assertContains(LANG_DIR, $directories);
		$this->assertContains(
			realpath(__DIR__ . '/../../../lang/bootstrap-article'),
			array_map('realpath', $directories)
		);
	}

}
