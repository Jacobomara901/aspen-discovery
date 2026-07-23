<?php
function getAllDatabaseUpdates(): array {
	require_once ROOT_DIR . '/sys/DBMaintenance/library_location_updates.php';
	$library_location_updates = getLibraryLocationUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/summon_updates.php';
	$summonUpdates = getSummonUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/cloud_library_updates.php';
	$cloudLibraryUpdates = getCloudLibraryUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/grapes_web_builder_updates.php';
	$grapesWebBuilderUpdates = getGrapesWebBuilderUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/heycentric_updates.php';
	$heycentricUpdates = getHeyCentricUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/community_engagement_updates.php';
	$communityEngagementUpdates = getCommunityEngagementUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/talpa_updates.php';
	$talpaUpdates = getTalpaUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/hoopla_version2_updates.php';
	$hooplaVersion2Updates = getHooplaVersion2Updates();
	require_once ROOT_DIR . '/sys/DBMaintenance/pay360_updates.php';
	$pay360Updates = getPay360Updates();
	require_once ROOT_DIR . '/sys/DBMaintenance/gale_updates.php';
	$galeUpdates = getGaleUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/aspen_event_registration_updates.php';
	$aspenEventRegistrationUpdates = getAspenEventRegistrationUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/aspen_event_waiting_list_updates.php';
	$aspenEventWaitingListUpdates = getAspenEventWaitingListUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/aspen_event_notification_updates.php';
	$aspenEventNotificationUpdates = getAspenEventNotificationUpdates();
	require_once ROOT_DIR . '/sys/DBMaintenance/daily_usage_updates.php';
	$dailyUsageUpdates = getDailyUsageUpdates();

	//having these on separate lines should make merges easier to manage
	$baseUpdates = array_merge(
		$library_location_updates,
		$summonUpdates,
		$cloudLibraryUpdates,
		$grapesWebBuilderUpdates,
		$communityEngagementUpdates,
		$talpaUpdates,
		$heycentricUpdates,
		$hooplaVersion2Updates,
		$pay360Updates,
		$galeUpdates,
		$aspenEventRegistrationUpdates,
		$aspenEventWaitingListUpdates,
		$aspenEventNotificationUpdates,
		$dailyUsageUpdates
	);

	//Get version updates
	require_once ROOT_DIR . '/sys/Utils/StringUtils.php';
	$versionUpdates = scandir(ROOT_DIR . '/sys/DBMaintenance/version_updates', SCANDIR_SORT_ASCENDING);
	foreach ($versionUpdates as $updateFile) {
		if (is_file(ROOT_DIR . '/sys/DBMaintenance/version_updates/' . $updateFile)) {
			if (StringUtils::endsWith($updateFile, '.php')) {
				include_once ROOT_DIR . "/sys/DBMaintenance/version_updates/$updateFile";
				$version = substr($updateFile, 0, strrpos($updateFile, '.'));
				$updateFunction = 'getUpdates' . str_replace('.', '_', $version);
				$updates = $updateFunction();
				$baseUpdates = array_merge($baseUpdates, $updates);
			}
		}
	}

	//Get Plugin Updates
	global $plugins;
	if (!empty($plugins)) {
		foreach ($plugins as $plugin) {
			$baseUpdates = array_merge($baseUpdates, $plugin->getDatabaseUpdates());
		}
	}

	return $baseUpdates;
}
