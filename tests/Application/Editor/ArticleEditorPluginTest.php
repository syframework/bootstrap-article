<?php
namespace Sy\Test\Application\Editor;

use PHPUnit\Framework\TestCase;

class ArticleEditorPluginTest extends TestCase {

	public function testPluginUsesTheSharedCkFileTrait() {
		$reflection = new \ReflectionClass(\Sy\Bootstrap\Application\Editor\Article::class);

		$this->assertContains(
			\Sy\Bootstrap\Application\Editor\CkFile::class,
			$reflection->getTraitNames()
		);
		$this->assertTrue($reflection->hasMethod('renderAction'));
	}

}
