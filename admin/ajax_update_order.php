<?php
// /admin/ajax_update_order.php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order']) || !is_array($input['order'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE dashboard_links SET display_order = ? WHERE id = ?");

    foreach ($input['order'] as $item) {
        $stmt->execute([(int)$item['order'], (int)$item['id']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Order updated successfully.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}