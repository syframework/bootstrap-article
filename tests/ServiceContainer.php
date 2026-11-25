<?php
namespace Project\Service;

class UserStub {

	public $id = 1;

	public $language = 'fr';

	public function getCurrentUser() {
		return $this;
	}

	public function isConnected() {
		return true;
	}

	public function hasPermission($permission) {
		return false;
	}

	public function getCsrfToken() {
		return 'csrf-token';
	}

}

class ArticleStub {

	public const DEFAULT_CONTENT = '<p style="color:red">Hello <script>alert(1)</script></p><div data-sycomponent="Unknown">slot</div><div data-sycomponent="Unknown">{SLOT}</div>';

	public static $userId = 2;

	public static $content = self::DEFAULT_CONTENT;

	public static $lang = null;

	public $updated = [];

	public function retrieve($where) {
		return [
			'id'             => $where['id'] ?? 1,
			'lang'           => self::$lang ?? ($where['lang'] ?? 'fr'),
			'title'          => 'Article title',
			'description'    => 'Article description',
			'content'        => self::$content,
			'status'         => 'public',
			'user_id'        => self::$userId,
			'user_firstname' => 'Jane',
			'user_lastname'  => 'Doe',
			'published_at'   => '2026-01-01 12:00:00',
			'updated_at'     => '2026-01-02 12:00:00',
			'category_id'    => null,
			'alias'          => 'article-title',
		];
	}

	public function update($where, $fields) {
		$this->updated[] = [$where, $fields];
	}

}

class ArticleHistoryStub {

	public $changed = [];

	public function change($data, $where) {
		$this->changed[] = [$data, $where];
	}

}

class Container extends \Sy\Bootstrap\Service\Container {

	public function __construct() {
		parent::__construct();

		$this->user = function () {
			return new UserStub();
		};
		$this->article = function () {
			return new ArticleStub();
		};
		$this->articleHistory = function () {
			return new ArticleHistoryStub();
		};
	}

}
