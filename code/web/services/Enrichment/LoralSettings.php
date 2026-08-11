<?php

require_once ROOT_DIR . '/services/Enrichment/CoverProviderEditor.php';
require_once ROOT_DIR . '/sys/Enrichment/LoralSetting.php';

class Enrichment_LoralSettings extends Enrichment_CoverProviderEditor {
	function getObjectType(): string {
		return 'LoralSetting';
	}

	function getToolName(): string {
		return 'LoralSettings';
	}

	function getPageTitle(): string {
		return 'Loral Settings';
	}

	function canAddNew(): bool {
		return $this->getNumObjects() == 0;
	}
}
