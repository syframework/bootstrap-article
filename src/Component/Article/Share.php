<?php
namespace Sy\Bootstrap\Component\Article;

/**
 * "Share this article" section.
 *
 * The section owns its heading so the Article translator is applied without
 * requiring the page controller to register it.
 */
class Share extends \Sy\Component\WebComponent {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @param string $url
	 */
	public function __construct($url) {
		parent::__construct();
		$this->url = $url;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(__DIR__ . '/../../../lang/bootstrap-article');
		$this->setTemplateContent('<h2>{"Share this article"}</h2><div>{BUTTONS/}</div>');
		$this->setComponent('BUTTONS', new \Sy\Bootstrap\Component\Share\Buttons($this->url));
	}

}
