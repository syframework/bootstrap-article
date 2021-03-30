<?php
namespace Sy\Bootstrap\Component\Article;

use Sy\Bootstrap\Lib\Url;

class Content extends \Sy\Component\WebComponent {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $lang;

	public function __construct($id, $lang) {
		parent::__construct();
		$this->id   = $id;
		$this->lang = $lang;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');

		// Template
		$this->setTemplateFile(__DIR__ . '/Content.html');

		// Javascript code
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/Content.js');

		// Retrieve article
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $this->id, 'lang' => $this->lang]);

		$user = $service->user->getCurrentUser();
		// Check read permission
		if ($article['status'] === 'draft') {
			if ($user->id !== $article['user_id'] and !$user->hasPermission('article-read')) {
				$this->redirect(Url::build('page', $this->default));
			}
		}

		// Set article title and description
		if (!empty($article['title'])) {
			$title = htmlentities($article['title'], ENT_QUOTES, 'UTF-8');
			\Sy\Bootstrap\Lib\HeadData::setTitle($title);
			\Sy\Bootstrap\Lib\HeadData::addMeta('og:title', $title);
			$this->setVar('TITLE', $title);
		}
		if (!empty($article['description'])) {
			$description = htmlentities($article['description'], ENT_QUOTES, 'UTF-8');
			\Sy\Bootstrap\Lib\HeadData::setDescription($description);
			\Sy\Bootstrap\Lib\HeadData::addMeta('og:description', $description);
			$this->setVar('DESCRIPTION', $description);
		}

		// Set meta url and type
		\Sy\Bootstrap\Lib\HeadData::addMeta('og:type', 'article');
		\Sy\Bootstrap\Lib\HeadData::addMeta('og:url', PROJECT_URL . Url::build('page', 'article', ['id' => $article['id']]));

		// Set meta og image
		if (is_dir(UPLOAD_DIR . "/article/image/${article['id']}")) {
			$files = array_diff(scandir(UPLOAD_DIR . "/article/image/${article['id']}"), ['.', '..']);

			// Sort $files oldest file to newest file
			array_multisort(array_map(function($file) use($article) {
				return filemtime(UPLOAD_DIR . "/article/image/${article['id']}/$file");
			}, $files), $files);

			$images = [];

			foreach ($files as $file) {
				if (\Sy\Bootstrap\Lib\Image::isImage(UPLOAD_DIR . "/article/image/${article['id']}/$file")) {
					$images[] = PROJECT_URL . UPLOAD_ROOT . "/article/image/${article['id']}/$file";
				}
			}
			if (!empty($images)) {
				\Sy\Bootstrap\Lib\HeadData::addMeta('og:image', $images[0]);
				$this->setVar('IMAGE', implode('","', $images));
				$this->setBlock('IMAGE_BLOCK');
			}
		}

		// Set article content
		$this->setVar('CONTENT', $article['content']);

		// Micro data
		$publishedAt = new \Sy\Bootstrap\Lib\Date($article['published_at']);
		$updatedAt   = new \Sy\Bootstrap\Lib\Date($article['updated_at']);
		$this->setVars([
			'PROJECT'      => PROJECT,
			'PROJECT_URL'  => PROJECT_URL,
			'ARTICLE_PAGE' => Url::build('page', 'articles'),
			'AUTHOR'       => $this->_(\Sy\Bootstrap\Lib\Str::convertName($article['user_firstname'] . ' ' . $article['user_lastname'])),
			'PUBLISHED_AT' => $publishedAt->f(\DateTime::ATOM),
			'UPDATED_AT'   => $updatedAt->f(\DateTime::ATOM),
			'PHUMANDATE'   => $publishedAt->humanTimeDiff(),
			'PTIMESTAMP'   => $publishedAt->timestamp(),
			'UHUMANDATE'   => $updatedAt->humanTimeDiff(),
			'UTIMESTAMP'   => $updatedAt->timestamp(),
			'AUTHOR_URL'   => \Sy\Bootstrap\Lib\Url::build('page', 'user', ['id' => $article['user_id']]),
		]);

		// Update
		if ($user->hasPermission('article-update') or $user->id === $article['user_id']) {
			$this->addJsLink(CKEDITOR_JS);
			$updateForm = new \Sy\Bootstrap\Component\Article\Update($this->id, $this->lang);

			$this->setComponent('UPDATE_ARTICLE_FORM', $updateForm);
			$js->setVars([
				'ID'              => $article['id'],
				'CSRF'            => $service->user->getCsrfToken(),
				'LANG'            => $article['lang'],
				'URL'             => Url::build('api', 'article'),
				'WEB_ROOT'        => WEB_ROOT,
				'IMG_BROWSE'      => Url::build('editor', 'browse', ['id' => $this->id, 'item' => 'article', 'type' => 'image']),
				'IMG_UPLOAD'      => Url::build('editor', 'upload', ['id' => $this->id, 'item' => 'article', 'type' => 'image']),
				'FILE_BROWSE'     => Url::build('editor', 'browse', ['id' => $this->id, 'item' => 'article', 'type' => 'file']),
				'FILE_UPLOAD'     => Url::build('editor', 'upload', ['id' => $this->id, 'item' => 'article', 'type' => 'file']),
				'IMG_UPLOAD_AJAX' => Url::build('editor', 'upload', ['id' => $this->id, 'item' => 'article', 'type' => 'image', 'json' => '']),
				'FILE_UPLOAD_AJAX'=> Url::build('editor', 'upload', ['id' => $this->id, 'item' => 'article', 'type' => 'file', 'json' => '']),
				'CKEDITOR_ROOT'   => CKEDITOR_ROOT,
			]);
			if (defined('IFRAMELY')) {
				$js->setVars([
					'IFRAMELY'     => IFRAMELY,
					'IFRAMELY_KEY' => IFRAMELY_KEY,
				]);
				$js->setBlock('IFRAMELY_BLOCK');
			}
			$js->setBlock('UPDATE_BLOCK');
			$this->setBlock('UPDATE_BTN_BLOCK');
			$this->setBlock('UPDATE_MODAL_BLOCK');
		}

		// Delete
		if ($user->hasPermission('article-delete') or $user->id === $article['user_id']) {
			$deleteForm = new \Sy\Bootstrap\Component\Form\Crud\Delete('article', ['id' => $this->id, 'lang' => $this->lang]);
			$deleteForm->setAttribute('id', 'delete-' . $this->id);
			$this->setComponent('DELETE_ARTICLE_FORM', $deleteForm);
			$this->setBlock('DELETE_BTN_BLOCK');
			$js->setVars([
				'CONFIRM_DELETE' => $this->_('Are you sure to delete this article?'),
				'DELETE_FORM_ID' => 'delete-' . $this->id
			]);
			$js->setBlock('DELETE_BLOCK');
		}

		// Add javascript code
		$this->addJsCode($js);
	}

}
