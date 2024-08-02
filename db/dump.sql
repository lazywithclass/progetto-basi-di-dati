SET search_path TO public;

DROP TABLE IF EXISTS librarians;

-- creation
CREATE TABLE librarians (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

-- data

-- Password is the hashed username
INSERT INTO librarians (username, password_hash, email) VALUES ('brooks', 'reUO0yT0/590.', 'brooks.hatlen@shawshank.com');
INSERT INTO librarians (username, password_hash, email) VALUES ('conan', 're0SmNOQGPBoE', 'conan.the.librarian@cimmeria.com');
