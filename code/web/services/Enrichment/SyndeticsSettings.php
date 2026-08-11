<?php

require_once ROOT_DIR . '/services/Enrichment/CoverProviderEditor.php';
require_once ROOT_DIR . '/sys/Enrichment/SyndeticsSetting.php';

class Enrichment_SyndeticsSettings extends Enrichment_CoverProviderEditor {
	function getObjectType(): string {
		return 'SyndeticsSetting';
	}

	function getToolName(): string {
		return 'SyndeticsSettings';
	}

	function getPageTitle(): string {
		return 'Syndetics Settings';
	}

	function getInitializationJs(): string {
		return 'AspenDiscovery.Admin.updateSyndeticsFields();';
	}
}
