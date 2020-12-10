<?php
namespace Sy\Bootstrap\Component\Article;

class Side extends \Sy\Component\Html\Panel {

	private $articleId;
	private $categoryId;

	public function __construct($articleId, $categoryId) {
		parent::__construct();
		$this->articleId  = $articleId;
		$this->categoryId = $categoryId;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$service = \Project\Service\Container::getInstance();

		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();

		$articles = $this->getSideArticles($this->articleId, $lang, $this->categoryId);

		foreach ($articles as $article) {
			$this->setComponent('CENTER', new Feed\Item($article), true);
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
				return $this->getSideArticles($articleId, $lang, $category['parent']);
			} else {
				return $articles;
			}
		}
	}

}