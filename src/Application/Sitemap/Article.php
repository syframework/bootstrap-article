<?php
namespace Sy\Bootstrap\Application\Sitemap;

class Article extends \Sy\Bootstrap\Component\Sitemap {

	public function index() {
		return array(PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('sitemap', 'article'));
	}

	public function init() {
		$service = \Project\Service\Container::getInstance();

		// Article
		$service->article->foreachRow(function ($row) {
			$date = new \Sy\Bootstrap\Lib\Date($row['updated_at']);
			$this->setVars([
				'LOC'  => PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('page', 'article', ['id' => $row['id'], 'alias' => $row['alias']]),
				'LAST' => $date->f('Y-m-d'),
			]);
			$alt = json_decode($row['alternate'], true);
			if (count($alt) > 1) {
				foreach ($alt as $lang => $alias) {
					$this->setVars([
						'LANG' => $lang,
						'HREF' => PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('page', 'article', ['id' => $row['id'], 'alias' => $alias]),
					]);
					$this->setBlock('ALT_BLOCK');
				}
			}
			$this->setBlock('LAST_BLOCK');
			$this->setBlock('URL_BLOCK');
		}, [
			'SELECT'   => "t_article.*, CONCAT('{', GROUP_CONCAT(CONCAT('\"', b.lang, '\":\"', b.alias, '\"')), '}') AS 'alternate'",
			'JOIN'     => 'LEFT JOIN t_article b ON t_article.id = b.id',
			'WHERE'    => ['t_article.status' => 'public'],
			'GROUP BY' => 't_article.id, t_article.lang'
		]);

		$this->out();
	}

}