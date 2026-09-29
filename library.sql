CREATE DATABASE IF NOT EXISTS librarydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE librarydb;

SET NAMES utf8mb4;

-- Development reset: importing again removes existing accounts and reservations.
-- Drop children before the tables they reference.
DROP TABLE IF EXISTS reserved_books;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  username VARCHAR(50) PRIMARY KEY,
  fullname VARCHAR(100),
  email VARCHAR(100),
  mobile VARCHAR(10),
  password_hash VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  category_code INT PRIMARY KEY AUTO_INCREMENT,
  category_description VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE books (
  isbn VARCHAR(20) PRIMARY KEY,
  title VARCHAR(255),
  author VARCHAR(200),
  category_code INT,
  publisher VARCHAR(200),
  year_published YEAR,
  FOREIGN KEY (category_code) REFERENCES categories(category_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reserved_books (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50),
  isbn VARCHAR(20),
  reserved_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (isbn),
  FOREIGN KEY (username) REFERENCES users(username),
  FOREIGN KEY (isbn) REFERENCES books(isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (category_description) VALUES
('Fiction'),('Non-Fiction'),('Business'),
('Computer Science'),('History'),('Science');

/* FULL 25 BOOKS */
INSERT INTO books (isbn, title, author, category_code, publisher, year_published) VALUES
('9780140449136','The Odyssey','Homer',1,'Penguin',1996),
('9780679783268','Pride and Prejudice','Jane Austen',1,'Vintage',2000),
('9780743273565','The Great Gatsby','Fitzgerald',1,'Scribner',2004),
('9780385348119','The Martian','Andy Weir',1,'Crown',2014),
('9780099406133','The Lord of the Rings','Tolkien',1,'HarperCollins',1954),
('9780747532743','Harry Potter and the Philosopher''s Stone','J.K. Rowling',1,'Bloomsbury',1997),
('9780062316110','The Alchemist','Paulo Coelho',1,'HarperOne',1988),
('9780307277671','The Road','Cormac McCarthy',1,'Vintage',2006),

('9780374533557','Thinking, Fast and Slow','Daniel Kahneman',2,'FSG',2011),
('9780553382563','A Short History of Nearly Everything','Bill Bryson',2,'Broadway Books',2003),
('9780307465351','The Lean Startup','Eric Ries',3,'Crown',2011),
('9781451648539','Steve Jobs','Walter Isaacson',3,'Simon & Schuster',2011),
('9781593275990','Eloquent JavaScript','Marijn Haverbeke',4,'No Starch Press',2014),
('9780131103627','The C Programming Language','K&R',4,'Prentice Hall',1988),
('9780321125217','Domain Driven Design','Eric Evans',4,'Addison-Wesley',2003),
('9780262033848','Introduction to Algorithms','CLRS',4,'MIT Press',2009),
('9781491957660','Fluent Python','Luciano Ramalho',4,'O’Reilly',2015),

('9780140104765','The Art of War','Sun Tzu',5,'Penguin',2003),
('9780192804587','The History of the Ancient World','Susan Bauer',5,'Norton',2007),
('9780307947307','Sapiens','Yuval Harari',5,'Vintage',2015),

('9780393354324','Astrophysics for People in a Hurry','Neil deGrasse Tyson',6,'WW Norton',2017),
('9780465030798','The Elegant Universe','Brian Greene',6,'Vintage',2003),
('9780307387842','The Gene','Siddhartha Mukherjee',6,'Scribner',2016),
('9781984823151','Cosmos','Carl Sagan',6,'Random House',1980),
('9780345803481','The Immortal Life of Henrietta Lacks','Rebecca Skloot',6,'Broadway',2010);
