<?php /** @noinspection PhpMissingFieldTypeInspection */

require_once ROOT_DIR . '/sys/DB/LibraryScopedSetting.php';

class LoralSetting extends DataObject {
	use LibraryScopedSetting;

	public $__table = 'loral_settings';    // table name
	public $id;
	public $name;
	public $loralUrl;
	public $loralId;
	public $password;
	public $enabled;

	static $_objectStructure = [];

	protected function getLibraryLinkColumn(): string {
		return 'loralSettingId';
	}

	public function getEncryptedFieldNames() : array {
		return [
			'password',
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
				'description' => 'A Name for the Loral Subscription for internal use',
				'maxlength' => 255,
				'required' => true,
			],
			'loralUrl' => [
				'property' => 'loralUrl',
				'type' => 'url',
				'label' => 'Loral Url',
				'description' => 'The Base URL of the Loral Server',
				'maxLength' => 255,
			],
			'loralId' => [
				'property' => 'loralId',
				'type' => 'text',
				'label' => 'Loral Id',
				'description' => 'The ID of the Loral subscription',
			],
			'password' => [
				'property' => 'password',
				'type' => 'storedPassword',
				'label' => 'Loral Password',
				'description' => 'The password for accessing the API',
				'hideInLists' => true,
				'maxLength' => 50,
			],
			'enabled' => [
				'property' => 'enabled',
				'type' => 'checkbox',
				'label' => 'Integration Enabled',
				'description' => 'Whether Loral integration is enabled',
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
				'forcesReindex' => true,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}
}
