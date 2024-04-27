# sy/bootstrap-article

[sy/bootstrap](https://github.com/syframework/bootstrap) plugin for adding "Article" feature in your [sy/project](https://github.com/syframework/project) based application.

## Installation

From your sy/project based application directory, run this command:

```bash
composer install-plugin article
```
---
**NOTES**

The install-plugin command will do all these following steps:

### 1. Install package sy/bootstrap-article

```bash
composer require sy/bootstrap-article
```

### 2. Copy database installation file into your sql directory

Use the database installation script: ```sql/install.sql```

### 3. Copy template files

Copy template files into your project templates directory: ```protected/templates/Application/content```

### 4. Copy language files

Copy the language folder ```lang/bootstrap-article``` into your project language directory: ```protected/lang```

### 5. Copy SCSS files

Copy the scss file ```scss/_bootstrap-article.scss``` into your project scss directory: ```protected/scss```

Import it in your ```app.scss``` file and rebuild the css file.

### 6. Copy assets files

### 7. Run composer build

### 8. Run composer db migrate

---

## Page methods

Create 2 methods in your ```Project\Application\Page``` class (in ```protected/src/Application/Page.php```):

```php
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

		$this->setContentVars($components);
	}

	/**
	 * Article page
	 */
	public function articleAction() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');

		// Redirection if no article id provided
		$id = $this->get('id');
		if (is_null($id)) throw new \Sy\Bootstrap\Application\Page\NotFoundException();

		// Detect language
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();

		// Retrieve article
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);

		if (empty($article)) {
			$lang = LANG;
			$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);
		}
		if (empty($article)) throw new \Sy\Bootstrap\Application\Page\NotFoundException();

		// Article content
		$content = new \Sy\Bootstrap\Component\Article\Content($id, $lang);

		$this->setContentVars([
			'ARTICLE_BREADCRUMB' => new \Sy\Bootstrap\Component\Article\Breadcrumb($id, $lang),
			'ARTICLE_CONTENT'    => $content,
			'SIDE'               => new \Sy\Bootstrap\Component\Article\Side($id, $article['category_id']),
			'SHARE'              => new \Sy\Bootstrap\Component\Share\Buttons(PROJECT_URL . Url::build('page', 'article', ['id' => $id])),
		]);
	}
```


## Add URL converter in Application.php

In ```protected/src/Application.php```
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