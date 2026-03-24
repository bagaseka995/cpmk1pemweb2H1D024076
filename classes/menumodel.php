<?php
require_once __DIR__ . '/basemodel.php';

class MenuModel extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table = 'menu_items';
    }

    public function getPaginatedMenu($limit, $offset) {
        $stmt = $this->db->prepare("
            SELECT m.*, c.name as category_name 
            FROM {$this->table} m 
            LEFT JOIN categories c ON m.category_id = c.id 
            ORDER BY m.id DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategories() {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY display_order ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createMenu($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description, price, category_id, image, is_available, is_spicy) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'], $data['description'], $data['price'],
            $data['category_id'], $data['image'], $data['is_available'], $data['is_spicy']
        ]);
    }

    public function getTotalCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return false; 
            }
            die("Error Database: " . $e->getMessage());
        }
    }

    public function getPublicMenus() {
        $stmt = $this->db->prepare("
            SELECT m.*, c.name as category_name 
            FROM {$this->table} m 
            JOIN categories c ON m.category_id = c.id 
            WHERE m.is_available = 1 
            ORDER BY c.display_order ASC, m.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateMenu($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET name = ?, description = ?, price = ?, category_id = ?, image = ?, is_available = ?, is_spicy = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'], $data['description'], $data['price'],
            $data['category_id'], $data['image'], $data['is_available'], $data['is_spicy'], $id
        ]);
    }

    public function getTopRecommendations() {
        $stmt = $this->db->prepare("
            SELECT m.*, c.name as category_name, COUNT(od.menu_id) as total_sold
            FROM menu_items m
            JOIN order_details od ON m.id = od.menu_id
            JOIN categories c ON m.category_id = c.id
            WHERE m.is_available = 1
            GROUP BY m.id
            ORDER BY total_sold DESC
            LIMIT 3
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>