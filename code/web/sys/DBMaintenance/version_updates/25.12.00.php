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

		//jacob - Open Fifth
		'library_events_default_calendar_view' => [
			'title' => 'Library Events Default Calendar View',
			'description' => 'Add eventsDefaultCalendarView field to library table for filtering calendar by location',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN eventsDefaultCalendarView TINYINT(1) DEFAULT 0 COMMENT '0 = All Locations, 1 = Use librarys main branch location, 2 = Use first library location (alphabetically)'"
			]
		],

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
	];
}
