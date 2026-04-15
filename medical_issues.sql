DROP TABLE IF EXISTS medical_issues;

CREATE TABLE medical_issues (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    issue_name VARCHAR(120) NOT NULL,
    symptom_group VARCHAR(80) NOT NULL,
    recommended_staff_type ENUM('GP', 'Nurse', 'Either') NOT NULL,
    appointment_type VARCHAR(100) NOT NULL,
    priority_level INT NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO medical_issues
(issue_name, symptom_group, recommended_staff_type, appointment_type, priority_level, is_active)
VALUES
('Chest pain', 'Urgent symptoms', 'GP', 'Urgent GP Consultation', 5, 1),
('Breathing problems', 'Urgent symptoms', 'GP', 'Urgent GP Consultation', 5, 1),
('Persistent fever', 'General illness', 'GP', 'GP Consultation', 4, 1),
('Severe headache', 'General illness', 'GP', 'GP Consultation', 4, 1),
('Abdominal pain', 'General illness', 'GP', 'GP Consultation', 4, 1),
('Mental health concerns', 'Mental health', 'GP', 'Mental Health Review', 4, 1),
('Medication review', 'Medication', 'GP', 'Medication Review', 3, 1),
('Skin rash', 'Skin', 'Either', 'Routine Assessment', 2, 1),
('Back pain', 'Musculoskeletal', 'GP', 'GP Consultation', 3, 1),
('Vaccination / immunisation', 'Preventive care', 'Nurse', 'Vaccination Appointment', 2, 1),
('Blood pressure check', 'Monitoring', 'Nurse', 'Nurse Check-Up', 2, 1),
('Wound dressing', 'Minor treatment', 'Nurse', 'Wound Care Appointment', 3, 1),
('Minor infection symptoms', 'Minor illness', 'Nurse', 'Minor Illness Appointment', 2, 1),
('Diabetes check', 'Monitoring', 'Nurse', 'Long-Term Condition Review', 3, 1),
('General tiredness / fatigue', 'General illness', 'GP', 'GP Consultation', 2, 1),
('Earache', 'Minor illness', 'Either', 'Routine Assessment', 2, 1),
('Sore throat', 'Minor illness', 'Either', 'Routine Assessment', 2, 1),
('Child health concern', 'Children', 'GP', 'Child Health Consultation', 3, 1);