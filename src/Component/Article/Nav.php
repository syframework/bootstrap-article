<?php
namespace Sy\Bootstrap\Component\Article;

class Nav extends \Sy\Component\Html\Navigation {

	/**
	 * Default articles page name
	 *
	 * @var string
	 */
	private $default;

	public function __construct($default) {
		parent::__construct();
		$this->default = $default;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');
		$service = \Project\Service\Container::getInstance();

		$categories = $service->articleCategory->retrieveAll(['WHERE' => ['parent' => null]]);
		$active = is_null($this->get('category')) ? 'active' : '';
		$li = $this->addItem($this->_('All articles'), \Sy\Bootstrap\Lib\Url::build('page', $this->default), ['class' => "nav-link $active"]);
		$li->setAttribute('class', 'nav-item');

		foreach ($categories as $category) {
			$this->addMenu($this, (int)$category['id'], $this->_($category['name']));
		}
		$this->setAttributes([
			'class' => 'nav nav-pills flex-column',
			'id'    => 'articles-menu'
		]);
	}

	private function addMenu($item, $id, $label, $sub = false) {
		$service = \Project\Service\Container::getInstance();
		$categories = $service->articleCategory->retrieveAll(['WHERE' => ['parent' => $id]]);
		if (empty($categories)) {
			$icon = '';
			if ($sub) $icon = '<span class="fas fa-chevron-right"></span> ';
			$active = $id === (int)$this->get('category') ? 'active' : '';
			$i = $item->addItem($icon . $this->_($label), \Sy\Bootstrap\Lib\Url::build('page', $this->default, ['category' => $id]), ['class' => "nav-link $active"]);
			$i->addClass('nav-item');
			return $i;
		} else {
			$active = ($id === (int)$this->get('category')) ? 'active' : '';
			$attributes = ['data-toggle' => 'collapse'];
			$attributes['class'] = 'nav-link';
			if (!$active and !in_array($this->get('category'), array_column($categories, 'id'))) {
				$attributes['class'] .= ' collapsed';
			}
			$li = $item->addItem($this->_($label) . ' <span class="caret"></span>', '#category_' . $id, $attributes);
			$li->addClass('nav-item');
			$all = $li->addItem('<span class="fas fa-chevron-right"></span> ' . sprintf($this->_('All in %s'), $this->_($label)), \Sy\Bootstrap\Lib\Url::build('page', $this->default, ['category' => $id]), ['class' => "nav-link $active"]);
			$all->addClass('nav-item');
			$list = $li->getList();
			$list->setAttributes([
				'class' => 'nav nav-pills collapse',
				'id'    => 'category_' . $id,
			]);
			if ($active) {
//				$all->setAttribute('class', 'active');
				$list->addClass('show');
			}
			if (in_array($this->get('category'), array_column($categories, 'id'))) {
				$list->addClass('show');
			}
			foreach ($categories as $category) {
				$i = $this->addMenu($li, (int)$category['id'], $this->_($category['name']), true);
			}
		}
	}

}