<?php
namespace Sy\Bootstrap\Component\Article;

class Search extends \Sy\Component\WebComponent {

	public function __construct() {
		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(__DIR__ . '/../../../lang/bootstrap-article');
		$this->setTemplateFile(__DIR__ . '/Search.html');
		$this->setVars([
			'ACTION' => $_SERVER['REQUEST_URI'],
			'VALUE'  => $this->get('q', ''),
		]);
	}

}