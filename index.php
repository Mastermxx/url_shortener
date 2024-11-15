<?php
session_start();
require 'config.php';       // Database connection
require 'functions.php';     // Functions like `generateShortURL`

// Define rate limit
$rate_limit = 5; // Maximum requests per minute
$user_ip = $_SERVER['REMOTE_ADDR']; // Get user's IP address
$rate_limit_exceeded = false; // Track if rate limit was exceeded

// Process URL shortening only if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check the rate limit
    $stmt = $pdo->prepare("SELECT request_count, last_request FROM rate_limit WHERE user_ip = :user_ip");
    $stmt->execute(['user_ip' => $user_ip]);
    $rate_data = $stmt->fetch();
    $current_time = new DateTime();

    if ($rate_data) {
        $last_request_time = new DateTime($rate_data['last_request']);
        $time_diff = $current_time->getTimestamp() - $last_request_time->getTimestamp();

        if ($time_diff < 60) {
            // If within the same minute, check if request count exceeds the limit
            if ($rate_data['request_count'] >= $rate_limit) {
                $rate_limit_exceeded = true;
            } else {
                // Increment request count
                $stmt = $pdo->prepare("UPDATE rate_limit SET request_count = request_count + 1 WHERE user_ip = :user_ip");
                $stmt->execute(['user_ip' => $user_ip]);
            }
        } else {
            // Reset the request count if more than a minute has passed
            $stmt = $pdo->prepare("UPDATE rate_limit SET request_count = 1, last_request = NOW() WHERE user_ip = :user_ip");
            $stmt->execute(['user_ip' => $user_ip]);
        }
    } else {
        // Insert a new record for a new user IP
        $stmt = $pdo->prepare("INSERT INTO rate_limit (user_ip, request_count, last_request) VALUES (:user_ip, 1, NOW())");
        $stmt->execute(['user_ip' => $user_ip]);
    }

    // If rate limit is not exceeded, proceed with URL shortening
    if (!$rate_limit_exceeded) {
        $original_url = $_POST['url'];
        $expiration_amount = isset($_POST['expiration_amount']) ? $_POST['expiration_amount'] : 1;
        $expiration_unit = isset($_POST['expiration_unit']) ? $_POST['expiration_unit'] : 'days';
        $timestamp = date("Y-m-d H:i:s");

        $expiration_date = calculateExpiration($expiration_amount, $expiration_unit);

        try {
            $stmt = $pdo->prepare("INSERT INTO urls (original_url, timestamp_date, expiration_date) VALUES (:original_url, :timestamp_date, :expiration_date)");
            $stmt->execute([
                'original_url' => $original_url,
                'timestamp_date' => $timestamp,
                'expiration_date' => $expiration_date
            ]);
            $url_id = $pdo->lastInsertId();

            $short_url = base62Encode($url_id);
            $update_stmt = $pdo->prepare("UPDATE urls SET short_url = :short_url WHERE id = :id");
            $update_stmt->execute(['short_url' => $short_url, 'id' => $url_id]);

            $_SESSION['success'] = "Shortened URL: <a href='http://localhost:8000/redirect.php?c=" . $short_url . "'>http://localhost:8000/redirect.php?c=" . $short_url . "</a>";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            echo "Error saving URL: " . $e->getMessage();
        }
    } else {
        // Set an error message for exceeding the rate limit
        $_SESSION['error'] = "Rate limit exceeded. Please wait a minute before trying again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>URL Shortener</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">URL Shortener</h1>

        <!-- Display rate limit error message if set -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="text-red-500 bg-red-100 p-4 rounded mb-4">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Display success message if set -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="text-green-500 bg-green-100 p-4 rounded mb-4">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Form for URL shortening -->
        <form method="post" class="max-w-md mx-auto bg-gray-800 p-6 rounded-lg shadow-md">
            <input type="url" name="url" placeholder="Enter URL" required class="w-full p-2 border border-gray-700 rounded mb-4 bg-gray-900 text-gray-100">
            <input type="number" name="expiration_amount" placeholder="Enter amount" required min="1" class="w-full p-2 border border-gray-700 rounded mb-4 bg-gray-900 text-gray-100">
            <select name="expiration_unit" required class="w-full p-2 border border-gray-700 rounded mb-4 bg-gray-900 text-gray-100">
                <option value="seconds">Seconds</option>
                <option value="minutes">Minutes</option>
                <option value="hours">Hours</option>
                <option value="days">Days</option>
                <option value="weeks">Weeks</option>
                <option value="months">Months</option>
            </select>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded mt-4 hover:bg-blue-700">Shorten</button>
        </form>

        <!-- Admin button -->
        <div class="mt-4">
            <a href="admin.php" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded">Admin</a>
        </div>
    </div>
</body>
</html>
