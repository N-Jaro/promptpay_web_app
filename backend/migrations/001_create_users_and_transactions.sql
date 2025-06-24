-- migrations/001_create_users_and_transactions.sql

CREATE TABLE users
(
    id INT
    AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR
    (255) NOT NULL UNIQUE,
    name VARCHAR
    (255) NOT NULL,
    google_id VARCHAR
    (255) UNIQUE,
    avatar VARCHAR
    (500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

    CREATE TABLE transactions
    (
        id INT
        AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL
        (10,2) NOT NULL,
    description VARCHAR
        (10000),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY
        (user_id) REFERENCES users
        (id) ON
        DELETE CASCADE
);
