<?php
header('Content-Type: application/json');
require_once __DIR__ . '/util/conec.php';

$role_id = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 2; // default director

try {
    $conexionObj = new ConexionBD();
    $db = $conexionObj->getConexion();

    $sql = "SELECT DISTINCT p.id, p.name 
            FROM people p
            INNER JOIN movie_cast mc ON p.id = mc.person_id
            WHERE mc.role_id = ?
            ORDER BY p.name ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([$role_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
exit;