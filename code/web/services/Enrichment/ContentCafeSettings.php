<?php

require_once ROOT_DIR . '/services/Enrichment/CoverProviderEditor.php';
require_once ROOT_DIR . '/sys/Enrichment/ContentCafeSetting.php';

class Enrichment_ContentCafeSettings extends Enrichment_CoverProviderEditor {
	function getObjectType(): string {
		return 'ContentCafeSetting';
	}

	function getToolName(): string {
		return 'ContentCafeSettings';
	}

	function getPageTitle(): string {
		return 'ContentCafe Settings';
	}

	protected function getBreadcrumbLabel(): string {
		return 'Content Cafe Settings';
	}

	function canAddNew(): bool {
		return $this->getNumObjects() == 0;
	}
}
