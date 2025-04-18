<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get recent blogs
$recent_sql = "SELECT b.*, u.username FROM blogs b 
              JOIN users u ON b.user_id = u.user_id 
              WHERE b.status = 'published' 
              ORDER BY b.created_at DESC LIMIT 6";
$recent_result = $conn->query($recent_sql);
$recent_blogs = [];

while ($row = $recent_result->fetch_assoc()) {
    $recent_blogs[] = $row;
}

// Get categories
$categories_sql = "SELECT c.*, COUNT(bc.blog_id) as blog_count 
                  FROM categories c 
                  LEFT JOIN blog_categories bc ON c.category_id = bc.category_id 
                  GROUP BY c.category_id 
                  ORDER BY blog_count DESC";
$categories_result = $conn->query($categories_sql);
$categories = [];

while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

$base_url = '/blogHive/'; // Adjust based on your server configuration

// Blog types for carousel
$blog_types = [
    [
        'name' => 'Business',
        'description' => 'Explore business insights, entrepreneurship tips, market trends, and success stories from industry leaders.',
        'icon' => 'fa-briefcase'
    ],
    [
        'name' => 'Technology',
        'description' => 'Discover the latest tech innovations, gadget reviews, coding tutorials, and digital transformation stories.',
        'icon' => 'fa-microchip'
    ],
    [
        'name' => 'Lifestyle',
        'description' => 'Find inspiration for better living through wellness tips, travel experiences, home decor ideas, and personal growth.',
        'icon' => 'fa-heart'
    ],
    [
        'name' => 'Entertainment',
        'description' => 'Stay updated with movie reviews, music releases, celebrity news, and trending entertainment content.',
        'icon' => 'fa-film'
    ],
    [
        'name' => 'Education',
        'description' => 'Access learning resources, academic insights, educational trends, and knowledge-sharing content.',
        'icon' => 'fa-graduation-cap'
    ]
];
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>blogHive - Share Your Stories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/images/logo.jpeg">
  </head>
  <body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="main">
      <div class="main-content">
        <h1>Join Us to Explore Blogs</h1>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="auth/signup.php" class="btn btn-light mt-5">Sign Up</a>
        <?php else: ?>
          <a href="blog/create.php" class="btn btn-light mt-5">Create Blog</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Blog Types Carousel Section -->
    <div class="section bg-dark text-white py-5">
      <div class="container">
        <h2 class="text-center mb-5">Explore Blog Categories</h2>
        
        <div id="blogTypesCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <?php foreach ($blog_types as $index => $type): ?>
              <button type="button" data-bs-target="#blogTypesCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
          </div>
          <div class="carousel-inner">
            <?php foreach ($blog_types as $index => $type): ?>
              <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="d-block w-100 bg-dark text-bg-light p-5">
                  <div class="blog-type text-center">
                    <i class="fas <?php echo $type['icon']; ?> fa-4x mb-4"></i>
                    <h1><?php echo $type['name']; ?></h1>
                    <p class="lead mt-4"><?php echo $type['description']; ?></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#blogTypesCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#blogTypesCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Recent Blogs Section -->
    <div class="section py-5">
      <div class="container">
        <div class="row mb-4">
          <div class="col-md-8">
            <h2>Recent Blogs</h2>
          </div>
          <div class="col-md-4 text-end">
            <a href="blog/list.php" class="btn btn-outline-dark">View All</a>
          </div>
        </div>
        
        <div class="row">
          <?php foreach ($recent_blogs as $blog): ?>
            <div class="col-md-4 mb-4">
              <div class="card h-100">
                <?php if (!empty($blog['featured_image']) && $blog['featured_image'] != 'default_blog.jpg'): ?>
                  <img src="assets/images/blogs/<?php echo $blog['featured_image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                <?php else: ?>
                  <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fa-solid fa-blog fa-3x"></i>
                  </div>
                <?php endif; ?>
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h5>
                  <p class="card-text text-muted small">By <?php echo htmlspecialchars($blog['username']); ?> | <?php echo date('F j, Y', strtotime($blog['created_at'])); ?></p>
                  <p class="card-text"><?php echo substr(htmlspecialchars($blog['content']), 0, 100) . '...'; ?></p>
                </div>
                <div class="card-footer bg-white border-0">
                  <a href="blog/view.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-dark">Read More</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="foot">
      &#x2728; &copy; All rights are reserved <?php echo date('Y'); ?> &#x2728;
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
