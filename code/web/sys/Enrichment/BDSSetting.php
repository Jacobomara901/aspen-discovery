<?php /** @noinspection PhpMissingFieldTypeInspection */

require_once ROOT_DIR . '/sys/DB/LibraryScopedSetting.php';

class BDSSetting extends DataObject {
	use LibraryScopedSetting;

	public $__table = 'bds_settings';    // table name
	public $id;
	public $name;
	public $dbmCode;
	public $enabled;

	static $_objectStructure = [];

	protected function getLibraryLinkColumn(): string {
		return 'bdsSettingId';
	}

	public function getEncryptedFieldNames() : array {
		return [
			'dbmCode',
		];
	}

	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}

		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'description' => 'A Name for the BDS Subscription for internal use',
				'maxlength' => 255,
				'required' => true,
			],
			'dbmCode' => [
				'property' => 'dbmCode',
				'type' => 'storedPassword',
				'label' => 'BDS DBM Code',
				'description' => 'The customer-issued BDS DBM code used to authenticate cover image requests.',
				'hideInLists' => true,
				'maxLength' => 50,
			],
			'enabled' => [
				'property' => 'enabled',
				'type' => 'checkbox',
				'label' => 'Integration Enabled',
				'description' => 'Whether BDS cover image integration is enabled',
				'default' => 1,
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that can use these settings',
				'values' => $libraryList,
				'hideInLists' => false,
				'forcesReindex' => false,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}
}
