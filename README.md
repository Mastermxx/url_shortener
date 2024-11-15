# URL Shortener with Rate Limiting

This is a simple URL shortener built with PHP and MySQL, featuring rate limiting to prevent abuse. Users can input a long URL and receive a shortened version, which can be set to expire after a specified time. Rate limiting restricts users to a specific number of URL shortening requests per minute.

## Requirements

- **PHP** 8.0 or higher
- **MySQL** 8.0 or higher
- **Web Server** (optional if using PHP’s built-in server)
- **Composer** (optional if external libraries are added)

## Setup Instructions

### 1. Clone or Extract Project Files

Unzip the project files or clone the repository to your desired location.

### 2. Configure the Database

1. Open **MySQL Workbench** or your MySQL client.
2. Create a new database named `url_shortener` (or any name you prefer).

    ```sql
    CREATE DATABASE url_shortener;
    ```

3. Select the `url_shortener` database.

    ```sql
    USE url_shortener;
    ```

4. Create the required tables:

    **`urls` Table**: Stores each URL and its expiration information.

    ```sql
    CREATE TABLE IF NOT EXISTS urls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        original_url VARCHAR(2048) NOT NULL,
        short_url VARCHAR(10) UNIQUE,
        timestamp_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expiration_date DATETIME DEFAULT NULL,
        visit_count INT DEFAULT 0,
        last_visited DATETIME DEFAULT NULL
    );
    ```

    **`rate_limit` Table**: Stores request counts by user IP for rate limiting.

    ```sql
    CREATE TABLE IF NOT EXISTS rate_limit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_ip VARCHAR(45) NOT NULL,
        request_count INT DEFAULT 0,
        last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );
    ```

### 3. Configure Database Connection

1. Open the `config.php` file in the project directory.
2. Update the database credentials with your MySQL username, password, and the database name.

    ```php
    <?php
    // config.php
    $host = 'localhost';
    $db   = 'url_shortener'; // Name of your database
    $user = 'your_username'; // Your MySQL username
    $pass = 'your_password'; // Your MySQL password
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    ```

### 4. Start the PHP Server

Navigate to the project folder in the terminal and start the PHP server:

```bash
php -S localhost:8000
```

### 5. Access the Application

1. Open a web browser.
2. Go to `http://localhost:8000/index.php` to access the URL shortener.

### 6. Testing the Rate Limiting

- The rate limit is set to 5 requests per minute. Try shortening a URL multiple times to observe the rate limit in action.
- When the rate limit is exceeded, an error message will display: `Rate limit exceeded. Please wait a minute before trying again.`

### 7. Optional: Customizing Rate Limits

You can adjust the rate limit by modifying the `$rate_limit` variable in `index.php`:

```php
$rate_limit = 5; // Adjust the number of allowed requests per minute
