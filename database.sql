-- Database schema for SmartKargo / Supernumerario.
-- Keep this file aligned with application code for fresh Docker deployments.

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `code` VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_login_codes_user_code` (`user_id`, `code`),
  CONSTRAINT `fk_login_codes_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` VARCHAR(20) NOT NULL UNIQUE,
  `date_out` VARCHAR(50),
  `date_return` VARCHAR(50),
  `approved_by` VARCHAR(255),
  `passengers` TEXT,
  `guide_code` VARCHAR(50),
  `area` VARCHAR(100),
  `transportadora` VARCHAR(50),
  `priority` VARCHAR(50),
  `status` VARCHAR(50),
  `orig_code` VARCHAR(10),
  `orig_city` VARCHAR(100),
  `dest_code` VARCHAR(10),
  `dest_city` VARCHAR(100),
  `time` VARCHAR(20),
  `flight` VARCHAR(20),
  `aircraft` VARCHAR(50),
  `miles` VARCHAR(20),
  `created_by_name` VARCHAR(255),
  `created_at_cdmx` DATETIME,
  `passenger_type` VARCHAR(255),
  `carrier2` VARCHAR(10) DEFAULT NULL,
  `flight2` VARCHAR(50) DEFAULT NULL,
  `from2` VARCHAR(10) DEFAULT NULL,
  `to2` VARCHAR(10) DEFAULT NULL,
  `time2` VARCHAR(20) DEFAULT NULL,
  `requested_by` VARCHAR(100) DEFAULT NULL,
  `signature` VARCHAR(100) DEFAULT NULL,
  `on_file` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `is_admin`)
VALUES (
  'admin',
  'admin@mascargo.com',
  '$2y$10$5tSMbLV4XY1GOeQxbDOig.bu1LUj9TFWnNfBXwAbk6ai7u5f1oI4e',
  'Administrador Mas Cargo',
  1
)
ON DUPLICATE KEY UPDATE username = username;
