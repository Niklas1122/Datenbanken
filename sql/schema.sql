-- Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
-- Zweck: Minimales Schema fuer den ersten Pflichtteil des Projekts.

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
