# sy/bootstrap-article

[sy/bootstrap](https://github.com/syframework/bootstrap) plugin for adding "Article" feature in your [sy/project](https://github.com/syframework/project) based application.

## Installation

```bash
composer require sy/bootstrap-article
```

## Database

Use the database installation script: ```sql/install.sql```

## Template files

Copy template files into your project templates directory: ```protected/templates/Application/content```

## Page methods

Create 2 methods in your ```Project\Application\Page``` class:

```php
<?php
namespace Project\Application;

class Page extends \Sy\Bootstrap\Application\Page {

	/**
	 * List of all articles page
	 */
	public function articlesAction() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');

		$components = [
			'NAV'         => new \Sy\Bootstrap\Component\Article\Nav('articles'),
			'SEARCH_FORM' => new \Sy\Bootstrap\Component\Article\Search(),
			'FEED'        => new \Sy\Bootstrap\Component\Article\Feed(),
		];

		// Add article modal button
		$service = \Project\Service\Container::getInstance();
		if ($service->user->getCurrentUser()->hasPermission('article-create')) {
			$components['ADD_FORM'] = new \Sy\Bootstrap\Component\Article\Add();
		}

		$this->__call('articles', ['CONTENT' => $components]);
	}

	/**
	 * Article page
	 */
	public function articleAction() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');

		// Redirection if no article id provided
		$id = $this->get('id');
		if (is_null($id)) throw new \Sy\Bootstrap\Application\PageNotFoundException();

		// Detect language
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();

		// Retrieve article
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);

		if (empty($article)) {
			$lang = LANG;
			$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);
		}
		if (empty($article)) throw new \Sy\Bootstrap\Application\PageNotFoundException();

		// Article content
		$content = new \Sy\Bootstrap\Component\Article\Content($id, $lang);

		$this->__call('article', ['CONTENT' => [
			'ARTICLE_BREADCRUMB' => new \Sy\Bootstrap\Component\Article\Breadcrumb($id, $lang),
			'ARTICLE_CONTENT'    => $content,
			'SIDE'               => new \Sy\Bootstrap\Component\Article\Side($id, $article['category_id']),
			'SHARE'              => new \Sy\Bootstrap\Component\Share\Buttons(PROJECT_URL . Url::build('page', 'article', ['id' => $id])),
		]]);
	}

	// ...
}
```

## Language files

Copy the language folder ```lang/bootstrap-article``` into your project language directory: ```protected/lang```

## CSS

Copy the scss file ```scss/_bootstrap-article.scss``` into your project scss directory: ```protected/scss```

Import it in your ```app.scss``` file and rebuild the css file.

## Add URL converter in Application.php

```php
<?php
namespace Project;

use Sy\Bootstrap\Lib\Url;

class Application extends \Sy\Bootstrap\Application {

	protected function initUrlConverter() {
		Url\AliasManager::setAliasFile(__DIR__ . '/../conf/alias.php');
		Url::addConverter(new Url\AliasConverter());
		Url::addConverter(new Url\ArticleConverter()); // Add article converter
		Url::addConverter(new Url\ControllerActionConverter());
	}

}
```