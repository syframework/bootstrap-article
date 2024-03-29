<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Bootstrap\Service\Container;
use Sy\Bootstrap\Lib\Url;
use Sy\Component\WebComponent;

class Author extends WebComponent {

	private $id;
	private $footer;

	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
		$this->footer = '';
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	public function setFooter($footer) {
		$this->footer = $footer;
	}

	private function init() {
		$this->setTemplateFile(__DIR__ . '/Author.html');
		$this->addTranslator(LANG_DIR . '/bootstrap-article');

		$service = Container::getInstance();
		$user = $service->user->retrieve(['id' => $this->id]);

		$this->setVars([
			'AVATAR' => Url::avatar($user['id']),
			'AUTHOR' => htmlentities(trim($user['firstname'] . ' ' . $user['lastname']), ENT_QUOTES, 'UTF-8'),
			'DESCRIPTION' => $user['description'],
			'FOOTER' => $this->footer,
		]);
	}

}