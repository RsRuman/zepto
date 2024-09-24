<?php

require_once 'models/FontGroup.php';
require_once 'models/FontGroupItem.php';

class FontGroupController
{
    public FontGroup $fontGroup;
    public FontGroupItem $fontGroupItem;

    public function __construct()
    {
        $this->fontGroup     = new FontGroup();
        $this->fontGroupItem = new FontGroupItem();
    }

    /**
     * List of font groups
     * @return void
     */
    public function index(): void
    {
        $fontGroups = $this->fontGroup->getAll();

        if (!empty($fontGroups)) {
            http_response_code(200);
            echo json_encode($fontGroups);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'No font groups found']);
        }
    }

    /**
     * Store font group
     * @return void
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate group name
        if (empty($data['groupName'])) {
            echo json_encode(['error' => 'Group name is required']);
            exit;
        }

        // Validate fonts
        if (empty($data['fonts']) || !is_array($data['fonts'])) {
            echo json_encode(['error' => 'Fonts array is required']);
            exit;
        }

        // Validate each font
        foreach ($data['fonts'] as $font) {
            if (empty($font['name'])) {
                echo json_encode(['error' => 'Font name is required']);
                exit;
            }
            if (empty($font['id'])) {
                echo json_encode(['error' => 'Font ID is required']);
                exit;
            }
        }

        $this->fontGroup->name       = $data['groupName'];
        $this->fontGroup->created_at = date('Y-m-d H:i:s');
        $this->fontGroup->save();

        foreach ($data['fonts'] as $font) {
            $this->fontGroupItem->font_group_id = $this->fontGroup->id;
            $this->fontGroupItem->font_id       = $font['id'];
            $this->fontGroupItem->created_at    = date('Y-m-d H:i:s');
            $this->fontGroupItem->save();
        }

        // Extract font names
        $fontNames       = array_column($data['fonts'], 'name');
        $fonts           = implode(',', $fontNames);

        http_response_code(201);
        echo json_encode(['message' => 'Font group created successfully', 'data' => [
            'id'          => $this->fontGroup->id,
            'name'        => $this->fontGroup->name,
            'fonts'       => $fonts,
            'count'       => count($fontNames),
            'created_at'  => $this->fontGroup->created_at
        ] ]);
    }

    /**
     * Delete font groups
     * @param $id
     * @return void
     */
    public function destroy($id): void
    {
        if ($this->fontGroup->delete($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Font group deleted successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Error deleting font']);
        }
    }
}