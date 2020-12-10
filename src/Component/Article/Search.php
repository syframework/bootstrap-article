<?php
namespace Sy\Bootstrap\Component\Article;

class Search extends \Sy\Component\WebComponent {

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Search.html');
		$this->setVars([
			'ACTION' => $_SERVER['REQUEST_URI'],
			'VALUE'  => $this->get('q', '')
		]);
	}

}