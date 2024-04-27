<?php
namespace Sy\Bootstrap\Component\Article\Feed;

class Page extends \Sy\Component\WebComponent {

	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var int
	 */
	private $category;

	/**
	 * @var string
	 */
	private $q;

	/**
	 * @param int $page
	 * @param int $category
	 * @param string $q
	 */
	public function __construct($page, $category, $q) {
		parent::__construct();
		$this->page     = $page;
		$this->category = $category;
		$this->q        = $q;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');
		$this->setTemplateFile(__DIR__ . '/Page.html');

		if (is_null($this->page)) {
			$this->setVar('ITEM', '');
			$this->setBlock('ARTICLE_BLOCK');
			return;
		}

		$service = \Project\Service\Container::getInstance();
		try {
			$user = $service->user->getCurrentUser();
			$condition = [
				'last'        => $this->page * 10,
				'category_id' => $service->article->getCategories($this->category),
				'q'           => $this->q,
				'user_id'     => $user->id,
				'lang'        => \Sy\Translate\LangDetector::getInstance(LANG)->getLang(),
			];
			if (!$user->hasPermission('article-read')) {
				$condition['status'] = 'public';
			}
			$articles = $service->article->retrieveAll($condition);
		} catch (\Sy\Db\MySql\Exception $e) {
			$articles = [];
		}

		foreach ($articles as $article) {
			$this->setComponent('ITEM', new Item($article, $this->page));
			$this->setBlock('ARTICLE_BLOCK');
		}
	}

}