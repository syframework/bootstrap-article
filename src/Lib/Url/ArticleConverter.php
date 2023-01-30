<?php
namespace Sy\Bootstrap\Lib\Url;

class ArticleConverter implements IConverter {

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @param string $prefix
	 */
	public function __construct($prefix = 'a/') {
		$this->prefix = $prefix;
	}

	/**
	 * {@inheritDoc}
	 */
	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return false;
		if ($params[CONTROLLER_TRIGGER] !== 'page') return false;
		unset($params[CONTROLLER_TRIGGER]);

		if (empty($params[ACTION_TRIGGER])) return false;
		if ($params[ACTION_TRIGGER] !== 'article') return false;
		unset($params[ACTION_TRIGGER]);

		if (empty($params['id'])) return false;
		$id = $params['id'];
		unset($params['id']);

		if (!empty($params['alias'])) {
			$url = WEB_ROOT . '/' . $this->prefix . $params['alias'];
			unset($params['alias']);
			return $url . (empty($params) ? '' : '?' . http_build_query($params));
		}

		$lang = $params['lang'] ?? \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		unset($params['lang']);
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);
		if (empty($article['alias'])) $article = $service->article->retrieve(['id' => $id, 'lang' => LANG]);
		if (empty($article['alias'])) return false;
		return WEB_ROOT . '/' . $this->prefix . $article['alias'] . (empty($params) ? '' : '?' . http_build_query($params));
	}

	/**
	 * {@inheritDoc}
	 */
	public function urlToParams($url) {
		$uri = parse_url($url, PHP_URL_PATH);
		$queryString = parse_url($url, PHP_URL_QUERY);

		list($alias) = sscanf(substr($uri, strlen(WEB_ROOT) + 1), $this->prefix . "%s");
		if (empty($alias)) return false;

		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['alias' => $alias]);
		if (empty($article)) return false;

		$params[CONTROLLER_TRIGGER] = 'page';
		$params[ACTION_TRIGGER] = 'article';
		$params['id'] = $article['id'];
		$params['lang'] = $article['lang'];
		$service->user->setLanguage($article['lang']);

		$queryParams = [];
		if (!is_null($queryString)) parse_str($queryString, $queryParams);

		return $params + $queryParams;
	}

}