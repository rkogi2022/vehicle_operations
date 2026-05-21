--add name field in drivers
ALTER TABLE `driver`
  ADD COLUMN `name` varchar(100) NOT NULL AFTER `id`,
  MODIFY COLUMN `email` varchar(100) DEFAULT NULL;

  --having an overtime manager field and allow one to assign many drivers to one state
  -- Add overtime_manager_email column
ALTER TABLE `ea_state`
ADD COLUMN `overtime_manager_email` varchar(100) DEFAULT NULL COMMENT 'Email from staff_login' AFTER `security_manager_email`,
ADD INDEX `idx_overtime_manager` (`overtime_manager_email`),
ADD CONSTRAINT `fk_ea_state_overtime_manager` FOREIGN KEY (`overtime_manager_email`) REFERENCES `staff_login` (`email`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Remove single driver_id from ea_state (drop FK first, then column)
ALTER TABLE `ea_state`
DROP FOREIGN KEY `fk_ea_state_driver`,
DROP INDEX `fk_ea_state_driver`,
DROP COLUMN `driver_id`;

-- Create many-to-many table for multiple drivers per ea_state
CREATE TABLE `ea_state_driver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ea_state_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ea_state_driver` (`ea_state_id`, `driver_id`),
  KEY `fk_ea_state_driver_ea_state` (`ea_state_id`),
  KEY `fk_ea_state_driver_driver` (`driver_id`),
  CONSTRAINT `fk_ea_state_driver_ea_state` FOREIGN KEY (`ea_state_id`) REFERENCES `ea_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ea_state_driver_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Passengers added to an intrastate trip by the requester
CREATE TABLE `intrastate_trip_passengers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trip_id` int(11) NOT NULL,
  `passenger_email` varchar(150) NOT NULL,
  `added_by` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_trip_passenger` (`trip_id`, `passenger_email`),
  KEY `fk_itp_trip` (`trip_id`),
  CONSTRAINT `fk_itp_trip` FOREIGN KEY (`trip_id`) REFERENCES `intrastate_request` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drop return_date and total_nights from intrastate (intrastate is same-day, no overnight stay)
ALTER TABLE `intrastate_request`
  DROP COLUMN `return_date`,
  DROP COLUMN `total_nights`;

-- Country-level default approvers (fallback when departure state has no EA State config)
CREATE TABLE `country_default_approvers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `reviewer_email` varchar(150) NOT NULL,
  `co_reviewer_email` varchar(150) DEFAULT NULL,
  `manager_email` varchar(150) NOT NULL,
  `security_manager_email` varchar(150) DEFAULT NULL,
  `overtime_manager_email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_country` (`country_id`),
  CONSTRAINT `fk_cda_country` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

