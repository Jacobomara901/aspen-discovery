<?php

require_once ROOT_DIR . '/services/Enrichment/CoverProviderEditor.php';
require_once ROOT_DIR . '/sys/Enrichment/BDSSetting.php';

class Enrichment_BDSSettings extends Enrichment_CoverProviderEditor {
	function getObjectType(): string {
		return 'BDSSetting';
	}

	function getToolName(): string {
		return 'BDSSettings';
	}

	function getPageTitle(): string {
		return 'BDS Settings';
	}

	public function getViewPermissions(): array {
		return [
			'Administer Third Party Enrichment API Keys',
			'Administer BDS',
		];
	}
}
