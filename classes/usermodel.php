<?php
require_once __DIR__ . '/basemodel.php';

class UserModel extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table = 'users';
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, username, role, created_at FROM {$this->table} ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, role FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($username, $password, $role) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (username, password, role) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashed, $role]);
    }

    public function updateUser($id, $username, $role, $password = null) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE {$this->table} SET username = ?, password = ?, role = ? WHERE id = ?");
            return $stmt->execute([$username, $hashed, $role, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET username = ?, role = ? WHERE id = ?");
            return $stmt->execute([$username, $role, $id]);
        }
    }

    public function delete($id) {
        if ($id == $_SESSION['user_id']) return false;
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}