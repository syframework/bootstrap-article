<?php
namespace Sy\Bootstrap\Lib\Url;

class ArticleConverter implements IConverter {

	private $prefix;

	public function __construct($prefix = 'a/') {
		$this->prefix = $prefix;
	}

	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return null;
		if ($params[CONTROLLER_TRIGGER] !== 'page') return null;
		unset($params[CONTROLLER_TRIGGER]);

		if (empty($params[ACTION_TRIGGER])) return null;
		if ($params[ACTION_TRIGGER] !== 'article') return null;
		unset($params[ACTION_TRIGGER]);

		if (empty($params['id'])) return null;
		$id = $params['id'];
		unset($params['id']);

		if (!empty($params['alias'])) {
			$url = WEB_ROOT . '/' . $this->prefix . $params['alias'];
			unset($params['alias']);
			return $url . (empty($params) ? '' : '?' . http_build_query($params));
		}

		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);
		if (empty($article['alias'])) $article = $service->article->retrieve(['id' => $id, 'lang' => LANG]);
		if (empty($article['alias'])) return null;
		return WEB_ROOT . '/' . $this->prefix . $article['alias'] . (empty($params) ? '' : '?' . http_build_query($params));
	}

	public function urlToParams($url) {
		list($uri) = explode('?', $url, 2);
		list($alias) = sscanf(substr($uri, strlen(WEB_ROOT) + 1), $this->prefix . "%s");
		if (empty($alias)) return false;

		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['alias' => $alias]);
		if (empty($article)) return false;

		$_REQUEST[CONTROLLER_TRIGGER] = 'page';
		$_GET[CONTROLLER_TRIGGER] = 'page';
		$_REQUEST[ACTION_TRIGGER] = 'article';
		$_GET[ACTION_TRIGGER] = 'article';
		$_REQUEST['id'] = $article['id'];
		$_GET['id'] = $article['id'];
		$service->user->setLanguage($article['lang']);
		return true;
	}

}