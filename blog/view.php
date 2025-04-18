<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$base_url = '/BlogHive/'; // Adjust based on your server configuration

// Get blog ID from URL
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($blog_id <= 0) {
    redirect('../index.php');
}

// Get blog details
$blog_sql = "SELECT b.*, u.username, u.full_name FROM blogs b
            JOIN users u ON b.user_id = u.user_id
            WHERE b.blog_id = ?";
$blog_stmt = $conn->prepare($blog_sql);
$blog_stmt->bind_param("i", $blog_id);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

if ($blog_result->num_rows == 0) {
    redirect('../index.php');
}

$blog = $blog_result->fetch_assoc();

// Update view count
$update_views = "UPDATE blogs SET views = views + 1 WHERE blog_id = ?";
$update_stmt = $conn->prepare($update_views);
$update_stmt->bind_param("i", $blog_id);
$update_stmt->execute();

// Get blog categories
$categories_sql = "SELECT c.* FROM categories c
                  JOIN blog_categories bc ON c.category_id = bc.category_id
                  WHERE bc.blog_id = ?";
$categories_stmt = $conn->prepare($categories_sql);
$categories_stmt->bind_param("i", $blog_id);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Get comments
$comments_sql = "SELECT c.*, u.username FROM comments c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.blog_id = ?
                ORDER BY c.created_at DESC";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $blog_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = [];
while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}

// Handle comment submission
$comment_error = '';
$comment_success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        redirect('../auth/login.php');
    }
    
    $comment_content = sanitize_input($_POST['comment_content']);
    
    if (empty($comment_content)) {
        $comment_error = "Comment cannot be empty";
    } else {
        $comment_sql = "INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)";
        $comment_stmt = $conn->prepare($comment_sql);
        $comment_stmt->bind_param("iis", $blog_id, $_SESSION['user_id'], $comment_content);
        
        if ($comment_stmt->execute()) {
            $comment_success = "Comment added successfully!";
            // Refresh the page to show the new comment
            redirect("view.php?id=$blog_id");
        } else {
            $comment_error = "Error: " . $comment_stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - BlogHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/images/logo.jpeg">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <span class="text-muted">By <?php echo htmlspecialchars($blog['full_name'] ?: $blog['username']); ?></span>
                            </div>
                            <div>
                                <span class="badge bg-secondary"><i class="fas fa-eye"></i> <?php echo $blog['views']; ?> views</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($categories)): ?>
                            <div class="mb-3">
                                <?php foreach ($categories as $category): ?>
                                    <a href="../blog/list.php?category=<?php echo $category['category_id']; ?>" class="badge bg-dark text-decoration-none"><?php echo htmlspecialchars($category['name']); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($blog['featured_image'] != 'default_blog.jpg'): ?>
                            <img src="../assets/images/blogs/<?php echo $blog['featured_image']; ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                        </div>
                        
                        <!-- Edit and Delete buttons - Only visible to the blog author -->
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $blog['user_id']): ?>
                            <div class="mt-4 d-flex gap-2">
                                <a href="edit.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Blog
                                </a>
                                <a href="delete.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this blog?');">
                                    <i class="fas fa-trash"></i> Delete Blog
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h4>Comments (<?php echo count($comments); ?>)</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($comment_error): ?>
                            <div class="alert alert-danger"><?php echo $comment_error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($comment_success): ?>
                            <div class="alert alert-success"><?php echo $comment_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="" class="mb-4">
                                <div class="mb-3">
                                    <label for="comment_content" class="form-label">Add a Comment</label>
                                    <textarea class="form-control" id="comment_content" name="comment_content" rows="3" required></textarea>
                                </div>
                                <button type="submit" name="comment" class="btn btn-dark">Post Comment</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info mb-4">
                                Please <a href="../auth/login.php">login</a> to leave a comment.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($comments)): ?>
                            <div class="text-center py-5">
                                <p class="text-muted">No comments yet. Be the first to comment!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-subtitle mb-2 text-muted">
                                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($comment['username']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php
                                                if (!empty($comment['created_at'])) {
                                                    echo date('F j, Y', strtotime($comment['created_at']));
                                                } else {
                                                    echo 'Recently';
                                                }
                                                ?>
                                            </small>
                                        </div>
                                        <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar with related information could go here -->
            <div class="col-md-4">
                <!-- Author information, related blogs, etc. -->
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
