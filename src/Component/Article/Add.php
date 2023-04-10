<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Component\WebComponent;

class Add extends WebComponent {

	public function __construct() {
		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');
		$this->setTemplateContent('{ADD}');
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		$create = new \Sy\Bootstrap\Component\Article\Create();
		$create->initialize();
		$create->getField('lang')->setAttribute('value', $lang);
		$add = new \Sy\Bootstrap\Component\Modal\Button('addArticleModal', $this->_('New article'), 'plus');
		$add->getDialog()->setBody($create);
		$this->setComponent('ADD', $add);
	}

}