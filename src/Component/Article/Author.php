<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Bootstrap\Service\Container;
use Sy\Bootstrap\Lib\Url;
use Sy\Component\WebComponent;

class Author extends WebComponent {

	private $id;

	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->setTemplateFile(__DIR__ . '/Author.html');
		$this->addTranslator(LANG_DIR . '/bootstrap-article');

		$service = Container::getInstance();
		$user = $service->user->retrieve(['id' => $this->id]);

		$this->setVars([
			'AVATAR' => Url::avatar($user['id']),
			'AUTHOR' => htmlentities(trim($user['firstname'] . ' ' . $user['lastname']), ENT_QUOTES, 'UTF-8'),
			'LINKS'  => new \Sy\Bootstrap\Component\Link\Div('user-' . $user['id']),
			'DESCRIPTION' => $user['description']
		]);
	}

}