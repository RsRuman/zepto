<?php

require_once 'includes/db.php';

global $conn;

// Create the migrations table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS migrations (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating migrations table: " . $conn->error . "\n");
}

// Get the list of already created migrations
$migrations = $conn->query("SELECT migration FROM migrations");

if ($migrations === FALSE) {
    die("Error fetching applied migrations: " . $conn->error . "\n");
}
$appliedMigrations = [];

while ($row = $migrations->fetch_assoc()) {
    $appliedMigrations[] = $row['migration'];
}

$migrationFiles = glob(__DIR__ . '/migrations/*.php');

foreach ($migrationFiles as $file) {
    $migration = basename($file, '.php');

    if (!in_array($migration, $appliedMigrations)) {
        try {
            // Run migration
            require $file;
            $stmt = $conn->prepare("INSERT INTO migrations (migration) VALUES (?)");
            if ($stmt === FALSE) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("s", $migration);
            $stmt->execute();

            echo "Created migration: $migration\n";
        } catch (Exception $e) {
            echo "Failed to apply migration: $migration. Error: " . $e->getMessage() . "\n";
        }
    }
}

$conn->close();
