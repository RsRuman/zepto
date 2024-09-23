<?php
require_once 'includes/db.php';

class Font {
    public $id;
    public $name;
    public $file_path;
    public $uploaded_at;

    public function getAll(): array
    {
        global $conn;

        $fonts = [];
        $query = "SELECT * FROM fonts";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $fonts[] = $row;
            }
        }

        return $fonts;
    }

    public function save(): bool
    {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO fonts (name, file_path, uploaded_at) VALUES (?, ?, ?)");

        if ($stmt === FALSE) {
            return false;
        }

        $stmt->bind_param(
            "sss",
            $this->name,
            $this->file_path,
            $this->uploaded_at
        );

        $result = $stmt->execute();

        if ($result) {
            $this->id = $conn->insert_id;
        }

        $stmt->close();

        return $result;
    }

    public function delete(int $id): bool
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM fonts WHERE id = ?");
        if ($stmt === FALSE) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}