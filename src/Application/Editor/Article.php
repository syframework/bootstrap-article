<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Lib\Str;

class Article extends \Sy\Bootstrap\Component\Api {

	use CkFile;

	public function security() {
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		if (is_null($this->request('id'))) {
			throw new \Sy\Bootstrap\Component\Api\RequestErrorException('Missing article id parameter');
		}

		$article = $service->article->retrieve(['id' => $this->request('id'), 'lang' => LANG]);

		if (!$user->hasPermission('article-update') and $user->id !== $article['user_id']) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Permission denied');
		}
	}

	/**
	 * CKEditor Upload
	 */
	public function uploadAction() {
		$func = $this->get('CKEditorFuncNum');
		$id   = $this->get('id');
		$item = str_replace('_', '-', Str::camlToSnake($this->action));
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
				$file = Str::slugify($parts['filename']) . '.' . strtolower($parts['extension']);
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
				'url'      => $url,
			];

			if (!empty($message)) $res['error']['message'] = $message;
			return $this->ok($res);
		} else {
			// Works for ckeditor <= 4.8
			return $this->ok("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($func, '$url', '$message');</script>");
		}
	}

}