<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: " . $base_url . "auth/login.php");
    exit();
}

// For edit/delete operations, check if user is the author
function check_blog_author($conn, $blog_id, $user_id) {
    $sql = "SELECT user_id FROM blogs WHERE blog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $blog = $result->fetch_assoc();
        if ($blog['user_id'] == $user_id) {
            return true;
        }
    }
    
    return false;
}
?>
