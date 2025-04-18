<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured blogs for carousel
$featured_sql = "SELECT b.*, u.username FROM blogs b 
                JOIN users u ON b.user_id = u.user_id 
                WHERE b.status = 'published' 
                ORDER BY b.views DESC LIMIT 3";
$featured_result = $conn->query($featured_sql);
$featured_blogs = [];

while ($row = $featured_result->fetch_assoc()) {
    $featured_blogs[] = $row;
}

$base_url = '/blogHive/'; // Adjust based on your server configuration
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>blogHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/images/logo.jpeg">
  </head>
