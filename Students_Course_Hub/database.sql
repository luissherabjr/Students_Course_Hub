DROP DATABASE IF EXISTS student_course_hub;
CREATE DATABASE student_course_hub;
USE student_course_hub;

-- Drop tables in reverse dependency order
DROP TABLE IF EXISTS InterestedStudents;
DROP TABLE IF EXISTS ProgrammeModules;
DROP TABLE IF EXISTS AdminUsers;
DROP TABLE IF EXISTS Programmes;
DROP TABLE IF EXISTS Modules;
DROP TABLE IF EXISTS Staff;
DROP TABLE IF EXISTS Levels;

-- =========================
-- LEVELS
-- =========================
CREATE TABLE Levels (
    LevelID INT PRIMARY KEY,
    LevelName VARCHAR(50) NOT NULL
);

-- =========================
-- STAFF
-- =========================
CREATE TABLE Staff (
    StaffID INT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Title VARCHAR(100),
    Bio TEXT,
    Department VARCHAR(100),
    Photo VARCHAR(255),
    Email VARCHAR(100)
);

-- =========================
-- MODULES
-- =========================
CREATE TABLE Modules (
    ModuleID INT PRIMARY KEY,
    ModuleName VARCHAR(150) NOT NULL,
    ModuleLeaderID INT,
    Description TEXT,
    Image VARCHAR(255),
    ImageAlt TEXT,
    Status ENUM('active','inactive') DEFAULT 'active',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ModuleLeaderID) REFERENCES Staff(StaffID)
);

-- =========================
-- PROGRAMMES
-- =========================
CREATE TABLE Programmes (
    ProgrammeID INT PRIMARY KEY AUTO_INCREMENT,
    ProgrammeName VARCHAR(150) NOT NULL,
    LevelID INT,
    ProgrammeLeaderID INT,
    Description TEXT,
    Image VARCHAR(255),
    ImageAlt TEXT,
    Status ENUM('published','draft') DEFAULT 'draft',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (LevelID) REFERENCES Levels(LevelID),
    FOREIGN KEY (ProgrammeLeaderID) REFERENCES Staff(StaffID)
);

-- =========================
-- PROGRAMME MODULES
-- =========================
CREATE TABLE ProgrammeModules (
    ProgrammeModuleID INT PRIMARY KEY AUTO_INCREMENT,
    ProgrammeID INT,
    ModuleID INT,
    Year INT,
    FOREIGN KEY (ProgrammeID) REFERENCES Programmes(ProgrammeID),
    FOREIGN KEY (ModuleID) REFERENCES Modules(ModuleID)
);

-- =========================
-- INTERESTED STUDENTS
-- =========================
CREATE TABLE InterestedStudents (
    InterestID INT AUTO_INCREMENT PRIMARY KEY,
    ProgrammeID INT NOT NULL,
    StudentName VARCHAR(100) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    RegisteredAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('active','withdrawn') DEFAULT 'active',
    WithdrawnAt TIMESTAMP NULL,
    FOREIGN KEY (ProgrammeID) REFERENCES Programmes(ProgrammeID) ON DELETE CASCADE,
    UNIQUE (Email, ProgrammeID)
);

-- =========================
-- ADMIN USERS
-- =========================
CREATE TABLE AdminUsers (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Role ENUM('admin','staff') DEFAULT 'staff',
    StaffID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID) ON DELETE SET NULL
);

-- =========================
-- SEED DATA
-- =========================

-- Levels
INSERT INTO Levels VALUES
(1,'Undergraduate'),
(2,'Postgraduate');

-- Staff
INSERT INTO Staff (StaffID, Name) VALUES
(1,'Dr. Alice Johnson'),
(2,'Dr. Brian Lee'),
(3,'Dr. Carol White'),
(4,'Dr. David Green'),
(5,'Dr. Emma Scott');

-- Modules
INSERT INTO Modules (ModuleID, ModuleName, ModuleLeaderID, Description) VALUES
(1,'Introduction to Programming',1,'Programming basics using Python and Java'),
(2,'Mathematics for Computer Science',2,'Discrete maths and probability'),
(3,'Computer Systems & Architecture',3,'CPU and memory architecture'),
(4,'Databases',4,'SQL and relational database design'),
(5,'Software Engineering',5,'Agile development and design patterns');

-- Programmes
INSERT INTO Programmes (ProgrammeName, LevelID, ProgrammeLeaderID, Description, Status) VALUES
('BSc Computer Science',1,1,'Broad CS degree covering programming and AI','published'),
('BSc Software Engineering',1,2,'Focus on software development lifecycle','published'),
('BSc Artificial Intelligence',1,3,'Machine learning and AI applications','published'),
('MSc Machine Learning',2,1,'Postgraduate AI and ML programme','published');

-- ProgrammeModules
INSERT INTO ProgrammeModules (ProgrammeID, ModuleID, Year) VALUES
(1,1,1),
(1,2,1),
(1,3,2),
(1,4,2),
(1,5,3);

-- Interested Students
INSERT INTO InterestedStudents (ProgrammeID, StudentName, Email) VALUES
(1,'John Doe','john.doe@example.com'),
(2,'Jane Smith','jane.smith@example.com'),
(4,'Alex Brown','alex.brown@example.com');

-- Admin Users
INSERT INTO AdminUsers (Username, Password, FullName, Email, Role, StaffID) VALUES
('admin','admin123','System Administrator','admin@university.ac.uk','admin',1),
('ajohnson','staff123','Dr Alice Johnson','a.johnson@university.ac.uk','staff',1),
('blee','staff123','Dr Brian Lee','b.lee@university.ac.uk','staff',2);

-- =========================
-- INDEXES (Performance)
-- =========================

CREATE INDEX idx_programmes_level ON Programmes(LevelID);
CREATE INDEX idx_programmes_status ON Programmes(Status);
CREATE FULLTEXT INDEX idx_programmes_search ON Programmes(ProgrammeName, Description);

CREATE INDEX idx_modules_leader ON Modules(ModuleLeaderID);
CREATE INDEX idx_modules_status ON Modules(Status);

CREATE INDEX idx_interested_email ON InterestedStudents(Email);
CREATE INDEX idx_interested_status ON InterestedStudents(Status); is this the right one for database according to the requirements of our assignment?