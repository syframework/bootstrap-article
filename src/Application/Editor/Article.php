<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Lib\Image;
use Sy\Bootstrap\Wysiwyg\Lib\ComponentFilter;

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
	 * Render one allowlisted component for the editor preview.
	 *
	 * The editor sends the component class and the JSON arguments; the
	 * response contains the rendered component HTML, never the source slot.
	 */
	public function renderAction() {
		$component = $this->post('component');
		$args      = $this->post('args');
		$class     = ltrim(trim((string) $component), '\\');
		$allowlist = ComponentFilter::allowlist('article');

		if ($class === '' or !in_array($class, $allowlist, true) or !class_exists($class)) {
			return $this->requestError(['message' => 'Component not allowed']);
		}

		$decoded = [];
		if (is_string($args) and trim($args) !== '') {
			$decoded = json_decode($args, true);
			if (!is_array($decoded)) {
				return $this->requestError(['message' => 'Invalid component arguments']);
			}
		}

		try {
			$instance = new $class(...$decoded);
			$html     = (string) $instance;
		} catch (\Throwable $e) {
			$this->logWarning($e);
			return $this->requestError(['message' => 'Component render failed']);
		}

		return $this->ok(['status' => 'ok', 'html' => $html]);
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
