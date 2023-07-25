<?php
namespace Sy\Bootstrap\Component\Article;

class Create extends \Sy\Bootstrap\Component\Form\Crud\Create {

	public function __construct() {
		parent::__construct('article');
	}

	public function init() {
		$this->addTranslator(LANG_DIR . '/bootstrap-article');
		parent::init();

		// Title
		$this->getField('title')->setAttributes([
			'maxlength' => '128',
			'required'  => 'required',
		]);

		// Description
		$this->getField('description')->setAttribute('maxlength', '256');

		// Article category
		$service = \Project\Service\Container::getInstance();
		$select = $this->getField('category_id');
		$select->addOption('');
		foreach ($service->articleCategory->retrieveAll() as $category) {
			$select->addOption((empty($category['parent']) ? '' : ' - ') . $this->_($category['name']), $category['id']);
		}
	}

	public function submitAction() {
		try {
			$service = \Project\Service\Container::getInstance();
			$user = $service->user->getCurrentUser();
			$this->validatePost();
			$fields = $this->post('form');
			$fields = array_filter($fields);
			$fields['user_id'] = $user->id;

			// Remove newline in description
			$fields['description'] = empty($fields['description']) ? '' : preg_replace('/\s+/', ' ', $fields['description']);

			$fields['content'] = '<h1>' . (empty(trim($fields['title'])) ? $this->_('Title') . ' <small>' . $this->_('Optional subtitle') . '</small>' : $fields['title']) . '</h1>'
				. '<p class="lead">' . (empty(trim($fields['description'])) ? $this->_('Lead paragraph') : $fields['description']) . '</p>'
				. '<p><img class="img-fluid rounded" src="https://picsum.photos/900/500" /></p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';

			// Article alias
			$title = trim($fields['title']);
			$fields['alias'] = \Sy\Bootstrap\Lib\Str::slugify($title);

			$id = $this->getService()->create($fields);
			$this->setSuccess($this->_('Article created successfully'), \Sy\Bootstrap\Lib\Url::build('page', 'article', ['id' => $id, 'alias' => $fields['alias']]));
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			if (is_null($this->getOption('error'))) {
				$this->setError($this->_('Please fill the form correctly'));
			}
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\DuplicateEntryException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Article already exists'));
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

}