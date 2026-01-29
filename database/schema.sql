CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  role ENUM('coach','admin') NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  coach_user_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  gender ENUM('male','female') NOT NULL,
  age TINYINT UNSIGNED NOT NULL,
  height_ft TINYINT UNSIGNED NOT NULL,
  height_in TINYINT UNSIGNED NOT NULL,
  start_weight_lbs DECIMAL(6,2) NOT NULL,
  waistline_in DECIMAL(6,2) NOT NULL,
  day10_waistline_in DECIMAL(6,2) NULL,
  bmi DECIMAL(6,2) NOT NULL,
  bmi_category VARCHAR(32) NOT NULL,
  front_photo_path VARCHAR(255) NOT NULL,
  side_photo_path VARCHAR(255) NOT NULL,
  day10_front_photo_path VARCHAR(255) NULL,
  day10_side_photo_path VARCHAR(255) NULL,
  challenge_start_date DATE NULL,
  registered_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_clients_coach_user_id (coach_user_id),
  CONSTRAINT fk_clients_coach_user_id FOREIGN KEY (coach_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS client_checkins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  coach_user_id INT UNSIGNED NOT NULL,
  day_number TINYINT UNSIGNED NOT NULL,
  weight_lbs DECIMAL(6,2) NOT NULL,
  recorded_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_client_checkins_day (client_id, day_number),
  KEY idx_client_checkins_client_id (client_id),
  KEY idx_client_checkins_coach_user_id (coach_user_id),
  CONSTRAINT fk_client_checkins_client_id FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_client_checkins_coach_user_id FOREIGN KEY (coach_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consent_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id INT UNSIGNED NOT NULL,
  coach_user_id INT UNSIGNED NOT NULL,
  consent_text TEXT NOT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_consent_logs_client_id (client_id),
  KEY idx_consent_logs_coach_user_id (coach_user_id),
  CONSTRAINT fk_consent_logs_client_id FOREIGN KEY (client_id) REFERENCES clients(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_consent_logs_coach_user_id FOREIGN KEY (coach_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coach_challenges (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  start_date DATE NOT NULL,
  duration_days TINYINT UNSIGNED NOT NULL DEFAULT 10,
  status ENUM('active','completed') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_coach_challenges_status_start (status, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coach_challenge_participants (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  coach_challenge_id INT UNSIGNED NOT NULL,
  coach_user_id INT UNSIGNED NOT NULL,
  height_ft TINYINT UNSIGNED NOT NULL,
  height_in TINYINT UNSIGNED NOT NULL,
  start_weight_lbs DECIMAL(6,2) NOT NULL,
  bmi DECIMAL(6,2) NOT NULL,
  bmi_category VARCHAR(32) NOT NULL,
  registered_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_coach_challenge_participant (coach_challenge_id, coach_user_id),
  KEY idx_coach_participants_challenge (coach_challenge_id),
  KEY idx_coach_participants_coach (coach_user_id),
  CONSTRAINT fk_coach_participants_challenge FOREIGN KEY (coach_challenge_id) REFERENCES coach_challenges(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_coach_participants_user FOREIGN KEY (coach_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coach_checkins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  coach_challenge_id INT UNSIGNED NOT NULL,
  coach_user_id INT UNSIGNED NOT NULL,
  day_number TINYINT UNSIGNED NOT NULL,
  weight_lbs DECIMAL(6,2) NOT NULL,
  recorded_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_coach_checkins_day (coach_challenge_id, coach_user_id, day_number),
  KEY idx_coach_checkins_challenge (coach_challenge_id),
  KEY idx_coach_checkins_coach (coach_user_id),
  CONSTRAINT fk_coach_checkins_challenge FOREIGN KEY (coach_challenge_id) REFERENCES coach_challenges(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_coach_checkins_user FOREIGN KEY (coach_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
