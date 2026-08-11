<?php

require_once ROOT_DIR . '/services/Enrichment/CoverProviderEditor.php';
require_once ROOT_DIR . '/sys/Enrichment/CoceServerSetting.php';

class Enrichment_CoceServerSettings extends Enrichment_CoverProviderEditor {
	function getObjectType(): string {
		return 'CoceServerSetting';
	}

	function getToolName(): string {
		return 'CoceServerSettings';
	}

	function getPageTitle(): string {
		return 'CoceServer Settings';
	}

	protected function getBreadcrumbLabel(): string {
		return 'Coce Server Settings';
	}

	function getInstructions(): string {
		return '';
	}

	function canAddNew(): bool {
		return $this->getNumObjects() == 0;
	}
}
