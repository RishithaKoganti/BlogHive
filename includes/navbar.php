<?php
$current_page = basename($_SERVER['PHP_SELF']);
$base_url = '/blogHive/'; // Adjust based on your server configuration
?>

<!-- Navbar -->
<nav class="navbar text-bg-secondary p-3 navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
      <h2><i class="fa-solid fa-blog"></i>&nbsp;&nbsp;&nbsp;blogHive</h2>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="d-flex justify-content-end collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item p-2">
          <a class="nav-link <?php echo ($current_page == 'index.php' || $current_page == 'homepage.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">Home</a>
        </li>
        <li class="nav-item p-2">
          <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>about.php">About Us</a>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item p-2">
            <a class="nav-link" href="<?php echo $base_url; ?>blog/create.php">Create Blog</a>
          </li>
          <li class="nav-item dropdown p-2">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <?php echo $_SESSION['username']; ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>blog/list.php?user=<?php echo $_SESSION['user_id']; ?>">My Blogs</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo $base_url; ?>auth/logout.php">Logout</a></li>            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item p-2">
            <a class="btn btn-dark" href="<?php echo $base_url; ?>auth/login.php">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
