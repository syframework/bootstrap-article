<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Lib\Image;

class Article extends \Sy\Bootstrap\Component\Api {

	use CkFile;
	use Render;

	public function security() {
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		if (is_null($this->request('id'))) {
			throw new \Sy\Bootstrap\Component\Api\RequestErrorException('Missing article id parameter');
		}

		$article = $service->article->retrieve(['id' => $this->request('id'), 'lang' => $this->language()]);

		if (!$user->hasPermission('article-update') and (int) $user->id !== (int) ($article['user_id'] ?? 0)) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Permission denied');
		}
	}

	/**
	 * Language of the article being edited.
	 *
	 * The render URL carries it (`?lang=`) so the preview uses the same
	 * translations as the page, even when the editor UI follows another user
	 * preference.
	 *
	 * @return string
	 */
	protected function renderLanguage(): string {
		return $this->language();
	}

	/**
	 * Article labels come from the page translator and the package one, both in
	 * the article language.
	 *
	 * @param string $lang
	 * @return string[]
	 */
	protected function renderTranslators(string $lang): array {
		return [LANG_DIR, __DIR__ . '/../../../lang/bootstrap-article'];
	}

	private function language() {
		$service = \Project\Service\Container::getInstance();
		$lang    = $this->request('lang');
		if (is_string($lang) and $service->lang->isAvailable($lang)) return $lang;
		return $service->lang->getLang();
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
