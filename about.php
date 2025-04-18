<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$base_url = '/blogHive/'; // Adjust based on your server configuration
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - blogHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/images/logo.jpeg">
  </head>
  <body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header bg-dark text-white">
              <h2>About blogHive</h2>
            </div>
            <div class="card-body">
              <p>Welcome to blogHive, a platform where writers can share their stories and connect with readers.</p>
              
              <h4 class="mt-4">Our Mission</h4>
              <p>To provide a simple and user-friendly platform for bloggers to express themselves and share their knowledge with the world.</p>
              
              <h4 class="mt-4">Contact Us</h4>
              <p>Email: contact@bloghive.com</p>
              <p>Phone: (123) 456-7890</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="foot mt-5">
      &#x2728; &copy; All rights are reserved <?php echo date('Y'); ?> &#x2728;
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
