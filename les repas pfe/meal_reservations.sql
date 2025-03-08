CREATE TABLE meal_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_date DATE NOT NULL,
    breakfast BOOLEAN DEFAULT 0,
    lunch BOOLEAN DEFAULT 0,
    dinner BOOLEAN DEFAULT 0,
    reservation_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
