SET search_path TO public;

DROP TABLE IF EXISTS librarians;
DROP TABLE IF EXISTS readers;

-- creation

CREATE TABLE librarians (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE readers (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fiscal_code VARCHAR(16) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL
);
CREATE INDEX idx_readers_username ON readers(username);
CREATE INDEX idx_readers_fiscal_code ON readers(fiscal_code);


-- data

-- Password is the hashed username
INSERT INTO librarians (username, password_hash, email) VALUES ('brooks', 'reUO0yT0/590.', 'brooks.hatlen@shawshank.com');
INSERT INTO librarians (username, password_hash, email) VALUES ('conan', 're0SmNOQGPBoE', 'conan.the.librarian@cimmeria.com');
