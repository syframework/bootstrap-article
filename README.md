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

Copy template files into your project templates directory: ```protected/templates/Application/Page/content```

### 4. Translations

Each Article component embeds its own translator (`lang/bootstrap-article`). The section titles are owned by the `Author`, `Share`, `Side` and `Categories` components, so no page-level translator registration is required.

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
		$components = [
			'NAV'         => new \Sy\Bootstrap\Component\Article\Categories('articles'),
			'SEARCH_FORM' => new \Sy\Bootstrap\Component\Article\Search(),
			'FEED'        => new \Sy\Bootstrap\Component\Article\Feed(),
		];

		// Add article modal button
		$service = \Project\Service\Container::getInstance();
		if ($service->user->getCurrentUser()->hasPermission('article-create')) {
			$components['ADD_FORM'] = new \Sy\Bootstrap\Component\Article\Add(['class' => 'mb-3']);
		}

		$this->setContentVars($components);
	}

	/**
	 * Article page
	 */
	public function articleAction() {
		// Redirection if no article id provided
		$id = $this->get('id');
		if (is_null($id)) throw new \Sy\Bootstrap\Application\Page\NotFoundException();

		// Detect language
		$service = \Project\Service\Container::getInstance();
		$lang = $service->lang->getLang();

		// Retrieve article
		$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);

		if (empty($article)) {
			$lang = LANG;
			$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);
		}
		if (empty($article)) throw new \Sy\Bootstrap\Application\Page\NotFoundException();

		// The article may come from the language fallback: use its real lang.
		$lang = $article['lang'];

		// Article content
		$content = new \Sy\Bootstrap\Component\Article\Content($id, $lang);

		$this->setContentVars([
			'ARTICLE_BREADCRUMB' => new \Sy\Bootstrap\Component\Article\Breadcrumb($id, $lang),
			'ARTICLE_CONTENT'    => $content,
			'ARTICLE_AUTHOR'     => new \Sy\Bootstrap\Component\Article\Author($article['user_id']),
			'SIDE'               => new \Sy\Bootstrap\Component\Article\Side($id, $article['category_id']),
			'SHARE'              => new \Sy\Bootstrap\Component\Article\Share(PROJECT_URL . Url::build('page', 'article', ['id' => $id])),
		]);
	}
```


## Component allowlist

By default, Article neutralizes every server-side component (`data-sycomponent`). A project can authorize its own components in `protected/conf/conf.php`:

```php
define('WYSIWYG_COMPONENT_ALLOWLIST', [
	'article' => [\Project\Component\Article\Map::class],
]);
```

The entries are merged with the package default (empty for Article), and the editor's `components` option is enabled automatically when the resulting list is not empty. Allowed components are instantiated and rendered by `ComponentRenderer`; their `data-sycomponent` wrapper is kept so they are rebuilt after a save even when `HtmlSanitizer` removes the rendered form controls. While editing, the `sycomponent` widget masks the rendered component and saves the source slot instead of the rendered form; a successful save reloads the page so the component is rebuilt server-side. The `Sycomponent` dialog lists readable component names and renders one typed field per constructor parameter; components without parameters hide the arguments area, and the source slot is generated from the class name (`{INLINE_STYLE}`). After an insertion or an edit, the editor previews the final component through `/editor/article/render`, which checks the allowlist and the article permission before instantiating the component. The action comes from the `Sy\Bootstrap\Application\Editor\Render` trait (wysiwyg): `Application\Editor\Article` only declares its page language (`renderLanguage()`) and its translator directories (`renderTranslators()`). The endpoint uses the article language, so the rendered labels match the page translations. Double-click a component or use the **Edit component** context-menu entry to reopen the dialog with its current arguments. The CKEditor interface follows the connected user's `language` when available, falling back to the display language for guests; the article language used by `load-data`/`save-data` is unchanged. The same constant also accepts `content` (CMS) and `place` keys.

## Add URL converter in Application.php

In ```protected/src/Application.php```:
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

Converters are evaluated in registration order. `ArticleConverter` must be registered **before** `ControllerActionConverter`, which is the terminal fallback; its position relative to `AliasConverter` depends on whether aliases should take precedence over article URLs. With this configuration, an article is reachable as `/a/<alias>` and is mapped to `p=page`, `a=article`, `id=<id>` and `lang=<article language>`.

Article URLs have no language segment: `ArticleConverter` sets the current language from the article, so the page is rendered in the article language. See the [URL Converters](https://github.com/syframework/bootstrap/wiki/URL-Converters) wiki page for the full converter contract, ordering rules and `Url::build()` examples.

## Add the articles sitemap in Sitemap.php

In ```protected/src/Application/Sitemap.php```

```php
<?php
namespace Project\Application;

class Sitemap extends \Sy\Bootstrap\Application\Sitemap {

	public function __construct() {
		parent::__construct();
		$this->addProvider(new \Sy\Bootstrap\Application\Sitemap\Page());
		$this->addProvider(new \Sy\Bootstrap\Application\Sitemap\Article()); // Add the articles sitemap
	}

}
```

`Article` adds one entry to the sitemap index (`<project>/sitemap/article`) and lists every `public` article, one entry per language: `<loc>` is the article URL (`/a/<alias>`), `<lastmod>` is its `updated_at` date, and `<alternate>` lists every translation of the same article (the join is not filtered on the translation status).

The provider must be registered: `Sitemap` resolves the requested action to `Project\Application\Sitemap\<Action>` then `Sy\Bootstrap\Application\Sitemap\<Action>`, and answers 404 when no provider class matches the action or when that class was never added with `addProvider()`. An unregistered Article sitemap is therefore a 404, not an empty file; leaving it out of `Sitemap.php` also keeps the article URLs out of the sitemap index.
