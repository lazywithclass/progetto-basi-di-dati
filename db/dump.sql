SET search_path TO public;

DROP TABLE IF EXISTS librarian CASCADE;
DROP TABLE IF EXISTS reader CASCADE;
DROP TABLE IF EXISTS author_book CASCADE;
DROP TABLE IF EXISTS author CASCADE;
DROP TABLE IF EXISTS book CASCADE;

-- creation

CREATE TABLE librarian (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE reader (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fiscal_code VARCHAR(16) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL
);
CREATE INDEX idx_reader_username ON reader(username);
CREATE INDEX idx_reader_fiscal_code ON reader(fiscal_code);

CREATE TABLE book (
    isbn VARCHAR(14) PRIMARY KEY,
    title TEXT NOT NULL,
    publisher VARCHAR(255) NOT NULL,
    plot TEXT NOT NULL
);
CREATE INDEX idx_book_isbn ON book(isbn);
CREATE INDEX idx_book_title ON book(title);

CREATE TABLE author (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    birth_date DATE NOT NULL,
    death_date DATE
);

CREATE TABLE author_book (
    author_id INT NOT NULL,
    isbn VARCHAR(14) NOT NULL,
    PRIMARY KEY (author_id, isbn),
    FOREIGN KEY (author_id) REFERENCES author(id) ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES book(isbn) ON DELETE CASCADE
);


-- data

-- Password is the hashed username
INSERT INTO librarian (username, password_hash, email) VALUES ('brooks', 'reUO0yT0/590.', 'brooks.hatlen@shawshank.com');
INSERT INTO librarian (username, password_hash, email) VALUES ('conan', 're0SmNOQGPBoE', 'conan.the.librarian@cimmeria.com');

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
