<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Bootstrap\Service\Container;
use Sy\Bootstrap\Lib\Url;
use Sy\Bootstrap\Lib\Str;
use Sy\Component\WebComponent;

class Author extends WebComponent {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string|\Sy\Component
	 */
	private $footer;

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		parent::__construct();
		$this->id = $id;
		$this->footer = '';

		$this->mount(function () {
			$this->init();
		});
	}

	/**
	 * @param string|\Sy\Component $footer
	 */
	public function setFooter($footer) {
		$this->footer = $footer;
	}

	private function init() {
		$this->setTemplateFile(__DIR__ . '/Author.html');

		$service = Container::getInstance();
		$user = $service->user->retrieve(['id' => $this->id]);

		$this->setVars([
			'AVATAR' => Url::avatar($user['email']),
			'AUTHOR' => Str::escape($user['firstname'] . ' ' . $user['lastname']),
			'DESCRIPTION' => $user['description'],
			'FOOTER' => $this->footer,
		]);
	}

}