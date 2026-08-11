<?php

/**
 * Shared behavior for settings that can be linked to one or more libraries
 * via a column on the library table (e.g. library.syndeticsSettingId).
 *
 * The using class must:
 *   - extend DataObject
 *   - implement getLibraryLinkColumn() returning the library column name
 *   - ensure Library.php is loadable
 */
trait LibraryScopedSetting {
	private $_libraries;

	abstract protected function getLibraryLinkColumn(): string;

	public function __get($name) {
		if ($name !== 'libraries') {
			return parent::__get($name);
		}
		$needsLoad = !isset($this->_libraries) && $this->id;
		if (!$needsLoad) {
			return $this->_libraries ?? null;
		}
		$column = $this->getLibraryLinkColumn();
		$this->_libraries = [];
		$obj = new Library();
		$obj->$column = $this->id;
		$obj->find();
		while ($obj->fetch()) {
			$this->_libraries[$obj->libraryId] = $obj->libraryId;
		}
		return $this->_libraries;
	}

	public function __set($name, $value) {
		if ($name !== 'libraries') {
			parent::__set($name, $value);
			return;
		}
		$this->_libraries = $value;
	}

	public function update(string $context = ''): bool|int {
		$ret = parent::update();
		if ($ret === FALSE) {
			return $ret;
		}
		$this->saveLibraries();
		return $ret;
	}

	public function insert(string $context = ''): int|bool {
		$ret = parent::insert();
		if ($ret === FALSE) {
			return $ret;
		}
		$this->saveLibraries();
		return $ret;
	}

	public function saveLibraries(): void {
		$hasPendingChanges = isset($this->_libraries) && is_array($this->_libraries);
		if (!$hasPendingChanges) {
			return;
		}
		$column = $this->getLibraryLinkColumn();
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		foreach ($libraryList as $libraryId => $displayName) {
			$library = new Library();
			$library->libraryId = $libraryId;
			$library->find(true);
			$needsLink = in_array($libraryId, $this->_libraries) && $library->$column != $this->id;
			$needsUnlink = !in_array($libraryId, $this->_libraries) && $library->$column == $this->id;
			if ($needsLink) {
				$library->$column = $this->id;
				$library->update();
			} elseif ($needsUnlink) {
				$library->$column = -1;
				$library->update();
			}
		}
		unset($this->_libraries);
	}
}
