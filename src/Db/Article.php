<?php
namespace Sy\Bootstrap\Db;

use Sy\Db\Sql;

class Article extends Crud {

	public function __construct() {
		parent::__construct('t_article');
	}

	public function create(array $fields) {
		$this->beginTransaction();
		parent::create($fields);
		$id = $this->lastInsertId();
		$this->commit();
		return $id;
	}

	public function retrieve(array $pk) {
		// Cache hit
		$key = $this->getCacheKey('retrieve', $pk);
		$res = $this->getCache($key);
		if (!empty($res)) return $res;

		// Cache miss
		$res = $this->queryOne(new \Sy\Db\MySql\Select([
			'FROM'  => 'v_article',
			'WHERE' => $pk,
		]), \PDO::FETCH_ASSOC);
		$this->setCache($key, $res);
		return $res;
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
				article.*,
				COUNT(message.id) AS nb_message
			FROM (
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
			) AS article
			LEFT JOIN v_message_received message ON message.item_id = article.id AND message.item_type = 'article'
			GROUP BY article.id, article.lang
			ORDER BY published_at DESC
		", $params);
		return $this->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function count($where = null) {
		list($where, $params)  = $this->where($where);
		$sql = new Sql("
			SELECT count(*)
			FROM t_article
			LEFT JOIN t_user user ON t_article.user_id = user.id
			$where
		", $params);
		$res = $this->queryOne($sql);
		return $res[0];
	}

	public function retrieveAll(array $parameters = []) {
		// Cache hit
		$key = $this->getCacheKey('retrieveAll', $parameters);
		$res = $this->getCache($key);
		if (!empty($res)) return $res;

		// Cache miss
		list($where, $params)  = $this->where($parameters);
		$offset = empty($parameters['last']) ? 0 : (int)$parameters['last'];
		$order  = empty($parameters['q']) ? 'published_at DESC' : 'MATCH(t_article.title, t_article.description) AGAINST(:q IN BOOLEAN MODE) DESC';
		$select = empty($parameters['q']) ? '' : 'MATCH(t_article.title, t_article.description) AGAINST(:q IN BOOLEAN MODE) AS matching,';
		$sql = new Sql("
			SELECT
				t_article.*,
				COUNT(message.id) AS nb_message
			FROM (
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
			) AS t_article
			LEFT JOIN v_message_received message ON message.item_id = t_article.id AND message.item_type = 'article'
			GROUP BY t_article.id, t_article.lang
			ORDER BY 1 DESC
		", $params);
		$res = $this->queryAll($sql, \PDO::FETCH_ASSOC);
		$this->setCache($key, $res);
		return $res;
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