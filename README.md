# sy/bootstrap-article

Plugin for adding Article feature

## Installation

```bash
$ composer require sy/bootstrap-article
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
	public function articles() {
		$components = [
			'NAV'         => new \Sy\Bootstrap\Component\Article\Nav('articles'),
			'SEARCH_FORM' => new \Sy\Bootstrap\Component\Article\Search(),
			'FEED'        => new \Sy\Bootstrap\Component\Article\Feed(),
		];

		// Add article modal button
		$service = \Project\Service\Container::getInstance();
		if ($service->user->getCurrentUser()->hasPermission('article-create')) {
			$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
			$create = new \Sy\Bootstrap\Component\Article\Create();
			$create->getField('lang')->setAttribute('value', $lang);
			$add = new \Sy\Bootstrap\Component\Modal\Button('addArticleModal', 'New article', 'plus');
			$add->getDialog()->setBody($create);
			$components['ADD_FORM'] = $add;
		}

		$this->__call('articles', ['CONTENT' => $components]);
	}

	/**
	 * Article page
	 */
	public function article() {
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
		]]);
	}

	// ...
}
```

## Language files

Copy the language folder ```lang/bootstrap-article``` into your project language directory: ```protected/lang```