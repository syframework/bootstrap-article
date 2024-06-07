<?php
namespace Sy\Bootstrap\Component\Article\Feed;

class Item extends \Sy\Component\WebComponent {

	/**
	 * @var int
	 */
	private $page;

	/**
	 * @var array
	 */
	private $article;

	/**
	 * @param array $article
	 * @param int $page
	 */
	public function __construct($article, $page = 0) {
		parent::__construct();
		$this->page    = $page;
		$this->article = $article;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(__DIR__ . '/../../../../lang/bootstrap-article');
		$this->setTemplateFile(__DIR__ . '/Item.html');

		$article = $this->article;
		$params = ['id' => $article['id']];
		if (!empty($article['alias'])) {
			$params['alias'] = $article['alias'];
		}
		$date = new \Sy\Bootstrap\Lib\Date($article['published_at']);
		$this->setVars([
			'ID'          => $this->page + 1,
			'TITLE'       => htmlentities($article['title'], ENT_QUOTES, 'UTF-8'),
			'DESCRIPTION' => htmlentities($article['description'], ENT_QUOTES, 'UTF-8'),
			'LINK'        => \Sy\Bootstrap\Lib\Url::build('page', 'article', $params),
			'AUTHOR'      => $this->_(\Sy\Bootstrap\Lib\Str::convertName($article['user_firstname'] . ' ' . $article['user_lastname'])),
			'DATE'        => $date->humanTimeDiff(),
			'DATETIME'    => $date->timestamp(),
			'CATEGORY'    => $this->_($article['category']),
		]);

		// Nb message
		if (isset($article['nb_message'])) {
			$this->setVar('NB_MESSAGE', $article['nb_message'] . ' ' . ($article['nb_message'] > 1 ?  $this->_('messages') : $this->_('message')));
			$this->setBlock('NB_MESSAGE_BLOCK');
		}

		if ($article['status'] === 'draft') {
			$this->setBlock('DRAFT_BLOCK');
		}

		if (is_dir(UPLOAD_DIR . "/article/image/{$article['id']}")) {
			$files = array_diff(scandir(UPLOAD_DIR . "/article/image/{$article['id']}"), ['.', '..']);

			// Sort $files oldest file to newest file
			array_multisort(array_map(function($file) use($article) {
				return filemtime(UPLOAD_DIR . "/article/image/{$article['id']}/$file");
			}, $files), $files);

			foreach ($files as $file) {
				if (\Sy\Bootstrap\Lib\Image::isImage(UPLOAD_DIR . "/article/image/{$article['id']}/$file")) {
					$this->setVar('IMG', UPLOAD_ROOT . "/article/image/{$article['id']}/$file");
					$this->setBlock('IMG_BLOCK');
					break;
				}
			}
		}
	}

}