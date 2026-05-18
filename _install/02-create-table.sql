-- Country table
CREATE TABLE `country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_country_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert countries
INSERT INTO `country` (`name`, `code`) VALUES
('Nigeria', 'NGA'),
('Liberia', 'LBR'),
('Cameroon', 'CMR');

-- State table
CREATE TABLE `state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_state_country` (`country_id`),
  CONSTRAINT `fk_state_country` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `unique_state_per_country` (`country_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert states for Nigeria (country_id will be 1) - REMOVED DUPLICATES
INSERT INTO `state` (`country_id`, `name`, `code`) VALUES
(1, 'Abia', 'AB'),
(1, 'Adamawa', 'AD'),
(1, 'Akwa Ibom', 'AK'),
(1, 'Anambra', 'AN'),
(1, 'Bauchi', 'BA'),
(1, 'Bayelsa', 'BY'),
(1, 'Benue', 'BE'),
(1, 'Borno', 'BO'),
(1, 'Cross River', 'CR'),
(1, 'Delta', 'DE'),
(1, 'Ebonyi', 'EB'),
(1, 'Edo', 'ED'),
(1, 'Ekiti', 'EK'),
(1, 'Enugu', 'EN'),
(1, 'Federal Capital Territory', 'FCT'),
(1, 'Gombe', 'GO'),
(1, 'Imo', 'IM'),
(1, 'Jigawa', 'JI'),
(1, 'Kaduna', 'KD'),
(1, 'Kano', 'KN'),
(1, 'Katsina', 'KT'),
(1, 'Kebbi', 'KE'),
(1, 'Kogi', 'KO'),
(1, 'Kwara', 'KW'),
(1, 'Lagos', 'LA'),
(1, 'Nasarawa', 'NA'),
(1, 'Niger', 'NI'),
(1, 'Ogun', 'OG'),
(1, 'Ondo', 'ON'),
(1, 'Osun', 'OS'),
(1, 'Oyo', 'OY'),
(1, 'Plateau', 'PL'),
(1, 'Rivers', 'RV'),
(1, 'Sokoto', 'SO'),
(1, 'Taraba', 'TA'),
(1, 'Yobe', 'YO'),
(1, 'Zamfara', 'ZA');

-- Insert counties for Liberia (country_id will be 2)
INSERT INTO `state` (`country_id`, `name`, `code`) VALUES
(2, 'Montserrado', 'MO'),
(2, 'Nimba', 'NI'),
(2, 'Bong', 'BG'),
(2, 'Bomi', 'BM'),
(2, 'Grand Bassa', 'GB'),
(2, 'Grand Cape Mount', 'CM'),
(2, 'Grand Gedeh', 'GG'),
(2, 'Grand Kru', 'GK'),
(2, 'Lofa', 'LO'),
(2, 'Margibi', 'MG'),
(2, 'Maryland', 'MY'),
(2, 'River Cess', 'RC'),
(2, 'River Gee', 'RG'),
(2, 'Sinoe', 'SN'),
(2, 'Gbarpolu', 'GP');

-- Insert regions for Cameroon (country_id will be 3)
INSERT INTO `state` (`country_id`, `name`, `code`) VALUES
(3, 'Centre', 'CE'),
(3, 'Littoral', 'LT'),
(3, 'Southwest', 'SW'),
(3, 'Adamawa', 'AD'),
(3, 'East', 'ES'),
(3, 'Far North', 'FN'),
(3, 'North', 'NO'),
(3, 'Northwest', 'NW'),
(3, 'West', 'WE'),
(3, 'South', 'SO');


-- Create departments table
CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO departments (name) VALUES
('Operations');

-- staff_login table 
-- Drop existing staff_login table if you want to recreate fresh
DROP TABLE IF EXISTS `staff_login`;

-- Create staff_login table with department_id column included
CREATE TABLE `staff_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `country_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `department_id` INT UNSIGNED NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `fk_staff_country` (`country_id`),
  KEY `fk_staff_state` (`state_id`),
  KEY `fk_staff_department` (`department_id`),
  CONSTRAINT `fk_staff_country` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_state` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert users with departments
INSERT INTO `staff_login` (`email`, `password`, `role`, `country_id`, `state_id`, `department_id`) 
VALUES 
(
    'admin@example.com', 
    '$2y$10$YourGeneratedHashHere', 
    'super_admin', 
    (SELECT id FROM country WHERE code = 'NGA'), 
    (SELECT id FROM state WHERE country_id = (SELECT id FROM country WHERE code = 'NGA') AND name = 'Lagos' LIMIT 1),
    (SELECT id FROM departments WHERE name = 'Operations')
),
(
    'rhyttahkogi@gmail.com', 
    '$2y$10$YourGeneratedHashHere', 
    'staff', 
    (SELECT id FROM country WHERE code = 'NGA'), 
    (SELECT id FROM state WHERE country_id = (SELECT id FROM country WHERE code = 'NGA') AND name = 'Lagos' LIMIT 1),
    (SELECT id FROM departments WHERE name = 'Programs')
),
(
    'rita.kogi@evidenceaction.org', 
    '$2y$10$YourGeneratedHashHere', 
    'staff', 
    (SELECT id FROM country WHERE code = 'NGA'), 
    (SELECT id FROM state WHERE country_id = (SELECT id FROM country WHERE code = 'NGA') AND name = 'Lagos' LIMIT 1),
    (SELECT id FROM departments WHERE name = 'MLE-D')
);

-- funders table
CREATE TABLE `funder_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- airlines table
CREATE TABLE `airlines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--- hotel vendors table
CREATE TABLE `hotel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `state_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hotel_state` (`state_id`),
  CONSTRAINT `fk_hotel_state` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_hotel_per_state` (`state_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `hotel`
ADD COLUMN `location` varchar(255) DEFAULT NULL AFTER `name`;

---drivers table
CREATE TABLE `driver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_driver_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--- EA States table
CREATE TABLE `ea_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state_id` int(11) NOT NULL COMMENT 'References state table',
  `reviewer_email` varchar(100) NOT NULL COMMENT 'Email from staff_login',
  `co_reviewer_email` varchar(100) DEFAULT NULL COMMENT 'Email from staff_login',
  `manager_email` varchar(100) NOT NULL COMMENT 'Email from staff_login',
  `driver_id` int(11) DEFAULT NULL COMMENT 'References driver table',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ea_state_state` (`state_id`),
  KEY `fk_ea_state_driver` (`driver_id`),
  KEY `idx_reviewer` (`reviewer_email`),
  KEY `idx_manager` (`manager_email`),
  CONSTRAINT `fk_ea_state_state` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ea_state_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  UNIQUE KEY `unique_state` (`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Updated EA States table with security_manager_email and operations_manager_email
-- Add only security manager (without operations manager)
ALTER TABLE `ea_state` 
ADD COLUMN `security_manager_email` varchar(100) DEFAULT NULL COMMENT 'Email from staff_login' AFTER `manager_email`,
ADD INDEX `idx_security_manager` (`security_manager_email`),
ADD CONSTRAINT `fk_ea_state_security_manager` FOREIGN KEY (`security_manager_email`) REFERENCES `staff_login` (`email`) ON DELETE SET NULL ON UPDATE CASCADE;


--- intrastate(within the state) requests table
CREATE TABLE `intrastate_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  
  -- Staff details (logged in user)
  `staff_email` varchar(100) NOT NULL,
  `staff_phone` varchar(20) NOT NULL,
  
  -- Supervisor details
  `supervisor_email` varchar(100) NOT NULL,
  
  -- Vehicle location (EA State)
  `vehicle_location_state_id` int(11) NOT NULL,
  
  -- Auto-populated from vehicle location (ea_state table)
  `reviewer_email` varchar(100) NOT NULL,
  `co_reviewer_email` varchar(100) DEFAULT NULL,
  `manager_email` varchar(100) NOT NULL,
  `security_manager_email` varchar(100) DEFAULT NULL,
  
  -- Trip dates
  `trip_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_nights` int(11) NOT NULL,
  
  -- Trip details
  `purpose` text NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `trip_destination` varchar(255) NOT NULL COMMENT 'City/town destination within the state',
  `trip_destination_time` time NOT NULL,
  `route_information` text,
  
  -- Funder (using funder_codes table)
  `funder_code_id` int(11) NOT NULL,
  
  -- Driver overtime
  `driver_overtime` enum('yes','no') NOT NULL DEFAULT 'no',
  
  -- Overtime details (appears if driver_overtime = 'yes')
  `trip_activity` text,
  `reason_for_overtime` text,
  `overtime_manager_email` varchar(100) DEFAULT NULL,
  
  -- Driver pickup
  `need_driver_pickup` enum('yes','no') NOT NULL DEFAULT 'no',
  `pickup_time` time DEFAULT NULL,
  
  -- Assigned driver (after security approval - now final approver)
  `assigned_driver_id` int(11) DEFAULT NULL COMMENT 'Driver assigned by security manager',
  
  -- Approval timestamps
  `reviewer_approved_at` timestamp NULL DEFAULT NULL,
  `co_reviewer_approved_at` timestamp NULL DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `security_manager_approved_at` timestamp NULL DEFAULT NULL,
  
  -- Rejection details
  `rejection_reason` text DEFAULT NULL,
  `rejected_by` varchar(100) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  
  -- Status tracking (updated - operations manager removed)
  `status` enum('draft','pending','reviewer_approved','co_reviewer_approved','manager_approved','security_approved','rejected','completed','cancelled') NOT NULL DEFAULT 'draft',
  `current_approval_level` enum('reviewer','co_reviewer','manager','security_manager','none') DEFAULT 'reviewer',
  
  -- Timestamps
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  
  PRIMARY KEY (`id`),
  KEY `idx_staff_email` (`staff_email`),
  KEY `idx_supervisor_email` (`supervisor_email`),
  KEY `idx_vehicle_location_state_id` (`vehicle_location_state_id`),
  KEY `idx_reviewer_email` (`reviewer_email`),
  KEY `idx_co_reviewer_email` (`co_reviewer_email`),
  KEY `idx_manager_email` (`manager_email`),
  KEY `idx_security_manager_email` (`security_manager_email`),
  KEY `idx_funder_code_id` (`funder_code_id`),
  KEY `idx_overtime_manager_email` (`overtime_manager_email`),
  KEY `idx_assigned_driver_id` (`assigned_driver_id`),
  KEY `idx_rejected_by` (`rejected_by`),
  KEY `idx_status` (`status`),
  KEY `idx_current_approval_level` (`current_approval_level`),
  KEY `idx_trip_date` (`trip_date`),
  KEY `idx_created_at` (`created_at`),
  
  -- Foreign key constraints
  CONSTRAINT `fk_intrastate_staff` FOREIGN KEY (`staff_email`) REFERENCES `staff_login` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_supervisor` FOREIGN KEY (`supervisor_email`) REFERENCES `staff_login` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_vehicle_location` FOREIGN KEY (`vehicle_location_state_id`) REFERENCES `state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_reviewer` FOREIGN KEY (`reviewer_email`) REFERENCES `staff_login` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_co_reviewer` FOREIGN KEY (`co_reviewer_email`) REFERENCES `staff_login` (`email`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_manager` FOREIGN KEY (`manager_email`) REFERENCES `staff_login` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_security_manager` FOREIGN KEY (`security_manager_email`) REFERENCES `staff_login` (`email`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_funder` FOREIGN KEY (`funder_code_id`) REFERENCES `funder_codes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_overtime_manager` FOREIGN KEY (`overtime_manager_email`) REFERENCES `staff_login` (`email`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_assigned_driver` FOREIGN KEY (`assigned_driver_id`) REFERENCES `driver` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_intrastate_rejected_by` FOREIGN KEY (`rejected_by`) REFERENCES `staff_login` (`email`) ON DELETE SET NULL ON UPDATE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--- interstate(one state to another state) requests table
CREATE TABLE `interstate_request` (

  `id` int(11) NOT NULL AUTO_INCREMENT,

  -- Staff details (logged in user)
  `staff_email` varchar(100) NOT NULL,
  `staff_phone` varchar(20) NOT NULL,

  -- Supervisor details
  `supervisor_email` varchar(100) NOT NULL,

  -- Vehicle location (EA State)
  `vehicle_location_state_id` int(11) NOT NULL,

  -- Auto-populated approvers
  `reviewer_email` varchar(100) NOT NULL,
  `co_reviewer_email` varchar(100) DEFAULT NULL,
  `manager_email` varchar(100) NOT NULL,
  `security_manager_email` varchar(100) DEFAULT NULL,

  -- Trip dates
  `trip_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_nights` int(11) NOT NULL,

  -- Trip details
  `purpose` text NOT NULL,
  `arrival_location_state_id` int(11) NOT NULL COMMENT 'Arrival state from states table',
  `destination_city` varchar(255) NOT NULL COMMENT 'Exact destination city/town',
  `pickup_location` varchar(255) NOT NULL,
  `trip_destination` varchar(255) NOT NULL COMMENT 'Final destination address',
  `trip_destination_time` time NOT NULL,
  `route_information` text DEFAULT NULL,

  -- Mode of travel
  `mode_of_travel` enum('air','road','both') NOT NULL DEFAULT 'road',

  -- Airport pickup
  `require_airport_pickup` enum('yes','no') NOT NULL DEFAULT 'no',
  `airport_pickup_dropoff_destination` varchar(255) DEFAULT NULL,

  -- Flight details (requester)
  `requester_departure_flight_airline_id` int(11) DEFAULT NULL,
  `requester_return_flight_airline_id` int(11) DEFAULT NULL,

  -- Flight details (operations)
  `operations_departure_flight_airline_id` int(11) DEFAULT NULL,
  `operations_return_flight_airline_id` int(11) DEFAULT NULL,

  -- Flight arrival time
  `flight_arrival_time` datetime DEFAULT NULL,

  -- =====================================================
  -- HOTEL ACCOMMODATION
  -- =====================================================
  `require_hotel` enum('yes','no') NOT NULL DEFAULT 'no',

  -- Selected hotel vendor
  `hotel_id` int(11) DEFAULT NULL COMMENT 'Selected hotel from hotel table',

  -- Manual hotel entry
  `hotel_other_name` varchar(255) DEFAULT NULL COMMENT 'Manual hotel entry if not in vendors',

  -- Auto-filled from hotel table
  `hotel_location` varchar(255) DEFAULT NULL COMMENT 'Auto-populated hotel location from hotel table',

  -- Auto-filled from hotel table
  `hotel_location_state_id` int(11) DEFAULT NULL COMMENT 'State ID from selected hotel',

  -- Funder
  `funder_code_id` int(11) NOT NULL,

  -- Driver overtime
  `driver_overtime` enum('yes','no') NOT NULL DEFAULT 'no',

  -- Overtime details
  `trip_activity` text DEFAULT NULL,
  `reason_for_overtime` text DEFAULT NULL,
  `overtime_manager_email` varchar(100) DEFAULT NULL,

  -- Driver pickup
  `need_driver_pickup` enum('yes','no') NOT NULL DEFAULT 'no',
  `pickup_time` time DEFAULT NULL,

  -- Assigned driver
  `assigned_driver_id` int(11) DEFAULT NULL,

  -- Different return driver
  `different_return_driver` enum('yes','no') NOT NULL DEFAULT 'no',
  `return_assigned_driver_id` int(11) DEFAULT NULL,

  -- Driver contact details
  `approved_driver_email` varchar(100) DEFAULT NULL,
  `approved_driver_phone` varchar(20) DEFAULT NULL,

  -- Approval timestamps
  `reviewer_approved_at` timestamp NULL DEFAULT NULL,
  `co_reviewer_approved_at` timestamp NULL DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `security_manager_approved_at` timestamp NULL DEFAULT NULL,

  -- Rejection details
  `rejection_reason` text DEFAULT NULL,
  `rejected_by` varchar(100) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,

  -- Status tracking
  `status` enum(
    'draft',
    'pending',
    'reviewer_approved',
    'co_reviewer_approved',
    'manager_approved',
    'security_approved',
    'rejected',
    'completed',
    'cancelled'
  ) NOT NULL DEFAULT 'draft',

  `current_approval_level` enum(
    'reviewer',
    'co_reviewer',
    'manager',
    'security_manager',
    'none'
  ) DEFAULT 'reviewer',

  -- Timestamps
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
    ON UPDATE current_timestamp(),

  PRIMARY KEY (`id`),

  -- =====================================================
  -- INDEXES
  -- =====================================================
  KEY `idx_staff_email` (`staff_email`),
  KEY `idx_supervisor_email` (`supervisor_email`),
  KEY `idx_vehicle_location_state_id` (`vehicle_location_state_id`),
  KEY `idx_arrival_location_state_id` (`arrival_location_state_id`),
  KEY `idx_hotel_id` (`hotel_id`),
  KEY `idx_hotel_location_state_id` (`hotel_location_state_id`),
  KEY `idx_reviewer_email` (`reviewer_email`),
  KEY `idx_co_reviewer_email` (`co_reviewer_email`),
  KEY `idx_manager_email` (`manager_email`),
  KEY `idx_security_manager_email` (`security_manager_email`),
  KEY `idx_funder_code_id` (`funder_code_id`),
  KEY `idx_overtime_manager_email` (`overtime_manager_email`),
  KEY `idx_assigned_driver_id` (`assigned_driver_id`),
  KEY `idx_return_assigned_driver_id` (`return_assigned_driver_id`),
  KEY `idx_approved_driver_email` (`approved_driver_email`),
  KEY `idx_requester_departure_flight_airline` (`requester_departure_flight_airline_id`),
  KEY `idx_requester_return_flight_airline` (`requester_return_flight_airline_id`),
  KEY `idx_operations_departure_flight_airline` (`operations_departure_flight_airline_id`),
  KEY `idx_operations_return_flight_airline` (`operations_return_flight_airline_id`),
  KEY `idx_rejected_by` (`rejected_by`),
  KEY `idx_status` (`status`),
  KEY `idx_current_approval_level` (`current_approval_level`),
  KEY `idx_trip_date` (`trip_date`),
  KEY `idx_created_at` (`created_at`),

  -- =====================================================
  -- FOREIGN KEYS
  -- =====================================================

  CONSTRAINT `fk_interstate_staff`
    FOREIGN KEY (`staff_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_supervisor`
    FOREIGN KEY (`supervisor_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_vehicle_location`
    FOREIGN KEY (`vehicle_location_state_id`)
    REFERENCES `state` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_arrival_location`
    FOREIGN KEY (`arrival_location_state_id`)
    REFERENCES `state` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_hotel`
    FOREIGN KEY (`hotel_id`)
    REFERENCES `hotel` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_hotel_location`
    FOREIGN KEY (`hotel_location_state_id`)
    REFERENCES `state` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_reviewer`
    FOREIGN KEY (`reviewer_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_co_reviewer`
    FOREIGN KEY (`co_reviewer_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_manager`
    FOREIGN KEY (`manager_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_security_manager`
    FOREIGN KEY (`security_manager_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_funder`
    FOREIGN KEY (`funder_code_id`)
    REFERENCES `funder_codes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_overtime_manager`
    FOREIGN KEY (`overtime_manager_email`)
    REFERENCES `staff_login` (`email`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_assigned_driver`
    FOREIGN KEY (`assigned_driver_id`)
    REFERENCES `driver` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_return_assigned_driver`
    FOREIGN KEY (`return_assigned_driver_id`)
    REFERENCES `driver` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_approved_driver`
    FOREIGN KEY (`approved_driver_email`)
    REFERENCES `driver` (`email`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_requester_departure_airline`
    FOREIGN KEY (`requester_departure_flight_airline_id`)
    REFERENCES `airlines` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_requester_return_airline`
    FOREIGN KEY (`requester_return_flight_airline_id`)
    REFERENCES `airlines` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_operations_departure_airline`
    FOREIGN KEY (`operations_departure_flight_airline_id`)
    REFERENCES `airlines` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_operations_return_airline`
    FOREIGN KEY (`operations_return_flight_airline_id`)
    REFERENCES `airlines` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,

  CONSTRAINT `fk_interstate_rejected_by`
    FOREIGN KEY (`rejected_by`)
    REFERENCES `staff_login` (`email`)
    ON DELETE SET NULL
    ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- trip_requests table
CREATE TABLE `trip_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `requester_id` INT(11) NOT NULL,
    `department_id` INT UNSIGNED NULL,
    `trip_type` ENUM('local', 'international') NOT NULL DEFAULT 'local',
    `trip_destination` VARCHAR(255) NOT NULL,
    `purpose` TEXT NOT NULL,
    `departure_date` DATE NOT NULL,
    `departure_time` TIME NOT NULL,
    `vehicle_departure_location` INT(11) NULL COMMENT 'State ID for departure',
    `vehicle_destination_location` INT(11) NULL COMMENT 'State ID for destination',
    `return_date` DATE NULL,
    `need_driver` TINYINT(1) DEFAULT 0,
    `driver_overtime` TINYINT(1) DEFAULT 0,
    `approved_supervisor_id` INT(11) NULL,
    `reviewer_id` INT(11) NOT NULL,
    `co_reviewer_id` INT(11) NULL,
    `manager_id` INT(11) NULL,
    `status` ENUM('pending','approved','cancelled','declined') DEFAULT 'pending',
    `comments` TEXT,
    `approval_token` VARCHAR(64) UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_requester` (`requester_id`),
    KEY `fk_department` (`department_id`),
    KEY `fk_supervisor` (`approved_supervisor_id`),
    KEY `fk_reviewer` (`reviewer_id`),
    KEY `fk_co_reviewer` (`co_reviewer_id`),
    KEY `fk_manager` (`manager_id`),
    KEY `fk_departure_location` (`vehicle_departure_location`),
    KEY `fk_destination_location` (`vehicle_destination_location`),
    KEY `idx_approval_token` (`approval_token`),
    CONSTRAINT `fk_requester` FOREIGN KEY (`requester_id`) REFERENCES `staff_login` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_supervisor` FOREIGN KEY (`approved_supervisor_id`) REFERENCES `staff_login` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `staff_login` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_co_reviewer` FOREIGN KEY (`co_reviewer_id`) REFERENCES `staff_login` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_manager` FOREIGN KEY (`manager_id`) REFERENCES `staff_login` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_departure_location` FOREIGN KEY (`vehicle_departure_location`) REFERENCES `state` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_destination_location` FOREIGN KEY (`vehicle_destination_location`) REFERENCES `state` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;