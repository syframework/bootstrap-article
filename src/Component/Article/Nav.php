<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Component\Html\Link;
use Sy\Component\Html\Navigation;
use Sy\Component\Html\Navigation\Item;
use Sy\Bootstrap\Component\Icon;
use Sy\Component\WebComponent;

class Nav extends Navigation {

	/**
	 * Default articles page name
	 *
	 * @var string
	 */
	private $default;

	/**
	 * @param string $default
	 * @param array $attributes
	 */
	public function __construct($default, $attributes = []) {
		parent::__construct(attributes: $attributes + [
			'class' => 'nav nav-pills flex-column',
			'id'    => 'articles-menu',
		]);
		$this->default = $default;
		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$service = \Project\Service\Container::getInstance();

		$categories = $service->articleCategory->retrieveAll(['WHERE' => ['parent' => null]]);
		$active = is_null($this->get('category')) ? 'active' : '';
		$li = $this->addItem(new Link($this->_('All articles'), \Sy\Bootstrap\Lib\Url::build('page', $this->default), ['class' => "nav-link $active"]));
		$li->setAttribute('class', 'nav-item');

		foreach ($categories as $category) {
			$this->addMenu($this, (int)$category['id'], $this->_($category['name']));
		}
	}

	/**
	 * @param Nav $item
	 * @param int $id
	 * @param string $label
	 * @param boolean $sub
	 */
	private function addMenu($item, $id, $label, $sub = false) {
		$service = \Project\Service\Container::getInstance();
		$categories = $service->articleCategory->retrieveAll(['WHERE' => ['parent' => $id]]);
		if (empty($categories)) {
			$icon = '';
			if ($sub) $icon = new Icon('chevron-right');
			$active = $id === (int)$this->get('category') ? 'active' : '';
			$i = $item->addItem(new Link(WebComponent::concat($icon, ' ', $this->_($label)), \Sy\Bootstrap\Lib\Url::build('page', $this->default, ['category' => $id]), ['class' => "nav-link $active"]));
			$i->addClass('nav-item');
			return $i;
		} else {
			$active = ($id === (int)$this->get('category')) ? 'active' : '';
			$attributes = ['data-bs-toggle' => 'collapse'];
			$attributes['class'] = 'nav-link';
			if (!$active and !in_array($this->get('category'), array_column($categories, 'id'))) {
				$attributes['class'] .= ' collapsed';
			}
			$li = $item->addItem(new Link($this->_($label) . ' <span class="caret"></span>', '#category_' . $id, $attributes));
			$li->addClass('nav-item');
			$list = $li->addElement(new Navigation());
			$list->addElement(
				new Item(
					new Link(
						WebComponent::concat(new Icon('chevron-right'), ' ', sprintf($this->_('All in %s')), $this->_($label)),
						\Sy\Bootstrap\Lib\Url::build('page', $this->default, ['category' => $id]),
						['class' => "nav-link $active"]
					)
				)
			);
			$list->setAttributes([
				'class' => 'nav nav-pills flex-column collapse',
				'id'    => 'category_' . $id,
			]);
			if ($active) {
				$list->addClass('show');
			}
			if (in_array($this->get('category'), array_column($categories, 'id'))) {
				$list->addClass('show');
			}
			foreach ($categories as $category) {
				$this->addMenu($list, (int)$category['id'], $this->_($category['name']), true);
			}
		}
	}

}