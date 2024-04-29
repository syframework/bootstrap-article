<?php
namespace Sy\Bootstrap\Service;

class Article extends \Sy\Bootstrap\Service\Crud {

	public function __construct() {
		parent::__construct('article');
	}

	public function count($where = null) {
		$where['exact'] = true;
		$nb = parent::count($where);
		if ($nb > 0) return $nb;
		$where['exact'] = false;
		return parent::count($where);
	}

	public function retrieve(array $pk) {
		if (!isset($pk['lang'])) {
			$service = \Project\Service\Container::getInstance();
			$pk['lang'] = $service->lang->getLang();
		}
		$article = parent::retrieve($pk);
		if (!empty($article)) return $article;
		unset($pk['lang']);
		return parent::retrieve($pk);
	}

	public function retrieveAll(array $parameters = []) {
		$parameters['exact'] = true;
		$items = parent::retrieveAll($parameters);
		if (!empty($items)) return $items;
		$parameters['exact'] = false;
		return parent::retrieveAll($parameters);
	}

	public function getCategories($categoryId) {
		if (empty($categoryId)) return [];
		$service    = \Project\Service\Container::getInstance();
		$categories = $service->articleCategory->retrieveAll(['WHERE' => ['parent' => $categoryId]]);
		if (empty($categories)) {
			return [$categoryId];
		} else {
			$res = [];
			foreach ($categories as $category) {
				$res = array_merge($res, $this->getCategories($category['id']));
			}
			return array_merge([$categoryId], $res);
		}
	}

}