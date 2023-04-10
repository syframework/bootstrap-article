<?php
namespace Sy\Bootstrap\Application\Api;

use Project\Service\Container;

class Article extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$service = Container::getInstance();
		$user = $service->user->getCurrentUser();
		if (is_null($this->request('id')) or is_null($this->request('lang'))) {
			throw new \Sy\Bootstrap\Component\Api\RequestErrorException('Missing article id or lang parameter');
		}

		$article = $service->article->retrieve(['id' => $this->request('id'), 'lang' => $this->request('lang')]);

		if (!$user->hasPermission('article-update') and $user->id !== $article['user_id']) {
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
				'content' => $article['content'],
			]);
		} catch (\Sy\Db\MySql\Exception $e) {
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
					'csrf'    => $service->user->getCsrfToken()
				]);
			}
			if (is_null($id) or is_null($lang) or is_null($content)) $this->requestError();

			// Create article revision
			$service->articleHistory->change([
				'user_id'         => $service->user->getCurrentUser()->id,
				'article_id'      => $id,
				'article_lang'    => $lang,
				'article_crc32'   => crc32($content),
				'article_content' => $content,
			], [
				'user_id'    => $service->user->getCurrentUser()->id,
				'updated_at' => date('Y-m-d H:i:s')
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
		} catch (\Sy\Db\MySql\Exception $e) {
			return $this->serverError([
				'status' => 'ko',
				'message' => $this->_('Database error')
			]);
		}
	}

}