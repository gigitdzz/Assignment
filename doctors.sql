DROP TABLE IF EXISTS doctors;

CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    staff_type ENUM('GP', 'Nurse', 'Specialist') NOT NULL DEFAULT 'GP',
    clinic_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    available_days VARCHAR(100) DEFAULT NULL,
    room_number VARCHAR(20) DEFAULT NULL,
    shift_start TIME NOT NULL DEFAULT '08:00:00',
    shift_end TIME NOT NULL DEFAULT '20:00:00',
    break_start TIME DEFAULT NULL,
    break_end TIME DEFAULT NULL,
    appointment_duration INT NOT NULL DEFAULT 15,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY unique_staff_email (email)
);

INSERT INTO doctors
(doctor_name, specialty, staff_type, clinic_name, email, phone, available_days, room_number, shift_start, shift_end, break_start, break_end, appointment_duration, is_active)
VALUES
('Dr Sarah Ahmed', 'General Practice', 'GP', 'General Medicine Clinic', 's.ahmed@nhs.uk', '07111111111', 'Monday, Tuesday, Wednesday, Thursday, Friday', 'A101', '08:00:00', '20:00:00', '13:00:00', '14:00:00', 15, 1),
('Dr James Wilson', 'General Practice', 'GP', 'General Medicine Clinic', 'j.wilson@nhs.uk', '07222222222', 'Monday, Tuesday, Wednesday, Thursday, Friday', 'A102', '08:00:00', '20:00:00', '13:00:00', '14:00:00', 15, 1),
('Dr Emily Carter', 'Cardiology', 'Specialist', 'Cardiology Clinic', 'e.carter@nhs.uk', '07333333333', 'Monday, Tuesday, Thursday', 'B201', '08:00:00', '20:00:00', '14:00:00', '14:30:00', 15, 1),
('Dr Michael Brown', 'Pediatrics', 'Specialist', 'Children''s Clinic', 'm.brown@nhs.uk', '07444444444', 'Wednesday, Thursday, Friday', 'B202', '08:00:00', '20:00:00', '14:00:00', '14:30:00', 15, 1),
('Dr Olivia Taylor', 'Dermatology', 'Specialist', 'Dermatology Clinic', 'o.taylor@nhs.uk', '07555555555', 'Tuesday, Friday', 'C301', '08:00:00', '20:00:00', '14:00:00', '14:30:00', 15, 1),
('Nurse Rachel Green', 'Practice Nursing', 'Nurse', 'Nursing Clinic', 'r.green@nhs.uk', '07666666661', 'Monday, Tuesday, Wednesday, Thursday, Friday', 'N101', '08:00:00', '20:00:00', '12:30:00', '13:00:00', 15, 1),
('Nurse Daniel White', 'Vaccination and Screening', 'Nurse', 'Nursing Clinic', 'd.white@nhs.uk', '07666666662', 'Monday, Tuesday, Wednesday, Thursday, Friday', 'N102', '08:00:00', '20:00:00', '12:30:00', '13:00:00', 15, 1),
('Nurse Sophia Hall', 'Minor Illness and Wound Care', 'Nurse', 'Nursing Clinic', 's.hall@nhs.uk', '07666666663', 'Monday, Tuesday, Wednesday, Thursday, Friday', 'N103', '08:00:00', '20:00:00', '12:30:00', '13:00:00', 15, 1);