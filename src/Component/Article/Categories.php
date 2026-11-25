<?php
namespace Sy\Bootstrap\Component\Article;

/**
 * "Categories" section around the article navigation.
 *
 * Keeping the heading in a component means the Article translator is applied
 * without a page-level translator.
 */
class Categories extends \Sy\Component\WebComponent {

	/**
	 * @var string
	 */
	private $default;

	/**
	 * @param string $default Default articles page name
	 */
	public function __construct($default) {
		parent::__construct();
		$this->default = $default;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(__DIR__ . '/../../../lang/bootstrap-article');
		$this->setTemplateContent('<h3 class="pt-3">{"Categories"}</h3><div class="mb-3">{NAV}</div>');
		$this->setComponent('NAV', new Nav($this->default));
	}

}
