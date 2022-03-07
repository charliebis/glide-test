CREATE TABLE `areas` (
                         `id` int NOT NULL AUTO_INCREMENT,
                         `area` varchar(50) NOT NULL,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `area_UNIQUE` (`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `calorific_value_data` (
                                        `id` int NOT NULL AUTO_INCREMENT,
                                        `area_id` int NOT NULL,
                                        `applicable_at` datetime NOT NULL,
                                        `applicable_for` datetime NOT NULL,
                                        `value` float NOT NULL,
                                        `generated_time` datetime NOT NULL,
                                        `quality_indicator` varchar(1) NOT NULL,
                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `process_log` (
                               `id` int NOT NULL AUTO_INCREMENT,
                               `process_start` datetime NOT NULL,
                               `process_end` datetime NOT NULL,
                               `total_records` int NOT NULL DEFAULT '0',
                               `total_saved` int NOT NULL DEFAULT '0',
                               `total_failed_area_parse` int NOT NULL DEFAULT '0',
                               `total_failed_area_save` int NOT NULL DEFAULT '0',
                               `total_failed_calorie_data_save` int NOT NULL DEFAULT '0',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
