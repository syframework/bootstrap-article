<?php
namespace Sy\Bootstrap\Component\Article;

class Update extends \Sy\Bootstrap\Component\Form\Crud {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @param int $id Article id
	 * @param string $lang Article language
	 */
	public function __construct($id, $lang) {
		parent::__construct('article', ['id' => $id, 'lang' => $lang]);
		$this->id = $id;
		$this->lang = $lang;
	}

	public function init() {
		// Title
		$this->getField('title')->setAttributes([
			'maxlength' => '128',
			'required'  => 'required',
		]);
		$this->getField('title')->addValidator(function($value) {
			if (strlen($value) <= 128) return true;
			$this->setError($this->_('128 characters max for title'));
			return false;
		});

		// Description
		$this->getField('description')->setAttribute('maxlength', '256');
		$this->getField('description')->addValidator(function($value) {
			if (strlen($value) <= 512) return true;
			$this->setError($this->_('512 characters max for description'));
			return false;
		});

		// Article category
		$select = $this->getField('category_id');
		$select->setOption('label', $this->_('Category'));
		$select->addOption('');
		$service = \Project\Service\Container::getInstance();
		$article = $service->article->retrieve(['id' => $this->id, 'lang' => $this->lang]);
		foreach ($service->articleCategory->retrieveAll() as $category) {
			$o = $select->addOption((empty($category['parent']) ? '' : ' - ') . $this->_($category['name']), $category['id']);
			if ($article['category_id'] === $category['id']) $o->setAttribute('selected', 'selected');
		}

		// Article alias
		$this->addTextInput(['name' => 'form[alias]', 'value' => $article['alias']], ['label' => 'Alias', 'validator' => function($value) {
			if (preg_match('/^[a-z0-9\-]*$/', $value) === 1) return true;
			$this->setError($this->_('Unauthorized character in the alias'));
			return false;
		}]);

		// Article status
		if ($service->user->getCurrentUser()->hasPermission('article-status')) {
			$this->addSelect(['name' => 'form[status]'], ['selected' => $article['status'], 'label' => 'Status', 'options' => [
				'draft'  => $this->_('Draft'),
				'public' => $this->_('Public')
			]]);
		}

		// Article published_at
		if ($service->user->getCurrentUser()->hasPermission('article-status')) {
			$this->addDateTime(['name' => 'form[published_at]', 'value' => $article['published_at']], ['label' => 'Published at']);
		}
	}

	public function submitAction() {
		$service = \Project\Service\Container::getInstance();
		try {
			$this->validatePost();
			$fields = $this->post('form');

			// Filter fields
			if (!$service->user->getCurrentUser()->hasPermission('article-status')) {
				unset($fields['status']);
			}
			if (empty($fields['category_id'])) {
				$fields['category_id'] = null;
			}

			// Remove newline in description
			$fields['description'] = preg_replace('/\s+/', ' ', $fields['description']);

			// Convert datetime
			$date = new \Sy\Bootstrap\Lib\Date($fields['published_at']);
			$fields['published_at'] = $date->f("yyyy-MM-dd HH:mm");

			$this->updateRow($fields);

			return $this->jsonSuccess('Saved', [
				'redirection' => \Sy\Bootstrap\Lib\Url::build('page', 'article', ['id' => $this->id]),
			]);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError($this->getOption('error') ?? 'Please fill the form correctly');
		} catch (\Sy\Db\MySql\DuplicateEntryException $e) {
			$this->logWarning($e);
			return $this->jsonError('Alias already exists');
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Database error');
		}
	}

}