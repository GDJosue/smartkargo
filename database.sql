-- Database schema for Supernumerario project

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` VARCHAR(20) NOT NULL UNIQUE,
  `date_out` VARCHAR(50),
  `date_return` VARCHAR(50),
  `main_dest` VARCHAR(255),
  `passengers` TEXT, -- JSON string
  `res_code` VARCHAR(50),
  `dep_day` VARCHAR(100),
  `flight_num` VARCHAR(50),
  `duration` VARCHAR(50),
  `cabin` VARCHAR(50),
  `status` VARCHAR(50),
  `orig_code` VARCHAR(10),
  `orig_city` VARCHAR(100),
  `dest_code` VARCHAR(10),
  `dest_city` VARCHAR(100),
  `orig_time` VARCHAR(20),
  `orig_term` VARCHAR(50),
  `dest_time` VARCHAR(20),
  `dest_term` VARCHAR(50),
  `aircraft` VARCHAR(50),
  `miles` VARCHAR(20),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `users` (`username`, `password`, `full_name`) 
VALUES ('admin', '$2y$10$AaGHA8acQVY3Y.krHnpZeO3SNSUNzDCk48Va68dPGYvBmuTkW.fei', 'Administrador Mas Cargo')
ON DUPLICATE KEY UPDATE username=username;
