<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/util/conec.php';
header('Content-Type: application/json');

try {
    $conexionClase = new ConexionBD();
    $db = $conexionClase->getConexion();

    // 1. INTENTAR LEER COMO JSON (Para Fetch API)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // 2. SI NO ES JSON, INTENTAR LEER COMO FORMULARIO ($_POST)
    if (!$data) {
        $data = $_POST;
    }

    // Si después de ambos intentos no hay nada, lanzamos error
    if (empty($data)) throw new Exception("No se recibieron datos en el servidor.");

    $id          = $data['id'] ?? null;
    $username    = $data['username'] ?? '';
    $name        = $data['name'] ?? '';
    $email       = $data['email'] ?? '';
    $milk        = $data['milk_type'] ?? '';
    $passwordRaw = $data['password'] ?? ''; 

    if (!$id) throw new Exception("Falta el ID del usuario.");

    // --- PROCESO DE ENCRIPTACIÓN Y ACTUALIZACIÓN ---
    if (!empty($passwordRaw)) {
        // Generamos el hash seguro
        $passwordHash = password_hash($passwordRaw, PASSWORD_BCRYPT);
        
        $sql = "UPDATE users SET username = ?, name = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($username, $name, $email, $passwordHash, $id));
    } else {
        // Si no hay password nueva, NO tocamos la columna password
        $sql = "UPDATE users SET username = ?, name = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($username, $name, $email, $id));
    }

    // --- ACTUALIZAR RELACIÓN DE LECHE ---
    $sqlMilk = "SELECT id FROM milks WHERE type = ?";
    $stmtM = $db->prepare($sqlMilk);
    $stmtM->execute(array($milk));
    $milkId = $stmtM->fetchColumn();

    if ($milkId) {
        $sqlCheck = "SELECT COUNT(*) FROM users_milks WHERE user_id = ?";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute(array($id));
        
        if ($stmtCheck->fetchColumn() > 0) {
            $sqlUpdMilk = "UPDATE users_milks SET milk_id = ? WHERE user_id = ?";
            $stmtUM = $db->prepare($sqlUpdMilk);
            $stmtUM->execute(array($milkId, $id));
        } else {
            $sqlInsMilk = "INSERT INTO users_milks (user_id, milk_id) VALUES (?, ?)";
            $stmtUM = $db->prepare($sqlInsMilk);
            $stmtUM->execute(array($id, $milkId));
        }
    }

    echo json_encode(array("success" => true, "message" => "Perfil actualizado con éxito"));

} catch (PDOException $e) {
    $msg = ($e->getCode() == 23000) ? "El nombre de usuario o email ya están en uso." : $e->getMessage();
    echo json_encode(array("success" => false, "error" => "Error DB: " . $msg));
} catch (Exception $e) {
    echo json_encode(array("success" => false, "error" => $e->getMessage()));
}