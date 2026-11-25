<?php
namespace Sy\Test\Component\Article;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Project\Service\ArticleStub;
use Sy\Bootstrap\Component\Article\Content;

class ArticleContentTest extends TestCase {

	protected function tearDown(): void {
		ArticleStub::$content = ArticleStub::DEFAULT_CONTENT;
	}

	private function render() {
		return (string) new Content(1, 'fr');
	}

	public function testRendersExactlyOneEditableContainer() {
		$html = $this->render();
		$this->assertSame(1, substr_count($html, 'id="article-content"'));
		$this->assertStringNotContainsString('{CONTENT}', $html);
		$this->assertStringNotContainsString('{WYSIWYG_EDITOR}', $html);
	}

	public function testContentIsSanitizedBeforeRendering() {
		$html = $this->render();
		$this->assertStringNotContainsString('alert(1)', $html);
		$this->assertStringNotContainsString('data-sycomponent', $html);
		$this->assertStringNotContainsString('<script>alert', $html);
		$this->assertStringNotContainsString('{SLOT}', $html);
		$this->assertStringNotContainsString('style="color:red"', $html);
	}

	#[RunInSeparateProcess]
	public function testAllowedComponentIsRendered() {
		ArticleStub::$content = '<p><script>alert(1)</script></p><div data-sycomponent="Sy\\Test\\Fixtures\\ArticleComponent">{SLOT}</div>';
		define('WYSIWYG_COMPONENT_ALLOWLIST', [
			'article' => [\Sy\Test\Fixtures\ArticleComponent::class],
		]);

		$html = $this->render();

		$this->assertStringContainsString('<span class="article-fixture">contact</span>', $html);
		$this->assertStringContainsString('data-sycomponent="Sy\\Test\\Fixtures\\ArticleComponent"', $html);
		$this->assertStringNotContainsString('{SLOT}', $html);
		$this->assertStringNotContainsString('alert(1)', $html);
	}

	public function testReadOnlyArticleLoadsNoEditorAssets() {
		$html = $this->render();
		$this->assertStringNotContainsString('ckeditor.js', $html);
		$this->assertStringNotContainsString('SyWysiwyg.create', $html);
	}

}
