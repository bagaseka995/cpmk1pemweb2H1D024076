<?php
require_once __DIR__ . '/basemodel.php';

class OrderModel extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table = 'orders';
    }

    public function getTables() {
        $stmt = $this->db->prepare("SELECT * FROM tables ORDER BY table_number ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMenus() {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createOrder($table_id, $waiter_id, $notes, $items) {
        try {
            $this->db->beginTransaction();

            $total = 0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['qty'];
            }

            $stmt = $this->db->prepare("INSERT INTO orders (table_id, waiter_id, status, total, notes) VALUES (?, ?, 'pending', ?, ?)");
            $stmt->execute([$table_id, $waiter_id, $total, $notes]);
            
            $order_id = $this->db->lastInsertId();

            $stmtDetail = $this->db->prepare("INSERT INTO order_details (order_id, menu_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtDetail->execute([$order_id, $item['menu_id'], $item['qty'], $item['price']]);
            }

            $stmtTable = $this->db->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
            $stmtTable->execute([$table_id]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Gagal membuat pesanan: " . $e->getMessage());
            return false;
        }
    }

    public function getActiveOrders() {
        $stmt = $this->db->prepare("
            SELECT o.*, t.table_number, u.username as waiter_name 
            FROM orders o 
            JOIN tables t ON o.table_id = t.id 
            JOIN users u ON o.waiter_id = u.id 
            WHERE o.status != 'paid' 
            ORDER BY o.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderItems($order_id) {
        $stmt = $this->db->prepare("
            SELECT od.*, m.name as menu_name 
            FROM order_details od 
            JOIN menu_items m ON od.menu_id = m.id 
            WHERE od.order_id = ?
        ");
        $stmt->execute([$order_id]);
        return $stmt->fetchAll();
    }

    public function updateStatus($order_id, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $order_id]);
    }

    public function getOrderById($id) {
        $stmt = $this->db->prepare("
            SELECT o.*, t.table_number, u.username as kasir_name 
            FROM orders o 
            JOIN tables t ON o.table_id = t.id 
            JOIN users u ON o.waiter_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function processPayment($old_order_id, $selected_detail_ids, $kasir_id) {
        try {
            $this->db->beginTransaction();

            $oldOrder = $this->getOrderById($old_order_id);

            $stmt = $this->db->prepare("INSERT INTO orders (table_id, waiter_id, status, notes) VALUES (?, ?, 'paid', ?)");
            $stmt->execute([$oldOrder['table_id'], $kasir_id, "Pembayaran dari Order #" . $old_order_id]);
            $new_order_id = $this->db->lastInsertId();

            $total_paid = 0;

            $stmtMove = $this->db->prepare("UPDATE order_details SET order_id = ? WHERE id = ?");
            $stmtPrice = $this->db->prepare("SELECT quantity, unit_price FROM order_details WHERE id = ?");

            foreach($selected_detail_ids as $detail_id) {
                $stmtPrice->execute([$detail_id]);
                $item = $stmtPrice->fetch();
                $total_paid += ($item['quantity'] * $item['unit_price']);

                $stmtMove->execute([$new_order_id, $detail_id]);
            }

            $this->db->prepare("UPDATE orders SET total = ? WHERE id = ?")->execute([$total_paid, $new_order_id]);

            $stmtCheckLeft = $this->db->prepare("SELECT SUM(quantity * unit_price) as sisa_total, COUNT(id) as sisa_item FROM order_details WHERE order_id = ?");
            $stmtCheckLeft->execute([$old_order_id]);
            $sisa = $stmtCheckLeft->fetch();

            if ($sisa['sisa_item'] == 0) {
                $this->db->prepare("UPDATE orders SET status = 'paid', total = 0 WHERE id = ?")->execute([$old_order_id]);
                $this->db->prepare("UPDATE tables SET status = 'empty' WHERE id = ?")->execute([$oldOrder['table_id']]);
            } else {
                $this->db->prepare("UPDATE orders SET total = ? WHERE id = ?")->execute([$sisa['sisa_total'], $old_order_id]);
            }

            $this->db->commit();
            return $new_order_id; 

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>