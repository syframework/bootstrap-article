<?php
namespace Sy\Test\Component\Article;

use PHPUnit\Framework\TestCase;

/**
 * Guards the test infrastructure itself: if the container or the autoloader
 * stops booting, every later Article test fails with a confusing error.
 */
class ArticleBootstrapTest extends TestCase {

	public function testComponentIsAutoloaded() {
		$this->assertTrue(class_exists(\Sy\Bootstrap\Component\Article\Content::class));
	}

	public function testServiceContainerIsAvailable() {
		$this->assertInstanceOf(
			\Sy\Bootstrap\Service\Container::class,
			\Project\Service\Container::getInstance()
		);
	}

	public function testConstantsAreDefined() {
		$this->assertSame('/webroot', WEB_ROOT);
		$this->assertSame('fr', LANG);
	}

}
