<?php

require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Administration/Role.php';
require_once ROOT_DIR . '/sys/Administration/Permission.php';
require_once ROOT_DIR . '/sys/Administration/PermissionGroup.php';
require_once ROOT_DIR . '/sys/Administration/PermissionGroupPermission.php';

class Admin_Permissions extends Admin_Admin {
	function launch(): void {
		global $interface;
		global $enabledModules;

		$roles = [];
		$role = new Role();
		$role->orderBy('name');
		$role->find();
		/** @var Role $selectedRole */
		$selectedRole = null;
		while ($role->fetch()) {
			$roles[$role->roleId] = clone $role;
			if ($selectedRole == null) {
				$selectedRole = $roles[$role->roleId];
			}
			if (isset($_REQUEST['roleId']) && $_REQUEST['roleId'] == $role->roleId) {
				$selectedRole = $roles[$role->roleId];
			}
		}
		$interface->assign('selectedRole', $selectedRole);

		$permissionLabelsForSortingBySection = [];

		// Load definitions for mutually exclusive permission groups.
		$permissionGroups = self::loadPermissionGroups($permissionLabelsForSortingBySection);
		$interface->assign('permissionGroups', $permissionGroups);
		if (isset($_REQUEST['submit']) && $selectedRole != null) {
			if (isset($_REQUEST['permissionGroup'])) {
				foreach ($_REQUEST['permissionGroup'] as $groupKey => $selectedPermId) {
					if (isset($permissionGroups[$groupKey])) {
						// Remove any other permissions in this group.
						foreach ($permissionGroups[$groupKey]['permissions'] as $permName) {
							$permObj = new Permission();
							$permObj->name = $permName;
							if ($permObj->find(true)) {
								unset($_REQUEST['permission'][$permObj->id]);
							}
						}
						// Apply the selected permission if one was selected (i.e., not "None").
						if (!empty($selectedPermId)) {
							$_REQUEST['permission'][$selectedPermId] = 1;
						}
					}
				}
			}
			$selectedPermissions = [];
			foreach ($_REQUEST['permission'] as $permissionId => $selected) {
				if ($selected) {
					$selectedPermissions[] = $permissionId;
				}
			}
			$selectedRole->setActivePermissions($selectedPermissions);
		}
		$interface->assign('roles', $roles);
		$interface->assign('numRoles', count($roles));
		$permissions = [];
		$permission = new Permission();
		$permission->orderBy([
			'sectionName',
			'name',
		]);
		$permission->find();
		$selectedSections = [];
		while ($permission->fetch()) {
			if (!empty($permission->requiredModule) && !array_key_exists($permission->requiredModule, $enabledModules)) {
				continue;
			}
			//Determine if we should skip this permission because it's in a permission group or because it's deprecated (but not deleted)
			if ($permission->name == 'Administer ProPay') {
				continue;
			}
			if (!array_key_exists($permission->sectionName, $permissions)) {
				$permissions[$permission->sectionName] = [];
			}
			$inGroup = false;
			foreach ($permissionGroups as $group) {
				if (array_key_exists($permission->id, $group['permissions'])) {
					$inGroup = true;
					break;
				}
			}
			if ($inGroup) {
				continue;
			}

			if (!array_key_exists($permission->sectionName, $permissionLabelsForSortingBySection)) {
				$permissionLabelsForSortingBySection[$permission->sectionName] = [];
			}
			if ($selectedRole->hasPermission($permission->name)) {
				$selectedSections[$permission->sectionName] = $permission->sectionName;
			}
			$permissions[$permission->sectionName][$permission->id] = clone $permission;
			$permissionLabelsForSortingBySection[$permission->sectionName][$permission->name] = [
				'type' => 'permission',
				'id' => $permission->id
			];
		}

		$interface->assign('permissions', $permissions);
		$interface->assign('selectedSections', $selectedSections);
		foreach ($permissionLabelsForSortingBySection as $sectionName => $permissions) {
			$permissionLabelsForSortingBySection[$sectionName] = self::sortPermissionsHierarchically($permissionLabelsForSortingBySection[$sectionName], $permissionGroups);
		}
		ksort($permissionLabelsForSortingBySection, SORT_NATURAL | SORT_FLAG_CASE);
		$interface->assign('permissionLabelsForSortingBySection', $permissionLabelsForSortingBySection);

		$this->display('permissions.tpl', 'Permissions');

	}

	/**
	 * Loads mutually exclusive permission groups from the database.
	 * Each group contains sectionName, label, description, and a list of permission names.
	 *
	 * @return array<string,array{sectionName:string,label:string,description:string,permissions:string[]}>
	 */
	private static function loadPermissionGroups(&$permissionLabelsForSortingBySection): array {
		$groups = [];
		$groupLookup = [];

		$groupObj = new PermissionGroup();
		$groupObj->find();
		while ($groupObj->fetch()) {
			$groups[$groupObj->groupKey] = [
				'sectionName' => $groupObj->sectionName,
				'label' => $groupObj->label,
				'description' => $groupObj->description,
				'parentGroupKey' => $groupObj->parentGroupKey,
				'permissions' => [],
			];
			$groupLookup[$groupObj->id] = $groupObj->groupKey;
			if (!array_key_exists($groupObj->sectionName, $permissionLabelsForSortingBySection)) {
				$permissionLabelsForSortingBySection[$groupObj->sectionName] = [];
			}
			$permissionLabelsForSortingBySection[$groupObj->sectionName][$groupObj->label] = [
				'type' => 'group',
				'id' => $groupObj->groupKey
			];
		}

		$mapping = new PermissionGroupPermission();
		$mapping->find();
		while ($mapping->fetch()) {
			if (isset($groupLookup[$mapping->groupId])) {
				$groupKey = $groupLookup[$mapping->groupId];
				$permObj = new Permission();
				$permObj->id = $mapping->permissionId;
				if ($permObj->find(true)) {
					$groups[$groupKey]['permissions'][$permObj->id] = clone $permObj;
				}
			}
		}
		return $groups;
	}

	/**
	 * Sorts permissions hierarchically so parent permission groups appear before their children.
	 * Supports unlimited nesting depth and maintains alphabetical order within each hierarchy level.
	 *
	 * @param array $permissionsInSection Permissions and groups to sort
	 * @param array $permissionGroups All permission group definitions
	 * @return array Sorted permissions with parents before children, including depth information
	 */
	private static function sortPermissionsHierarchically(array $permissionsInSection, array $permissionGroups): array {
		// First, sort everything alphabetically
		ksort($permissionsInSection, SORT_NATURAL | SORT_FLAG_CASE);

		// Calculate depth for all permission groups
		$depths = self::calculatePermissionDepths($permissionGroups);

		// Separate groups by whether they have a parent, and separate regular permissions
		$groupsByParent = [];
		$topLevelGroups = [];
		$regularPermissions = [];

		foreach ($permissionsInSection as $key => $info) {
			if ($info['type'] == 'group') {
				$groupKey = $info['id'];
				if (isset($permissionGroups[$groupKey])) {
					// Add depth information to the group info
					$info['depth'] = $depths[$groupKey] ?? 0;

					$parentKey = $permissionGroups[$groupKey]['parentGroupKey'] ?? null;
					if (!empty($parentKey)) {
						// This group has a parent
						if (!isset($groupsByParent[$parentKey])) {
							$groupsByParent[$parentKey] = [];
						}
						$groupsByParent[$parentKey][$key] = $info;
					} else {
						// This is a top-level group
						$topLevelGroups[$key] = $info;
					}
				}
			} else {
				// Regular permission
				$regularPermissions[$key] = $info;
			}
		}

		// Recursively build the sorted array
		$sorted = [];
		foreach ($topLevelGroups as $key => $info) {
			self::addGroupAndChildren($key, $info, $groupsByParent, $sorted);
		}

		// Add any orphaned groups (groups whose parent doesn't exist)
		foreach ($groupsByParent as $parentKey => $children) {
			foreach ($children as $childKey => $childInfo) {
				if (!isset($sorted[$childKey])) {
					self::addGroupAndChildren($childKey, $childInfo, $groupsByParent, $sorted);
				}
			}
		}

		// Add regular permissions at the end
		foreach ($regularPermissions as $key => $info) {
			$sorted[$key] = $info;
		}

		return $sorted;
	}

	/**
	 * Recursively adds a permission group and all its descendants to the sorted array.
	 *
	 * @param string $key The permission key/label
	 * @param array $info The permission group info
	 * @param array $groupsByParent Array of groups indexed by their parent's groupKey
	 * @param array &$sorted The sorted array being built (passed by reference)
	 */
	private static function addGroupAndChildren(string $key, array $info, array $groupsByParent, array &$sorted): void {
		// Add this group
		$sorted[$key] = $info;

		// Get the groupKey to find children
		$groupKey = $info['id'];

		// Recursively add all children
		if (isset($groupsByParent[$groupKey])) {
			foreach ($groupsByParent[$groupKey] as $childKey => $childInfo) {
				self::addGroupAndChildren($childKey, $childInfo, $groupsByParent, $sorted);
			}
		}
	}

	/**
	 * Calculates the depth of each permission group in the hierarchy.
	 * Depth 0 = no parent, Depth 1 = has parent with no parent, etc.
	 *
	 * @param array $permissionGroups All permission group definitions
	 * @return array Array mapping groupKey to depth level
	 */
	private static function calculatePermissionDepths(array $permissionGroups): array {
		$depths = [];

		foreach ($permissionGroups as $groupKey => $groupDef) {
			if (!isset($depths[$groupKey])) {
				$depths[$groupKey] = self::calculateGroupDepth($groupKey, $permissionGroups, []);
			}
		}

		return $depths;
	}

	/**
	 * Recursively calculates the depth of a specific permission group.
	 * Uses memoization and cycle detection.
	 *
	 * @param string $groupKey The groupKey to calculate depth for
	 * @param array $permissionGroups All permission group definitions
	 * @param array $visited Array of visited groupKeys for cycle detection
	 * @return int The depth level (0 = no parent)
	 */
	private static function calculateGroupDepth(string $groupKey, array $permissionGroups, array $visited): int {
		// Cycle detection
		if (in_array($groupKey, $visited)) {
			return 0; // Treat cycles as top-level to avoid infinite recursion
		}

		if (!isset($permissionGroups[$groupKey])) {
			return 0;
		}

		$parentKey = $permissionGroups[$groupKey]['parentGroupKey'] ?? null;

		if (empty($parentKey)) {
			return 0; // No parent, depth is 0
		}

		// Mark as visited for cycle detection
		$visited[] = $groupKey;

		// Recursively calculate parent's depth and add 1
		return 1 + self::calculateGroupDepth($parentKey, $permissionGroups, $visited);
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#system_admin', 'System Administration');
		$breadcrumbs[] = new Breadcrumb('/Admin/Administrators', 'Administrators');
		$breadcrumbs[] = new Breadcrumb('', 'Permissions');
		return $breadcrumbs;
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Permissions');
	}

	function getActiveAdminSection(): string {
		return 'system_admin';
	}
}