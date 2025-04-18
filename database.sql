-- Create database if not exists
CREATE DATABASE IF NOT EXISTS bloghive;

-- Use the database
USE bloghive;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255) DEFAULT 'default_profile.jpg',
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blogs table
CREATE TABLE blogs (
    blog_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    featured_image VARCHAR(255) DEFAULT 'default_blog.jpg',
    status ENUM('draft', 'published') DEFAULT 'published',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Comments table
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(blog_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Blog-Category relationship table
CREATE TABLE blog_categories (
    blog_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (blog_id, category_id),
    FOREIGN KEY (blog_id) REFERENCES blogs(blog_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Technology', 'Blogs about technology, gadgets, and digital trends'),
('Travel', 'Travel experiences, tips, and destination guides'),
('Food', 'Recipes, restaurant reviews, and culinary experiences'),
('Lifestyle', 'Daily life, personal development, and wellness'),
('Business', 'Entrepreneurship, finance, and professional growth');

-- Insert a default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, bio) VALUES
('admin', 'admin@bloghive.com', '$2y$10$8KOO.VXOW3MN6LKB0VLOeecAQK7wCnSY0tU0.qgUvP8c5o5rHI.Hy', 'Admin User', 'BlogHive Administrator');

-- Insert some sample blog posts

-- Associate blogs with categories
INSERT INTO blog_categories (blog_id, category_id) VALUES
(1, 1), -- Welcome to BlogHive - Technology
(2, 4), -- How to Write Engaging Content - Lifestyle
(3, 5); -- The Future of Blogging - Business
