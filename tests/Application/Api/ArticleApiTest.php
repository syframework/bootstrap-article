<?php
namespace Sy\Test\Application\Api;

use PHPUnit\Framework\TestCase;
use Project\Service\ArticleStub;

class ArticleApiTest extends TestCase {

	private $requestBackup;

	private $postBackup;

	private $serverBackup;

	protected function setUp(): void {
		$this->requestBackup = $_REQUEST;
		$this->postBackup    = $_POST;
		$this->serverBackup  = $_SERVER;


		// The API security check accepts the article owner without the
		// article-update permission.
		ArticleStub::$userId = 1;
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST[ACTION_TRIGGER] = 'article';
		$_REQUEST['id'] = 1;
		$_REQUEST['lang'] = 'fr';
	}

	protected function tearDown(): void {
		$_REQUEST = $this->requestBackup;
		$_POST    = $this->postBackup;
		$_SERVER  = $this->serverBackup;
		ArticleStub::$userId = 2;
		ArticleStub::$lang = null;
	}

	private function post(array $data) {
		$_POST = array_merge([
			'id'      => 1,
			'lang'    => 'fr',
			'csrf'    => 'csrf-token',
			'content' => '<p>Hello</p>',
		], $data);

		return (string) new \Sy\Bootstrap\Application\Api\Article();
	}

	public function testPostStoresSanitizedContent() {
		$output = $this->post(['content' => '<p>Hello <script>alert(1)</script></p>']);

		$this->assertStringContainsString('"status":"ok"', $output);
		$this->assertStringNotContainsString('alert(1)', $output);
		$this->assertStringContainsString('Hello', $output);
	}

	public function testPostRejectsSaveOnMissingTranslation() {
		ArticleStub::$lang = 'en';

		$output = $this->post(['lang' => 'fr', 'content' => '<p>Hello</p>']);

		$this->assertStringContainsString('Article not found', $output);
	}

	public function testPostRejectsContentLargerThanOneMebibyte() {
		$output = $this->post(['content' => str_repeat('a', \Sy\Bootstrap\Lib\HtmlSanitizer::MAX_LENGTH + 1)]);

		$this->assertStringContainsString('Content too large', $output);
	}

	public function testPostRejectsInvalidUtf8() {
		$output = $this->post(['content' => "\xC3\x28"]);

		$this->assertStringContainsString('not valid UTF-8', $output);
	}

}
