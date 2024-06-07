<?php
namespace Sy\Bootstrap\Component\Article;

class Breadcrumb extends \Sy\Component\WebComponent {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @param int $id Article id
	 * @param string $lang Article language
	 */
	public function __construct($id, $lang) {
		parent::__construct();
		$this->id   = $id;
		$this->lang = $lang;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(__DIR__ . '/../../../lang/bootstrap-article');
		// Template
		$this->setTemplateFile(__DIR__ . '/Breadcrumb.html');

		// Retrieve article
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $this->id, 'lang' => $this->lang]);

		$this->setVars([
			'ALL_ARTICLES_URL' => \Sy\Bootstrap\Lib\Url::build('page', 'articles'),
			'TITLE'            => htmlentities($article['title'], ENT_QUOTES, 'UTF-8'),
		]);

		if (!empty($article['category_id'])) {
			$this->initBreadcrumb($article['category_id']);
		}
	}

	private function initBreadcrumb($categoryId) {
		$service = \Project\Service\Container::getInstance();
		$category = $service->articleCategory->retrieve(['id' => $categoryId]);
		$position = 1;
		if (!is_null($category['parent'])) {
			$position += $this->initBreadcrumb($category['parent']);
		}
		$this->setVars([
			'CATEGORY_URL'  => \Sy\Bootstrap\Lib\Url::build('page', 'articles', ['category' => $category['id']]),
			'CATEGORY_NAME' => $this->_($category['name']),
			'POSITION'      => $position + 1,
		]);
		$this->setBlock('CATEGORY_BLOCK');
		return $position;
	}

}