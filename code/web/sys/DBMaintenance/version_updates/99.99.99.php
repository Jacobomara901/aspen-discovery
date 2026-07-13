<?php

function getUpdates99_99_99(): array {
	return [
		'ci_regen_smoke_test' => [
			'title' => 'CI regeneration smoke test',
			'description' => 'Temporary update used to verify the aspen.sql regeneration workflow end to end',
			'continueOnError' => false,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS ci_regen_smoke_test (
					id INT(11) NOT NULL AUTO_INCREMENT,
					note VARCHAR(50) DEFAULT NULL,
					PRIMARY KEY (id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
			],
		],
	];
}
