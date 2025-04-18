<?php
// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to get user details
function get_user_details($conn, $user_id) {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get blog posts
function get_blog_posts($conn, $limit = 10, $offset = 0, $user_id = null) {
    $sql = "SELECT b.*, u.username FROM blogs b 
            JOIN users u ON b.user_id = u.user_id 
            WHERE b.status = 'published'";
    
    if ($user_id) {
        $sql .= " AND b.user_id = $user_id";
    }
    
    $sql .= " ORDER BY b.created_at DESC LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    $posts = [];
    
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    return $posts;
}
?>
