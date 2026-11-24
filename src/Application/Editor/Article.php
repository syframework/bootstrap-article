<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Lib\Image;
use Sy\Bootstrap\Wysiwyg\Application\Editor\CkFile;

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
		$result = $this->processUpload();

		if (empty($result['message']) and $result['type'] === 'image' and !empty($result['path'])) {
			$this->resizeImage($result['path']);
		}

		return $this->uploadResponse($result);
	}

	/**
	 * Downscale an image to 900px max, keeping the aspect ratio.
	 *
	 * @param string $path
	 */
	private function resizeImage($path) {
		list($w, $h) = getimagesize($path);
		if ($w === false or $h === false) return;

		$max = 900;
		if (max([$w, $h]) > $max) {
			if ($h > $w) {
				$w = $max * $w / $h;
				$h = $max;
			} else {
				$h = $max * $h / $w;
				$w = $max;
			}
			Image::resize($path, (int) $w, (int) $h, strtolower(pathinfo($path, PATHINFO_EXTENSION)));
		}
	}

}
