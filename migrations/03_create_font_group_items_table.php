<?php

require_once "includes/db.php";

// SQL to create the table
$sql = "CREATE TABLE font_group_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    font_group_id INT NOT NULL,
    font_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (font_group_id) REFERENCES font_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (font_id) REFERENCES fonts(id) ON DELETE CASCADE)";

// Execute the query
if ($conn->query($sql) !== TRUE) {
    echo "Error creating table: " . $conn->error . "\n";
}