<?php
namespace Sy\Bootstrap\Component\Article;

class Side extends \Sy\Component\WebComponent {

	/**
	 * @var int
	 */
	private $articleId;

	/**
	 * @var int
	 */
	private $categoryId;

	/**
	 * @param int $articleId
	 * @param int $categoryId
	 */
	public function __construct($articleId, $categoryId) {
		parent::__construct();
		$this->articleId  = $articleId;
		$this->categoryId = $categoryId;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->setTemplateContent('{CONTENT/}');

		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();

		$articles = $this->getSideArticles($this->articleId, $lang, $this->categoryId);

		foreach ($articles as $article) {
			$this->setComponent('CONTENT', new Feed\Item($article), true);
		}
	}

	private function getSideArticles($articleId, $lang, $categoryId) {
		$service = \Project\Service\Container::getInstance();
		if (empty($categoryId)) {
			return $service->article->retrieveSide($articleId, $lang);
		} else {
			$articles = $service->article->retrieveSide($articleId, $lang, $categoryId);
			if (empty($articles)) {
				$category = $service->articleCategory->retrieve(['id' => $categoryId]);
				$this->log($category);
				return $this->getSideArticles($articleId, $lang, $category['parent']);
			} else {
				return $articles;
			}
		}
	}

}