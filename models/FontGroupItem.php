<?php

class FontGroupItem
{
    public $id;
    public $font_group_id;
    public $font_id;
    public $created_at;

    public function save(): bool
    {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO font_group_items (font_group_id, font_id, created_at) VALUES (?, ?, ?)");

        if ($stmt === FALSE) {
            return false;
        }

        $stmt->bind_param(
            "sss",
            $this->font_group_id,
            $this->font_id,
            $this->created_at
        );

        $result = $stmt->execute();

        if ($result) {
            $this->id = $conn->insert_id;
        }

        $stmt->close();

        return $result;
    }
}