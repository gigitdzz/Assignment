CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(50) DEFAULT 'Booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);