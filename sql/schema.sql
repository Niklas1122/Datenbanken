-- Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
-- Zweck: Einfaches Gesamtschema fuer Teams, Fahrer, Trainings, Rennen, Meldungen und Ergebnisse.

CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS teamchefs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    first_name VARCHAR(120) NOT NULL,
    last_name VARCHAR(120) NOT NULL,
    login_name VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    CONSTRAINT fk_teamchefs_team
        FOREIGN KEY (team_id) REFERENCES teams(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS organizers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_name VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS bikes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    brand VARCHAR(120) NOT NULL,
    tire_size INT NOT NULL DEFAULT 28,
    tire_width DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_bikes_team
        FOREIGN KEY (team_id) REFERENCES teams(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS riders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    employee_id VARCHAR(50) NOT NULL,
    rider_name VARCHAR(120) NOT NULL,
    address_text VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    bike_id INT NOT NULL,
    CONSTRAINT uq_rider_employee UNIQUE (team_id, employee_id),
    CONSTRAINT fk_riders_team
        FOREIGN KEY (team_id) REFERENCES teams(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_riders_bike
        FOREIGN KEY (bike_id) REFERENCES bikes(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS training_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    goal_name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rider_id INT NOT NULL,
    bike_id INT NOT NULL,
    training_date DATE NOT NULL,
    kilometers DECIMAL(8,2) NOT NULL,
    goal_id INT NOT NULL,
    CONSTRAINT uq_training_per_day UNIQUE (rider_id, training_date),
    CONSTRAINT fk_trainings_rider
        FOREIGN KEY (rider_id) REFERENCES riders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_trainings_bike
        FOREIGN KEY (bike_id) REFERENCES bikes(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_trainings_goal
        FOREIGN KEY (goal_id) REFERENCES training_goals(id)
        ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS races (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    race_date DATE NOT NULL,
    start_location VARCHAR(120) NOT NULL,
    race_kilometers DECIMAL(8,2) NOT NULL,
    elevation_meters INT NOT NULL,
    max_gradient_percent DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_races_organizer
        FOREIGN KEY (organizer_id) REFERENCES organizers(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS race_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    race_id INT NOT NULL,
    rider_id INT NOT NULL,
    start_number INT NOT NULL,
    CONSTRAINT uq_race_rider UNIQUE (race_id, rider_id),
    CONSTRAINT uq_race_start_number UNIQUE (race_id, start_number),
    CONSTRAINT fk_registrations_race
        FOREIGN KEY (race_id) REFERENCES races(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_registrations_rider
        FOREIGN KEY (rider_id) REFERENCES riders(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS race_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    placing INT NOT NULL,
    medal VARCHAR(80) NOT NULL,
    ride_time VARCHAR(40) NOT NULL,
    CONSTRAINT fk_results_registration
        FOREIGN KEY (registration_id) REFERENCES race_registrations(id)
        ON DELETE CASCADE
);

INSERT IGNORE INTO training_goals (id, goal_name) VALUES
    (1, 'Ausdauer'),
    (2, 'Sprintkraft'),
    (3, 'Steigungen');
