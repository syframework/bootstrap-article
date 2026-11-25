<?php
namespace Sy\Test\Component\Article;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Component\Article\Share;

class ArticleSectionsTest extends TestCase {

	public function testShareSectionOwnsItsTranslatedTitle() {
		$section = new Share('http://example.com');

		$html = (string) $section;

		$this->assertStringContainsString("Partager cet article", $html);
	}

}
