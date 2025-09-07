-- Create database and use it
CREATE DATABASE IF NOT EXISTS movietix CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE movietix;

-- Movies
CREATE TABLE IF NOT EXISTS movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  duration_min INT,
  rating VARCHAR(10),
  poster_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Halls and seat layouts
CREATE TABLE IF NOT EXISTS halls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  layout JSON NOT NULL
) ENGINE=InnoDB;

-- Showtimes
CREATE TABLE IF NOT EXISTS showtimes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  movie_id INT NOT NULL,
  show_date DATE NOT NULL,
  show_time TIME NOT NULL,
  hall_id INT NULL,
  price DECIMAL(8,2) NOT NULL,
  CONSTRAINT fk_showtimes_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
  CONSTRAINT fk_showtimes_hall FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  showtime_id INT NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(150) NOT NULL,
  user_id INT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  payment_ref VARCHAR(100) NULL,
  promo_id INT NULL,
  booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Booking seats with uniqueness per showtime+seat
CREATE TABLE IF NOT EXISTS booking_seats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  showtime_id INT NOT NULL,
  seat_label VARCHAR(5) NOT NULL,
  UNIQUE KEY uniq_showtime_seat (showtime_id, seat_label),
  CONSTRAINT fk_bs_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_bs_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Promo codes
CREATE TABLE IF NOT EXISTS promo_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  discount_percent INT NOT NULL,
  valid_until DATE NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rate limiting events
CREATE TABLE IF NOT EXISTS rate_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ip VARBINARY(16) NOT NULL,
  bucket VARCHAR(32) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bucket_time (bucket, created_at)
) ENGINE=InnoDB;

-- Seed halls
INSERT INTO halls (name, layout) VALUES
('Hall 1', JSON_OBJECT('rows', 8, 'cols', 12, 'vip_rows', JSON_ARRAY('A','B'))),
('Hall 2', JSON_OBJECT('rows', 10, 'cols', 14, 'vip_rows', JSON_ARRAY()));

-- Seed movies
INSERT INTO movies (title, description, duration_min, rating, poster_url) VALUES
('Inception', 'A thief who steals corporate secrets through dream-sharing tech.', 148, 'PG-13', 'posters/inception.jpg'),
('Spirited Away', 'A girl enters the spirit world to save her parents.', 125, 'PG', 'posters/spirited.jpg');

-- Seed showtimes (update dates as needed)
INSERT INTO showtimes (movie_id, show_date, show_time, hall_id, price) VALUES
(1, DATE_FORMAT(NOW(), '%Y-%m-%d'), '18:00:00', 1, 15.00),
(1, DATE_FORMAT(NOW(), '%Y-%m-%d'), '21:00:00', 1, 15.00),
(2, DATE_FORMAT(NOW(), '%Y-%m-%d'), '19:00:00', 2, 12.00);

-- Seed admin user (password = Admin@123 ; replace later)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin', 'admin@example.com', '$2y$10$H7G7vVvGm1x1yR1m3n1m8O6bFv0b6p0g2b6jUQ5Q0W1dQsPf0m6nK', 'admin');