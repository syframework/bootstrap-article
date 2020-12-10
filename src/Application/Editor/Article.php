<?php
namespace Sy\Bootstrap\Application\Editor;

class Article {

	public function authorized($id) {
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		$article = $service->article->retrieve(['id' => $id]);
		if ((int) $user->id === (int) $article['user_id']) return true;
		return $user->hasPermission('article-update');
	}

}