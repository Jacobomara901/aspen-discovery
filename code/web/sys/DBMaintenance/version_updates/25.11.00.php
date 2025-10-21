<?php

/** @noinspection PhpUnused */
function getUpdates25_11_00(): array {
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

		//katherine - Grove

		//kodi - ByWater

		//kirstien - ByWater

		//alexander - Open Fifth

		//chloe - Open Fifth


		//Jacob - Open Fifth
		'user_oauth_enable_system_variable' => [
			'title' => 'Add User OAuth System Variable',
			'description' => 'Add system variable to enable user OAuth key generation',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN enableUserOAuth TINYINT(1) DEFAULT 0',
			]
		], //user_oauth_enable_system_variable
		'user_oauth_keys_table' => [
			'title' => 'Create User OAuth Keys Table',
			'description' => 'Create table to store user-specific OAuth keys for API authentication',
			'continueOnError' => false,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS user_oauth_keys (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userId INT(11) NOT NULL,
					keyName VARCHAR(100) NOT NULL,
					clientId VARCHAR(64) NOT NULL UNIQUE,
					clientSecret VARCHAR(255) NOT NULL,
					created INT(11) NOT NULL,
					lastUsed INT(11) DEFAULT NULL,
					isActive TINYINT(1) DEFAULT 1,
					INDEX (userId),
					INDEX (clientId),
					INDEX (isActive),
					FOREIGN KEY (userId) REFERENCES user(id) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
			]
		], //user_oauth_keys_table

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

		//Talpa Search

		// Brendan Lawlor

	];
}
