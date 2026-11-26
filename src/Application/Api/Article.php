<?php
namespace Sy\Bootstrap\Application\Api;

use Project\Service\Container;
use Sy\Bootstrap\Lib\HtmlSanitizer;
use Sy\Bootstrap\Wysiwyg\Lib\ComponentFilter;

class Article extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$service = Container::getInstance();
		$user = $service->user->getCurrentUser();
		if (is_null($this->request('id')) or is_null($this->request('lang'))) {
			throw new \Sy\Bootstrap\Component\Api\RequestErrorException('Missing article id or lang parameter');
		}

		$article = $service->article->retrieve(['id' => $this->request('id'), 'lang' => $this->request('lang')]);

		if (!$user->hasPermission('article-update') and (int) $user->id !== (int) ($article['user_id'] ?? 0)) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Permission denied');
		}
	}

	public function getAction() {
		try {
			// Retrieve page
			$id   = $this->get('id');
			$lang = $this->get('lang');
			if (is_null($id) or is_null($lang)) {
				return $this->requestError([
					'status'  => 'ko',
					'message' => 'Missing id or lang parameter',
				]);
			}

			$service = Container::getInstance();
			$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);

			if (empty($article)) {
				return $this->notFound([
					'status'  => 'ko',
					'message' => 'Article not found',
				]);
			}

			return $this->ok([
				'status'  => 'ok',
				'content' => ComponentFilter::sanitize(
					HtmlSanitizer::sanitize($article['content']),
					ComponentFilter::allowlist('article')
				),
			]);
		} catch (\Sy\Db\MySql\Exception) {
			return $this->serverError([
				'status'  => 'ko',
				'message' => $this->_('Database error'),
			]);
		}
	}

	public function postAction() {
		$service = Container::getInstance();
		try {
			// Update page
			$id      = $this->post('id');
			$lang    = $this->post('lang');
			$content = $this->post('content');
			$csrf    = $this->post('csrf');
			if ($csrf !== $service->user->getCsrfToken()) {
				return $this->requestError([
					'status'  => 'ko',
					'message' => $this->_('You have taken too long to submit the form please try again'),
					'csrf'    => $service->user->getCsrfToken(),
				]);
			}
			if (is_null($id) or is_null($lang) or is_null($content)) {
				return $this->requestError([
					'status'  => 'ko',
					'message' => 'Missing id, lang or content parameter',
				]);
			}

			// Refuse a save on a translation that does not exist: the history
			// foreign key (article_id, article_lang) would otherwise fail.
			$article = $service->article->retrieve(['id' => $id, 'lang' => $lang]);
			if (empty($article) or $article['lang'] !== $lang) {
				return $this->notFound([
					'status'  => 'ko',
					'message' => 'Article not found',
				]);
			}

			try {
				$content = HtmlSanitizer::sanitize($content);
			} catch (\LengthException $e) {
				return $this->response(413, [
					'status'  => 'ko',
					'message' => 'Content too large',
				]);
			} catch (\InvalidArgumentException $e) {
				return $this->response(400, [
					'status'  => 'ko',
					'message' => 'Content is not valid UTF-8',
				]);
			}

			// Create article revision
			$service->articleHistory->change([
				'user_id'         => $service->user->getCurrentUser()->id,
				'article_id'      => $id,
				'article_lang'    => $lang,
				'article_crc32'   => crc32($content),
				'article_content' => $content,
			], [
				'user_id'    => $service->user->getCurrentUser()->id,
				'updated_at' => date('Y-m-d H:i:s'),
			]);

			// Update article content
			$service->article->update(
				['id' => $id, 'lang' => $lang],
				['content' => $content]
			);
			return $this->ok([
				'status'  => 'ok',
				'content' => $content,
			]);
		} catch (\Sy\Db\MySql\Exception) {
			return $this->serverError([
				'status'  => 'ko',
				'message' => $this->_('Database error'),
			]);
		}
	}

}