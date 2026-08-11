<?php

require_once ROOT_DIR . '/services/Enrichment/CoverProviderEditor.php';
require_once ROOT_DIR . '/sys/Enrichment/ChiliFreshSetting.php';

class Enrichment_ChiliFreshSettings extends Enrichment_CoverProviderEditor {
	function getObjectType(): string {
		return 'ChiliFreshSetting';
	}

	function getToolName(): string {
		return 'ChiliFreshSettings';
	}

	function getPageTitle(): string {
		return 'ChiliFresh Settings';
	}

	function canAddNew(): bool {
		return $this->getNumObjects() == 0;
	}
}
