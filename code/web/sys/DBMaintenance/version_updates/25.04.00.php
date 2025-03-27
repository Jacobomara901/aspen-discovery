<?php

function getUpdates25_04_00(): array {
	$curTime = time();
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark - Grove
		'restrict_local_ill_by_patron_type' => [
			'title' => 'Restrict Local ILL by Patron Type',
			'description' => 'Add an option to restrict local ILL by Patron Type',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE ptype ADD COLUMN allowLocalIll TINYINT DEFAULT  1'
			]
		], //restrict_local_ill_by_patron_type

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - PTFS-Europe

		//chloe - PTFS-Europe

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
		'enable_physical_location_detection' => [
			'title' => 'Enable Physical Location Detection',
			'description' => 'Add option to enable/disable physical location detection for theme selection. When enabled (default), physical location detection is used for theme selection, which may cause theme conflicts when multiple libraries share the same subdomain. When disabled, theme selection is based on library ID, allowing libraries sharing the same subdomain to have different themes.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN enablePhysicalLocationDetection tinyint(1) DEFAULT 1 AFTER disableIpSpammyControl'
			]
		], //enable_physical_location_detection

	];
}