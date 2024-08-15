SET search_path TO public;

DROP TABLE IF EXISTS author CASCADE;
DROP TABLE IF EXISTS book CASCADE;
DROP TABLE IF EXISTS author_book CASCADE;
DROP TABLE IF EXISTS loan CASCADE;
DROP TABLE IF EXISTS reader CASCADE;
DROP TABLE IF EXISTS librarian CASCADE;
DROP TABLE IF EXISTS library CASCADE;
DROP TABLE IF EXISTS library_reader CASCADE;
DROP TABLE IF EXISTS library_librarian CASCADE;
DROP TABLE IF EXISTS branch CASCADE;

-- creation

CREATE TABLE author (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    birth_date DATE NOT NULL,
    death_date DATE
);

CREATE TABLE book (
    isbn VARCHAR(14) PRIMARY KEY,
    title TEXT NOT NULL,
    publisher VARCHAR(255) NOT NULL,
    plot TEXT NOT NULL
);
CREATE INDEX idx_book_isbn ON book(isbn);
CREATE INDEX idx_book_title ON book(title);

CREATE TABLE author_book (
    author_id INT NOT NULL,
    isbn VARCHAR(14) NOT NULL,
    PRIMARY KEY (author_id, isbn),
    FOREIGN KEY (author_id) REFERENCES author(id) ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES book(isbn) ON DELETE CASCADE
);

CREATE TABLE reader (
    id SERIAL PRIMARY KEY,
    fiscal_code VARCHAR(16) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL
);
CREATE INDEX idx_reader_username ON reader(username);
CREATE INDEX idx_reader_fiscal_code ON reader(fiscal_code);

CREATE TABLE loan (
    id SERIAL PRIMARY KEY,
    id_reader INTEGER NOT NULL,
    id_physical_copy INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    length INTEGER NOT NULL,
    FOREIGN KEY (id_reader) REFERENCES reader(id),
    FOREIGN KEY (id_physical_copy) REFERENCES physical_copy(id)
);

CREATE TABLE librarian (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE library (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE library_reader (
    id_reader INTEGER NOT NULL,
    id_library INTEGER NOT NULL,
    overdue_returns INTEGER NOT NULL DEFAULT 0,
    category VARCHAR(50) NOT NULL DEFAULT 'base',
    PRIMARY KEY (id_reader, id_library),
    FOREIGN KEY (id_reader) REFERENCES reader(id),
    FOREIGN KEY (id_library) REFERENCES library(id)
);

CREATE TABLE library_librarian (
    id_librarian INTEGER NOT NULL,
    id_library INTEGER NOT NULL,
    PRIMARY KEY (id_librarian, id_library),
    FOREIGN KEY (id_librarian) REFERENCES librarian(id),
    FOREIGN KEY (id_library) REFERENCES library(id)
);

CREATE TABLE branch (
    id SERIAL PRIMARY KEY,
    id_library INTEGER NOT NULL,
    city VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_library) REFERENCES library(id)
);


-- data

-- Password is the hashed username
INSERT INTO librarian (username, password_hash, email) VALUES ('brooks', 'reUO0yT0/590.', 'brooks.hatlen@shawshank.com');
INSERT INTO librarian (username, password_hash, email) VALUES ('conan', 're0SmNOQGPBoE', 'conan.the.librarian@cimmeria.com');

INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('GNDLF1234567890', 'gandalf', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe', 'Gandalf', 'Grey');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('FRDBG1234567891', 'frodo', '$2y$10$M.4K9h.kUFeBN/4Q3FvD.6', 'Frodo', 'Baggins');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('GLDML1234567892', 'galadriel', '$2y$10$VJ0pFxY.zx2eVB3.s/ePFO', 'Galadriel', '');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('ASLN01234567893', 'aslan', '$2y$10$K9jhsdf88uhd92jd/', 'Aslan', '');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('PVRL12345678994', 'pippin', '$2y$10$msdguyrqwe78asdh/', 'Pippin', 'Took');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('GMRY12345678995', 'gimli', '$2y$10$QkJD6sXk3oOq8GF.v6snze', 'Gimli', '');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('SMWIS1234567896', 'samwise', '$2y$10$sYQYuwjdyb1jgwoPNO/.D.', 'Samwise', 'Gamgee');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('LEGOL1234567897', 'legolas', '$2y$10$R0mdaQn1KyfHo/Je4N.BvG', 'Legolas', 'Greenleaf');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('MDMRT1234567898', 'madmartigan', '$2y$10$0gm/VfBZhshBXOCwzCGSu/', 'Madmartigan', '');
INSERT INTO reader (fiscal_code, username, password_hash, name, surname) VALUES ('TYRN12345678999', 'tyrion', '$2y$10$Qnwg78juh43/nYsd/', 'Tyrion', 'Lannister');

INSERT INTO author (name, surname, bio, birth_date, death_date) VALUES
('John', 'Tolkien', 'J. R. R. Tolkien was an English writer, poet, philologist, and academic, best known as the author of the classic high-fantasy works The Hobbit, The Lord of the Rings, and The Silmarillion.', '1892-01-03', '1973-09-02'),
('Ernest', 'Hemingway', 'Ernest Hemingway was an American novelist, short-story writer, journalist, and sportsman. His economical and understated style had a strong influence on 20th-century fiction.', '1899-07-21', '1961-07-02'),
('Richard', 'Bach', 'Richard Bach is an American writer, widely known as the author of the hugely popular 1970s best-sellers Jonathan Livingston Seagull and Illusions: The Adventures of a Reluctant Messiah.', '1936-06-23', NULL),
('Ken', 'Follett', 'Ken Follett is a Welsh author of thrillers and historical novels. He has sold more than 160 million copies of his works.', '1949-06-05', NULL);

INSERT INTO book (isbn, title, publisher, plot) VALUES
('978-0618640157', 'The Lord of the Rings', 'George Allen & Unwin', 'An epic high-fantasy novel written by English author and scholar J. R. R. Tolkien. The story began as a sequel to Tolkien''s 1937 fantasy novel The Hobbit, but eventually developed into a much larger work.'),
('978-0684801223', 'The Old Man and the Sea', 'Charles Scribner''s Sons', 'A short novel written by the American author Ernest Hemingway in 1951 in Cuba, and published in 1952. It tells the story of Santiago, an aging Cuban fisherman who struggles with a giant marlin far out in the Gulf Stream.'),
('978-0743278904', 'Jonathan Livingston Seagull', 'Macmillan', 'A fable about a seagull who is trying to learn about life and flight, and a homily about self-perfection. Richard Bach, a former US Air Force pilot, and avowed flying enthusiast who has written numerous works of fiction and non-fiction related to flight.'),
('978-0451225245', 'The Pillars of the Earth', 'William Morrow & Company', 'A historical novel by Ken Follett, published in 1989, about the building of a cathedral in the fictional town of Kingsbridge, England. It is the first in the Kingsbridge Series.');

INSERT INTO author_book (author_id, isbn) VALUES (1, '978-0618640157'), (2, '978-0684801223'), (3, '978-0743278904'), (4, '978-0451225245');

INSERT INTO library (name) VALUES ('Shawshank Library');
INSERT INTO library (name) VALUES ('Minas Tirith Library');
INSERT INTO library (name) VALUES ('Dorian Gray Library');
INSERT INTO library (name) VALUES ('Jedi Archive');
INSERT INTO library (name) VALUES ('Rivendell Library');

INSERT INTO library_librarian (id_librarian, id_library) VALUES (1, 2), (1, 5);
INSERT INTO library_librarian (id_librarian, id_library) VALUES (2, 1), (2, 3), (2, 4);

INSERT INTO library_reader (id_reader, id_library) VALUES (1, 1), (1, 2), (1, 3), (1, 4), (1, 5);
INSERT INTO library_reader (id_reader, id_library) VALUES (2, 1), (2, 2), (2, 3), (2, 4), (2, 5);
INSERT INTO library_reader (id_reader, id_library) VALUES (3, 2), (3, 5);
INSERT INTO library_reader (id_reader, id_library) VALUES (4, 2), (4, 5);
INSERT INTO library_reader (id_reader, id_library, overdue_returns) VALUES (5, 2, 4), (5, 5, 5);
INSERT INTO library_reader (id_reader, id_library, overdue_returns) VALUES (6, 2, 2), (6, 5, 4);
INSERT INTO library_reader (id_reader, id_library, overdue_returns) VALUES (7, 2, 3), (7, 5, 3);
INSERT INTO library_reader (id_reader, id_library) VALUES (8, 2), (8, 5);
INSERT INTO library_reader (id_reader, id_library) VALUES (9, 1), (9, 2), (9, 3), (9, 4), (9, 5);
INSERT INTO library_reader (id_reader, id_library) VALUES (10, 1), (10, 2), (10, 3), (10, 4), (10, 5);

INSERT INTO branch (id_library, city, address) VALUES (1, 'Portland', '123 Oak Street');
INSERT INTO branch (id_library, city, address) VALUES (1, 'Portsmouth', '456 Maple Avenue');
INSERT INTO branch (id_library, city, address) VALUES (2, 'Minas Tirith', '789 White Tree Road');
INSERT INTO branch (id_library, city, address) VALUES (2, 'Osgiliath', '321 Anduin Way');
INSERT INTO branch (id_library, city, address) VALUES (3, 'London', '654 Hyde Park Blvd');
INSERT INTO branch (id_library, city, address) VALUES (3, 'Oxford', '987 Piccadilly Square');
INSERT INTO branch (id_library, city, address) VALUES (4, 'Coruscant', '111 Jedi Temple');
INSERT INTO branch (id_library, city, address) VALUES (4, 'Alderaan', '222 Peaceful Grove');
INSERT INTO branch (id_library, city, address) VALUES (5, 'Rivendell', '333 Elven Path');
INSERT INTO branch (id_library, city, address) VALUES (5, 'Lothlórien', '444 Golden Forest');
