<?php
namespace Sy\Bootstrap\Component\Article;

class Feed extends \Sy\Bootstrap\Component\Feed {

	/**
	 * @var int
	 */
	private $category;

	/**
	 * @var string
	 */
	private $q;

	public function __construct() {
		parent::__construct();
		$this->category = $this->get('category');
		$this->q        = $this->get('q');
	}

	public function getPage($n) {
		return new Feed\Page($n, $this->category, $this->q);
	}

	public function isLastPage($n) {
		try {
			if (is_null($n)) return false;
			$service = \Project\Service\Container::getInstance();
			$user = $service->user->getCurrentUser();
			$condition = [
				'last'        => $n,
				'category_id' => $service->article->getCategories($this->category),
				'q'           => $this->q,
				'user_id'     => $user->id,
				'lang'        => $service->lang->getLang(),
			];
			if (!$user->hasPermission('article-read')) {
				$condition['status'] = 'public';
			}
			$nb = $service->article->count($condition);
			return $nb <= (($n + 1) * 10);
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logError('SQL Error');
			return true;
		}
	}

	public function getParams() {
		return [
			'category' => $this->category,
			'q'        => $this->q,
		];
	}

}