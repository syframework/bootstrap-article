<?php
namespace Sy\Bootstrap\Service\Container;

use Sy\Bootstrap\Service\Crud;

/**
 * @property-read \Sy\Bootstrap\Service\Article $article Article service
 * @property-read Crud $articleCategory Article category service
 * @property-read Crud $articleHistory Article history service
 */
class Article extends \Sy\Container {

	public function __construct() {
		parent::__construct();

		$this->article = function () {
			return new \Sy\Bootstrap\Service\Article();
		};
		$this->articleCategory = function () {
			return new Crud('articleCategory');
		};
		$this->articleHistory = function () {
			return new Crud('articleHistory');
		};
	}

}