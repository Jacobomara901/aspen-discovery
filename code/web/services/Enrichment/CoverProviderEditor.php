<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';

/**
 * Shared ObjectEditor base for cover-image provider admin pages under
 * Administration → Third Party Enrichment.
 */
abstract class Enrichment_CoverProviderEditor extends ObjectEditor {
	abstract function getObjectType(): string;
	abstract function getToolName(): string;
	abstract function getPageTitle(): string;

	/** Label for the trailing breadcrumb; defaults to the page title. */
	protected function getBreadcrumbLabel(): string {
		return $this->getPageTitle();
	}

	function getModule(): string {
		return 'Enrichment';
	}

	function getAllObjects(int $page, int $recordsPerPage): array {
		$className = $this->getObjectType();
		$object = new $className();
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$this->applyFilters($object);
		$object->orderBy($this->getSort());
		$object->find();
		$objectList = [];
		while ($object->fetch()) {
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'id asc';
	}

	function getObjectStructure($context = ''): array {
		$className = $this->getObjectType();
		return $className::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getAdditionalObjectActions(?DataObject $existingObject): array {
		return [];
	}

	function getInstructions(): string {
		return 'https://aspen-discovery.atlassian.net/wiki/spaces/Help/pages/328105985/Third+Party+Enrichment';
	}

	function getBreadcrumbs(): array {
		return [
			new Breadcrumb('/Admin/Home', 'Administration Home'),
			new Breadcrumb('/Admin/Home#third_party_enrichment', 'Third Party Enrichment'),
			new Breadcrumb('/Enrichment/' . $this->getToolName(), $this->getBreadcrumbLabel()),
		];
	}

	function getActiveAdminSection(): string {
		return 'third_party_enrichment';
	}

	public function getViewPermissions(): array {
		return ['Administer Third Party Enrichment API Keys'];
	}
}
