<?php
namespace Sy\Test\Component\Article;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Component\Article\Content;

class ArticleContentTest extends TestCase {

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
	}

	public function testReadOnlyArticleLoadsNoEditorAssets() {
		$html = $this->render();
		$this->assertStringNotContainsString('ckeditor.js', $html);
		$this->assertStringNotContainsString('SyWysiwyg.create', $html);
	}

}
