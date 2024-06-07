<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Component\Html\Element;

class Add extends Element {

	public function __construct(array $attributes = []) {
		parent::__construct(tagName: 'div', attributes: $attributes);
		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(__DIR__ . '/../../../lang/bootstrap-article');
		$add = new \Sy\Bootstrap\Component\Modal\Button('addArticleModal', $this->_('New article'), 'plus');
		$add->getDialog()->setBody(new \Sy\Bootstrap\Component\Article\Create());
		$this->setContent($add);
	}

}