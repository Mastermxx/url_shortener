<?php
require 'config.php'; 

// Query to fetch URL data and analytics
$stmt = $pdo->query("SELECT id, original_url, short_url, visit_count, last_visited, expiration_date FROM urls ORDER BY id DESC");
$urls = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - URL Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Admin Panel - URL Analytics</h1>
        
        <table class="w-full text-left bg-gray-800 text-gray-100 rounded-lg shadow-lg">
            <thead class="bg-gray-700">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Original URL</th>
                    <th class="p-4">Shortened URL</th>
                    <th class="p-4">Visit Count</th>
                    <th class="p-4">Last Visited</th>
                    <th class="p-4">Expiration Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urls as $url): ?>
                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                        <td class="p-4"><?php echo htmlspecialchars($url['id']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($url['original_url']); ?></td>
                        <td class="p-4">
                            <a href="http://localhost:8000/redirect.php?c=<?php echo htmlspecialchars($url['short_url']); ?>" class="text-blue-400 hover:text-blue-500">
                                <?php echo htmlspecialchars($url['short_url']); ?>
                            </a>
                        </td>
                        <td class="p-4"><?php echo htmlspecialchars($url['visit_count']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($url['last_visited'] ?: 'Never'); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($url['expiration_date'] ?: 'No Expiration'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
