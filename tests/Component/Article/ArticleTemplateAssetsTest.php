<?php
namespace Sy\Test\Component\Article;

use PHPUnit\Framework\TestCase;

class ArticleTemplateAssetsTest extends TestCase {

	private function template($lang) {
		return __DIR__ . '/../../../assets/ckeditor/templates/article/js/' . $lang . '.js';
	}

	public function testLanguageTemplatesExist() {
		$this->assertFileExists($this->template('fr'));
		$this->assertFileExists($this->template('en'));
	}

	public function testImagesPathFollowsTheEditorAssetsRoot() {
		foreach (['fr', 'en'] as $lang) {
			$content = file_get_contents($this->template($lang));
			$this->assertStringContainsString('window.SY_CKEDITOR_ROOT', $content);
			$this->assertStringNotContainsString("imagesPath: '/assets/ckeditor/templates/article/img/'", $content);
		}
	}

}
