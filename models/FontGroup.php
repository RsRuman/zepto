<?php

class FontGroup
{
    public $id;
    public $name;
    public $created_at;

    public function getAll(): array
    {
        global $conn;

        $fontGroups = [];

        // SQL query to get all font groups along with their font group items
        $query = "
    SELECT 
        fg.id AS font_group_id, 
        fg.name AS font_group_name, 
        fgi.font_id, 
        fgi.created_at AS group_item_created_at, 
        f.name AS font_name, 
        f.file_path AS font_file_path
    FROM font_groups fg
    LEFT JOIN font_group_items fgi ON fg.id = fgi.font_group_id
    LEFT JOIN fonts f ON fgi.font_id = f.id
    ORDER BY fg.id, fgi.created_at";

        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
                // Check if the group already exists in the array
                if (!isset($fontGroups[$row['font_group_id']])) {
                    $fontGroups[$row['font_group_id']] = [
                        'id'         => $row['font_group_id'],
                        'group_name' => $row['font_group_name'],
                        'items'      => []
                    ];
                }

                // Add the font item to the current group
                $fontGroups[$row['font_group_id']]['items'][] = [
                    'font_id'        => $row['font_id'],
                    'font_name'      => $row['font_name'],
                    'font_file_path' => $row['font_file_path'],
                    'created_at'     => $row['group_item_created_at']
                ];
            }
        }

        return array_values($fontGroups);
    }

    public function save(): bool
    {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO font_groups (name, created_at) VALUES (?, ?)");

        if ($stmt === FALSE) {
            return false;
        }

        $stmt->bind_param(
            "ss",
            $this->name,
            $this->created_at
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

        $stmt = $conn->prepare("DELETE FROM font_groups WHERE id = ?");
        if ($stmt === FALSE) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}