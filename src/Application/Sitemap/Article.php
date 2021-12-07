<?php
namespace Sy\Bootstrap\Application\Sitemap;

class Article implements IProvider {

	/**
	 * Returns sitemap index urls
	 *
	 * @return array An array of URL string
	 */
	public function getIndexUrls() {
		return array(PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('sitemap', 'article'));
	}

	/**
	 * Returns sitemap urls
	 */
	public function getUrls() {
		$urls = [];

		// Article
		$service = \Project\Service\Container::getInstance();
		$service->article->foreachRow(function ($row) use(&$urls) {
			$date = new \Sy\Bootstrap\Lib\Date($row['updated_at']);
			$url['loc'] = PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('page', 'article', ['id' => $row['id'], 'alias' => $row['alias']]);
			$url['lastmod'] = $date->f('yyyy-MM-dd');

			$alt = json_decode($row['alternate'], true);
			if (count($alt) > 1) {
				foreach ($alt as $lang => $alias) {
					$url['alternate'][] = [$lang => PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('page', 'article', ['id' => $row['id'], 'alias' => $alias])];
				}
			}

			$urls[] = $url;
		}, [
			'SELECT'   => "t_article.*, CONCAT('{', GROUP_CONCAT(CONCAT('\"', b.lang, '\":\"', b.alias, '\"')), '}') AS 'alternate'",
			'JOIN'     => 'LEFT JOIN t_article b ON t_article.id = b.id',
			'WHERE'    => ['t_article.status' => 'public'],
			'GROUP BY' => 't_article.id, t_article.lang'
		]);

		return $urls;
	}

}