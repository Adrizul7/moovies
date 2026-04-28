<?php
header('Content-Type: application/json');
require_once __DIR__ . '/util/conec.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['nombre']) || empty($data['movie_id']) || empty($data['role_id'])) {
    echo json_encode(["success" => false, "error" => "Faltan datos requeridos."]);
    exit;
}

if (intval($data['role_id']) === 2) {
    echo json_encode(["success" => false, "error" => "El director se asigna desde Gestión de Películas."]);
    exit;
}

try {
    $conexionObjeto = new ConexionBD();
    $db = $conexionObjeto->getConexion();
    $db->beginTransaction();

    // 1. Insertar persona nueva en people
    $stmt = $db->prepare("INSERT INTO people (name) VALUES (?)");
    $stmt->execute([trim($data['nombre'])]);
    $personId = $db->lastInsertId();

    // 2. Vincular a la película con su rol
    $stmt2 = $db->prepare("INSERT INTO movie_cast (movie_id, person_id, role_id) VALUES (?, ?, ?)");
    $stmt2->execute([$data['movie_id'], $personId, $data['role_id']]);

    $db->commit();
    echo json_encode(["success" => true, "message" => "Persona registrada y vinculada correctamente."]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
exit;