<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

// Check if blog ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('list.php');
}

$blog_id = $_GET['id'];
$base_url = '/BlogHive/'; // Adjust based on your server configuration

// Get blog data to check ownership
$blog_sql = "SELECT * FROM blogs WHERE blog_id = ?";
$stmt = $conn->prepare($blog_sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$blog_result = $stmt->get_result();

if ($blog_result->num_rows == 0) {
    redirect('list.php');
}

$blog = $blog_result->fetch_assoc();

// Check if user is the author
if ($_SESSION['user_id'] != $blog['user_id']) {
    $_SESSION['error'] = "You don't have permission to delete this blog.";
    redirect("view.php?id=$blog_id");
}

// Process deletion if confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Delete blog categories first (foreign key constraint)
    $delete_categories = "DELETE FROM blog_categories WHERE blog_id = ?";
    $delete_stmt = $conn->prepare($delete_categories);
    $delete_stmt->bind_param("i", $blog_id);
    $delete_stmt->execute();
    
    // Delete the blog
    $delete_blog = "DELETE FROM blogs WHERE blog_id = ?";
    $delete_stmt = $conn->prepare($delete_blog);
    $delete_stmt->bind_param("i", $blog_id);
    
    if ($delete_stmt->execute()) {
        // Delete image file if not default
        if ($blog['featured_image'] != 'default_blog.jpg') {
            $image_path = '../assets/images/blogs/' . $blog['featured_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $_SESSION['success'] = "Blog deleted successfully.";
        redirect("list.php?user=" . $_SESSION['user_id']);
    } else {
        $_SESSION['error'] = "Error deleting blog: " . $conn->error;
        redirect("view.php?id=$blog_id");
    }
} else {
    // Show confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Blog - BlogHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.jpeg">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
                    </div>
                    <div class="card-body text-center">
                        <h4>Are you sure you want to delete this blog?</h4>
                        <p class="lead">"<?php echo htmlspecialchars($blog['title']); ?>"</p>
                        <p class="text-danger">This action cannot be undone!</p>
                        
                        <div class="mt-4 d-flex justify-content-center gap-3">
                            <a href="delete.php?id=<?php echo $blog_id; ?>&confirm=yes" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Yes, Delete Blog
                            </a>
                            <a href="view.php?id=<?php echo $blog_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-5"></div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
?>
