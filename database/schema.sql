-- University Laboratory Equipment Lending Management System
-- Relational schema (MySQL 8 / MariaDB 10.4+ as shipped with XAMPP)

CREATE DATABASE IF NOT EXISTS lab_lending
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lab_lending;

CREATE TABLE users (
    user_id        INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('Administrator', 'LaboratoryStaff') NOT NULL,
    is_active      BOOLEAN NOT NULL DEFAULT TRUE,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE borrowers (
    borrower_id       INT AUTO_INCREMENT PRIMARY KEY,
    student_staff_id  VARCHAR(20) NOT NULL UNIQUE,
    full_name         VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL,
    contact_number    VARCHAR(20) NOT NULL,
    registered_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE equipment (
    equipment_id         INT AUTO_INCREMENT PRIMARY KEY,
    equipment_code       VARCHAR(30) NOT NULL UNIQUE,
    name                 VARCHAR(100) NOT NULL,
    category             VARCHAR(50) NOT NULL,
    condition_status     ENUM('Good', 'Fair', 'Damaged') NOT NULL DEFAULT 'Good',
    availability_status  ENUM('Available', 'OnLoan') NOT NULL DEFAULT 'Available',
    default_loan_days    INT NOT NULL,
    CONSTRAINT chk_equipment_loan_days CHECK (default_loan_days > 0)
) ENGINE = InnoDB;

CREATE TABLE loans (
    loan_id           INT AUTO_INCREMENT PRIMARY KEY,
    borrower_id       INT NOT NULL,
    equipment_id      INT NOT NULL,
    issued_by         INT NOT NULL,
    issue_date        DATE NOT NULL,
    due_date          DATE NOT NULL,
    return_date       DATE NULL,
    return_condition  ENUM('Good', 'Fair', 'Damaged') NULL,
    renewal_count     TINYINT NOT NULL DEFAULT 0,
    status            ENUM('Active', 'Overdue', 'Returned') NOT NULL DEFAULT 'Active',
    CONSTRAINT fk_loans_borrower  FOREIGN KEY (borrower_id)  REFERENCES borrowers (borrower_id),
    CONSTRAINT fk_loans_equipment FOREIGN KEY (equipment_id) REFERENCES equipment (equipment_id),
    CONSTRAINT fk_loans_issued_by FOREIGN KEY (issued_by)    REFERENCES users (user_id),
    CONSTRAINT chk_loans_due_date      CHECK (due_date >= issue_date),
    CONSTRAINT chk_loans_return_date   CHECK (return_date IS NULL OR return_date >= issue_date),
    CONSTRAINT chk_loans_renewal_count CHECK (renewal_count BETWEEN 0 AND 2),
    INDEX idx_loans_status_due (status, due_date),
    INDEX idx_loans_equipment_status (equipment_id, status)
) ENGINE = InnoDB;

CREATE TABLE renewals (
    renewal_id         INT AUTO_INCREMENT PRIMARY KEY,
    loan_id            INT NOT NULL,
    previous_due_date  DATE NOT NULL,
    revised_due_date   DATE NOT NULL,
    renewal_date       DATE NOT NULL,
    renewed_by         INT NOT NULL,
    CONSTRAINT fk_renewals_loan       FOREIGN KEY (loan_id)    REFERENCES loans (loan_id),
    CONSTRAINT fk_renewals_renewed_by FOREIGN KEY (renewed_by) REFERENCES users (user_id),
    CONSTRAINT chk_renewals_dates CHECK (revised_due_date > previous_due_date)
) ENGINE = InnoDB;

CREATE TABLE notifications (
    notification_id  INT AUTO_INCREMENT PRIMARY KEY,
    loan_id          INT NOT NULL,
    sent_by          INT NOT NULL,
    recipient_email  VARCHAR(150) NOT NULL,
    sent_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    delivery_status  ENUM('Sent', 'Failed') NOT NULL,
    CONSTRAINT fk_notifications_loan    FOREIGN KEY (loan_id) REFERENCES loans (loan_id),
    CONSTRAINT fk_notifications_sent_by FOREIGN KEY (sent_by) REFERENCES users (user_id),
    INDEX idx_notifications_loan_status_sent (loan_id, delivery_status, sent_at)
) ENGINE = InnoDB;

-- The first Administrator account is created by a one-time PHP setup script,
-- because the password must be hashed with password_hash() before it is stored.
-- Run:  C:\xampp\php\php.exe database\setup_admin.php "Full Name" admin@university.lk "Password"
