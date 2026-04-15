DROP TABLE IF EXISTS appointments;

CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 15,
    appointment_type VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),

    INDEX idx_user_date (user_id, appointment_date),
    INDEX idx_doctor_date_time (doctor_id, appointment_date, appointment_time)
);