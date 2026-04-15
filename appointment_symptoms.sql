DROP TABLE IF EXISTS appointment_symptoms;

CREATE TABLE appointment_symptoms (
    appointment_id INT NOT NULL,
    issue_id INT NOT NULL,
    PRIMARY KEY (appointment_id, issue_id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (issue_id) REFERENCES medical_issues(issue_id) ON DELETE CASCADE
);