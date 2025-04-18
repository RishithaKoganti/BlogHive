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
$error = '';
$success = '';
$base_url = '/BlogHive/'; // Adjust based on your server configuration

// Get blog data
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
    $_SESSION['error'] = "You don't have permission to edit this blog.";
    redirect("view.php?id=$blog_id");
}

// Get categories for dropdown
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Get selected categories for this blog
$selected_categories_sql = "SELECT category_id FROM blog_categories WHERE blog_id = ?";
$stmt = $conn->prepare($selected_categories_sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$selected_result = $stmt->get_result();
$selected_categories = [];
while ($row = $selected_result->fetch_assoc()) {
    $selected_categories[] = $row['category_id'];
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
        $featured_image = $blog['featured_image']; // Keep existing image by default
        
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
                    // Delete old image if it's not the default
                    if ($featured_image != 'default_blog.jpg' && file_exists($upload_dir . $featured_image)) {
                        unlink($upload_dir . $featured_image);
                    }
                    $featured_image = $new_filename;
                } else {
                    $error = "Failed to upload image";
                }
            } else {
                $error = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed";
            }
        }
        
        if (empty($error)) {
            // Update blog post
            $sql = "UPDATE blogs SET title = ?, content = ?, featured_image = ?, status = ?, updated_at = NOW() WHERE blog_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $content, $featured_image, $status, $blog_id);
            
            if ($stmt->execute()) {
                // Delete existing category associations
                $delete_categories = "DELETE FROM blog_categories WHERE blog_id = ?";
                $delete_stmt = $conn->prepare($delete_categories);
                $delete_stmt->bind_param("i", $blog_id);
                $delete_stmt->execute();
                
                // Insert updated blog categories
                if (!empty($selected_categories)) {
                    $category_sql = "INSERT INTO blog_categories (blog_id, category_id) VALUES (?, ?)";
                    $category_stmt = $conn->prepare($category_sql);
                    
                    foreach ($selected_categories as $category_id) {
                        $category_stmt->bind_param("ii", $blog_id, $category_id);
                        $category_stmt->execute();
                    }
                }
                
                $success = "Blog updated successfully!";
                // Redirect to view the updated blog
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
    <title>Edit Blog - BlogHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.jpeg">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3>Edit Blog</h3>
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
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Blog Content</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
                                <small class="text-muted">You can use line breaks for paragraphs.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Featured Image</label>
                                <?php if (!empty($blog['featured_image']) && $blog['featured_image'] != 'default_blog.jpg'): ?>
                                    <div class="mb-2">
                                        <img src="../assets/images/blogs/<?php echo $blog['featured_image']; ?>" alt="Current image" style="max-width: 200px; max-height: 150px;">
                                        <p class="small">Current image</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="featured_image" name="featured_image">
                                <small class="text-muted">Optional. Leave empty to keep current image. Supported formats: JPG, JPEG, PNG, GIF</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Categories</label>
                                <div class="row">
                                    <?php foreach ($categories as $category): ?>
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="categories[]" 
                                                       value="<?php echo $category['category_id']; ?>" 
                                                       id="category_<?php echo $category['category_id']; ?>"
                                                       <?php echo in_array($category['category_id'], $selected_categories) ? 'checked' : ''; ?>>
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
                                    <option value="published" <?php echo $blog['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo $blog['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark">Update Blog</button>
                                <a href="view.php?id=<?php echo $blog_id; ?>" class="btn btn-outline-secondary">Cancel</a>
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
