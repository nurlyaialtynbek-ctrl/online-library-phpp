CREATE DATABASE IF NOT EXISTS online_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_library;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    author VARCHAR(120) NOT NULL,
    publish_year INT NOT NULL,
    description TEXT,
    cover_image VARCHAR(255) DEFAULT NULL,
    available_copies INT NOT NULL DEFAULT 1,
    category_id INT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_book_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_book_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name, description) VALUES
('Көркем әдебиет', 'Қазақ және әлем классикалық әдебиеті'),
('Ақпараттық технологиялар', 'Бағдарламалау, дерекқорлар және желілер'),
('Ғылым және тарих', 'Тарихи зерттеулер мен ғылыми-көпшілік еңбектер'),
('Психология және даму', 'Жеке тұлғалық даму және психология');

INSERT INTO users (full_name, email, password_hash, role) VALUES
('Жүйе Әкімшісі', 'admin@library.kz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Айгерім Сапарбек', 'reader@library.kz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO books (title, author, publish_year, description, available_copies, category_id, created_by) VALUES
('Абай жолы (1-том)', 'Мұхтар Әуезов', 1942, 'Қазақ халқының ұлы ақыны Абай Құнанбайұлының өмірі мен шығармашылығына арналған эпопея.', 5, 1, 1),
('Таза код (Clean Code)', 'Роберт Мартин', 2008, 'Бағдарламалық жасақтаманы сапалы және оқылымды етіп жазу қағидалары.', 3, 2, 1),
('Көшпенділер', 'Ілияс Есенберлин', 1976, 'Қазақ хандығының қалыптасуы мен даму тарихын баяндайтын тарихи трилогия.', 4, 3, 1),
('Атомдық әдеттер', 'Джеймс Клир', 2018, 'Күнделікті шағын әдеттер арқылы үлкен нәтижелерге жету әдістемесі.', 6, 4, 1);
