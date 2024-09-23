<?php

require_once 'models/Font.php';

class FontController
{
    public Font $font;

    public function __construct()
    {
        $this->font = new Font();
    }

    /**
     * Display all fonts
     * @return void
     */
    public function index(): void
    {
        $fonts = $this->font->getAll();

        if (!empty($fonts)) {
            http_response_code(200);
            echo json_encode($fonts);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'No fonts found']);
        }
    }

    /**
     * Store font
     * @return void
     */
    public function store(): void
    {
        if (isset($_FILES['font'])) {
            $font = $_FILES['font'];

            $ext  = pathinfo($font['name'], PATHINFO_EXTENSION);

            if ($ext !== 'ttf') {
                echo json_encode(['error' => 'Invalid file format. Only TTF font are allowed']);
                exit;
            }

            $dir  = 'storage/fonts/';

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $path = $dir . basename($font['name']);

            if (move_uploaded_file($font['tmp_name'], $path)) {
                $this->font->name        = $font['name'];
                $this->font->file_path   = $path;
                $this->font->uploaded_at = date('Y-m-d H:i:s');
                $this->font->save();

                http_response_code(201);
                echo json_encode(['message' => 'Font uploaded successfully', 'data' => [
                    'id'          => $this->font->id,
                    'name'        => $this->font->name,
                    'file_path'   => $this->font->file_path,
                    'uploaded_at' => $this->font->uploaded_at
                ] ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Error uploading font']);
            }
        }
    }

    public function destroy($id): void
    {
        if ($this->font->delete($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Font deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Error deleting font']);
        }
    }
}