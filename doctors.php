
USE nhs_booking_system;

-- USERS TABLE
CREATE TABLE users ( 
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'admin') DEFAULT 'patient'
);

--  DOCTORS TABLE
CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    available_days VARCHAR(100),
    room_number VARCHAR(20)
);

-- APPOINTMENTS TABLE
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('booked', 'checked-in', 'cancelled', 'completed', 'rescheduled') DEFAULT 'booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);

-- NOTIFICATIONS TABLE
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

--  CHECKINS TABLE
CREATE TABLE checkins (
    checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('checked-in') DEFAULT 'checked-in',
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id)
);

-- SAMPLE DATA doctors
INSERT INTO doctors (doctor_name, specialty, email, phone, available_days, room_number)
VALUES
('Dr Sarah Ahmed', 'General Practice', 's.ahmed@nhs.uk', '07111111111', 'Monday, Wednesday, Friday', 'A101'),
('Dr James Wilson', 'Emergency Medicine', 'j.wilson@nhs.uk', '07222222222', 'Tuesday, Thursday', 'A102'),
('Dr Emily Carter', 'Cardiology', 'e.carter@nhs.uk', '07333333333', 'Monday, Tuesday', 'B201'),
('Dr Michael Brown', 'Pediatrics', 'm.brown@nhs.uk', '07444444444', 'Wednesday, Thursday', 'B202'),
('Dr Olivia Taylor', 'Dermatology', 'o.taylor@nhs.uk', '07555555555', 'Friday', 'C301');
