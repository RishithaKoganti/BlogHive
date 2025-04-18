<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$base_url = '/BlogHive/'; // Adjust based on your server configuration

// Get categories for dropdown
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
$categories = [];

while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize_input($_POST['title']);
    $content = $_POST['content']; // Don't sanitize content here to preserve formatting
    $status = sanitize_input($_POST['status']);
    $selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Validate inputs
    if (empty($title) || empty($content)) {
        $error = "Title and content are required";
    } else {
        // Handle image upload if provided
        $featured_image = 'default_blog.jpg'; // Default image
        
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['featured_image']['name'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed)) {
                $new_filename = uniqid() . '.' . $file_ext;
                $upload_dir = '../assets/images/blogs/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $featured_image = $new_filename;
                } else {
                    $error = "Failed to upload image";
                }
            } else {
                $error = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed";
            }
        }
        
        if (empty($error)) {
            // Insert blog post
            $sql = "INSERT INTO blogs (user_id, title, content, featured_image, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $_SESSION['user_id'], $title, $content, $featured_image, $status);
            
            if ($stmt->execute()) {
                $blog_id = $stmt->insert_id;
                
                // Insert blog categories
                if (!empty($selected_categories)) {
                    $category_sql = "INSERT INTO blog_categories (blog_id, category_id) VALUES (?, ?)";
                    $category_stmt = $conn->prepare($category_sql);
                    
                    foreach ($selected_categories as $category_id) {
                        $category_stmt->bind_param("ii", $blog_id, $category_id);
                        $category_stmt->execute();
                    }
                }
                
                $success = "Blog published successfully!";
                // Redirect to view the new blog
                redirect("view.php?id=$blog_id");
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog - BlogHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.jpeg">
    <!-- Removed TinyMCE script -->
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3>Create New Blog</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Blog Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Blog Content</label>
                                <!-- Simple textarea instead of TinyMCE -->
                                <textarea class="form-control" id="content" name="content" rows="15" required 
                                    placeholder="Write your blog content here..."></textarea>
                                <small class="text-muted">You can use line breaks for paragraphs.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="featured_image" name="featured_image">
                                <small class="text-muted">Optional. Supported formats: JPG, JPEG, PNG, GIF</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Categories</label>
                                <div class="row">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $category['category_id']; ?>" id="category_<?php echo $category['category_id']; ?>">
                                                <label class="form-check-label" for="category_<?php echo $category['category_id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark">Publish Blog</button>
                                <a href="../index.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
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
