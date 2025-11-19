<?php

/** @noinspection PhpUnused */
function getUpdates25_12_00(): array {
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

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth

		//chloe - Open Fifth
    
    //Jacob O'Mara - Open Fifth
		'permission_groups_hierarchy' => [
			'title' => 'Permission Groups Hierarchy',
			'description' => 'Add support for hierarchical permission groups with parent-child relationships',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE permission_groups ADD COLUMN parentGroupKey VARCHAR(100) DEFAULT NULL AFTER description",
			]
		], //permission_groups_hierarchy

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}
