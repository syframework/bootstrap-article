<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Service\Container;

class Article extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$service = Container::getInstance();
		$user = $service->user->getCurrentUser();
		if (is_null($this->request('id'))) $this->requestError();

		$article = $service->article->retrieve(['id' => $this->request('id'), 'lang' => LANG]);

		if (!$user->hasPermission('article-update') and $user->id !== $article['user_id']) {
			$this->forbidden([
				'status' => 'ko',
				'message' => $this->_('Permission denied')
			]);
		}
	}

	public function dispatch() {
		$this->actionDispatch(ACTION_TRIGGER);
	}

	/**
	 * CKEditor Upload
	 */
	public function uploadAction() {
		$func = $this->get('CKEditorFuncNum');
		$id   = $this->get('id');
		$item = $this->get('item');
		$type = $this->get('type');

		$url = '';
		$message = 'error';

		try {
			if (!is_null($id)) {
				$parts = pathinfo($_FILES['upload']['name']);
				switch ($type) {
					case 'image':
						$checkfile = '\Sy\Bootstrap\Lib\Image::isImage';
						break;
					default:
						$checkfile = null;
						break;
				}
				$file = \Sy\Bootstrap\Lib\Str::slugify($parts['filename']) . '.' . strtolower($parts['extension']);
				\Sy\Bootstrap\Lib\Upload::proceed(UPLOAD_DIR . "/$item/$type/$id/$file", 'upload', $checkfile);

				// resize image
				if ($type === 'image') {
					list($w, $h) = getimagesize(UPLOAD_DIR . "/$item/$type/$id/$file");
					$max = 900;
					if (max([$w, $h]) > $max) {
						if ($h > $w) {
							$w = $max * $w / $h;
							$h = $max;
						} else {
							$h = $max * $h / $w;
							$w = $max;
						}
					}
					\Sy\Bootstrap\Lib\Image::resize(UPLOAD_DIR . "/$item/$type/$id/$file", $w, $h);
				}

				$url = UPLOAD_ROOT . "/$item/$type/$id/$file";
				$message = '';
			}
		} catch (\Sy\Bootstrap\Lib\Upload\Exception $e) {
			$message = $e->getMessage();
		}

		// Ckeditor 4.9+ works only json response
		if (isset($_GET['json'])) {
			$res = [
				'uploaded' => (empty($message) ? 1 : 0),
				'filename' => $file,
				'url' => $url
			];

			if (!empty($message)) $res['error']['message'] = $message;
			echo json_encode($res);
		} else {
			// Works for ckeditor <= 4.8
			echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($func, '$url', '$message');</script>";
		}

		exit;
	}

}