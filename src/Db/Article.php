<?php
namespace Sy\Bootstrap\Db;

use Sy\Db\Sql;

class Article extends Crud {

	public function __construct() {
		parent::__construct('t_article');
	}

	public function create(array $fields) {
		return $this->transaction(function() use ($fields) {
			parent::create($fields);
			return $this->lastInsertId();
		});
	}

	public function retrieve(array $pk) {
		return parent::executeRetrieve($pk, new \Sy\Db\MySql\Select([
			'FROM'  => 'v_article',
			'WHERE' => $pk,
		]));
	}

	public function retrieveSide($articleId, $lang = null, $categoryId = null) {
		$params = [':article_id' => $articleId];
		$where  = 'AND t_article.published_at < NOW() ';
		if (!empty($categoryId)) {
			$params[':category_id'] = $categoryId;
			$where .= 'AND t_article.category_id = :category_id ';
		}
		if (!empty($lang)) {
			$params[':lang'] = $lang;
			$where .= 'AND t_article.lang = :lang ';
		}
		$sql = new Sql("
			SELECT
				t_article.*,
				user.firstname AS user_firstname,
				user.lastname AS user_lastname,
				category.name AS category
			FROM t_article
			LEFT JOIN t_user user ON t_article.user_id = user.id
			LEFT JOIN t_article_category category ON t_article.category_id = category.id
			WHERE t_article.status <> 'draft'
				AND t_article.id <> :article_id
				$where
			ORDER BY published_at DESC
			LIMIT 3
		", $params);
		return $this->db->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function count($where = null) {
		list($where, $params)  = $this->where($where);
		$sql = new Sql("
			SELECT count(*)
			FROM t_article
			LEFT JOIN t_user user ON t_article.user_id = user.id
			$where
		", $params);
		$res = $this->db->queryOne($sql);
		return $res[0];
	}

	public function retrieveAll(array $parameters = []) {
		list($where, $params)  = $this->where($parameters);
		$offset = empty($parameters['last']) ? 0 : (int)$parameters['last'];
		$order  = empty($parameters['q']) ? 'published_at DESC' : 'MATCH(t_article.title, t_article.description) AGAINST(:q IN BOOLEAN MODE) DESC';
		$select = empty($parameters['q']) ? '' : 'MATCH(t_article.title, t_article.description) AGAINST(:q IN BOOLEAN MODE) AS matching,';
		$sql = new Sql("
			SELECT
				$select
				t_article.published_at AS at,
				t_article.*,
				user.firstname AS user_firstname,
				user.lastname AS user_lastname,
				category.name AS category
			FROM t_article
			LEFT JOIN t_user user ON t_article.user_id = user.id
			LEFT JOIN t_article_category category ON t_article.category_id = category.id
			$where
			ORDER BY $order
			LIMIT 10 OFFSET $offset
		", $params);
		return $this->executeRetrieveAll($parameters, $sql);
	}

	private function where($parameters) {
		$params = [];
		$where  = ["user.status = 'active'"];
		if (!empty($parameters['lang'])) {
			$params[':lang'] = $parameters['lang'];
			$where[] = 't_article.lang = :lang';
		}
		if (!empty($parameters['category_id'])) {
			$params[':category_id'] = $parameters['category_id'];
			$where[] = 't_article.category_id IN (:category_id)';
		}
		if (!empty($parameters['q'])) {
			$params[':q'] = $this->getQueryMatch($parameters['q'], isset($parameters['exact']) ? $parameters['exact'] : false);
			$where[] = 'MATCH(t_article.title, t_article.description) AGAINST(:q IN BOOLEAN MODE)';
		}
		if (!empty($parameters['status'])) {
			$params[':status'] = $parameters['status'];
			if (empty($parameters['user_id'])) {
				$where[] = 't_article.status = :status';
				$where[] = 't_article.published_at < NOW()';
			} else {
				$params[':user_id'] = $parameters['user_id'];
				$where[] = '(t_article.status = :status OR t_article.user_id = :user_id)';
				$where[] = '(t_article.published_at < NOW() OR t_article.user_id = :user_id)';
			}
		}
		return ['WHERE ' . implode(' AND ', $where), $params];
	}

	private function getQueryMatch($q, $exact = false) {
		if ($exact) return '"' . str_replace('"', ' ', $q) . '"';
		$words = explode(' ', str_replace(["'", '@', '+', '-', '>', '<', '(', ')', '~', '*', '"'], ' ', $q));
		$words = array_filter($words, function($word) {
			return strlen($word) > 1;
		});
		return '+' . implode('* +', $words) . '*';
	}

}