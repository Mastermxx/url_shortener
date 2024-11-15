<?php
require 'config.php'; // Database connection

// Check if a short code was provided in the URL
if (isset($_GET['c'])) {
    $short_url = $_GET['c'];

    // Look up the original URL and expiration date by short URL
    $stmt = $pdo->prepare("SELECT id, original_url, expiration_date, visit_count FROM urls WHERE short_url = :short_url");
    $stmt->execute(['short_url' => $short_url]);
    $url_data = $stmt->fetch();

    if ($url_data) {
        // Check if the URL has expired
        if ($url_data['expiration_date'] && new DateTime() > new DateTime($url_data['expiration_date'])) {
            die("This link has expired.");
        }

        // Update visit count and last visited timestamp
        $update_stmt = $pdo->prepare("UPDATE urls SET visit_count = visit_count + 1, last_visited = NOW() WHERE id = :id");
        $update_stmt->execute(['id' => $url_data['id']]);

        // Redirect to the original URL
        header("Location: " . $url_data['original_url']);
        exit;
    } else {
        // If no URL found, display an error message
        die("URL not found.");
    }
} else {
    die("No URL specified.");
}
