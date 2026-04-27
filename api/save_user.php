<?php
// Reportar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 0); // Lo ponemos en 0 para que no rompa el JSON en caso de avisos menores

require_once __DIR__ . '/util/conec.php';
header('Content-Type: application/json');

try {
    $conexionClase = new ConexionBD();
    $db = $conexionClase->getConexion();

    // Recibir datos del FormData del HTML
    // Usamos isset para evitar errores si algún campo falta
    $username = $_POST['username'] ?? '';
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $passwordRaw = $_POST['password'] ?? ''; 
    $milk     = $_POST['milk_type'] ?? 'Entera';

    if (empty($username) || empty($passwordRaw)) {
        throw new Exception("Usuario y contraseña son obligatorios.");
    }

    // --- ENCRIPTACIÓN AUTOMÁTICA ---
    // Esta línea transforma "mi_contra123" en un hash seguro
    $passwordHash = password_hash($passwordRaw, PASSWORD_BCRYPT);

    // 1. Insertar el usuario usando la contraseña encriptada
    $sql = "INSERT INTO users (username, name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($username, $name, $email, $passwordHash));
    
    // Obtener el ID del usuario recién creado
    $userId = $db->lastInsertId();

    // 2. Relacionar con la leche favorita
    $sqlMilk = "SELECT id FROM milks WHERE type = ?";
    $stmtM = $db->prepare($sqlMilk);
    $stmtM->execute(array($milk));
    $milkId = $stmtM->fetchColumn();

    if ($milkId) {
        $sqlRel = "INSERT INTO users_milks (user_id, milk_id) VALUES (?, ?)";
        $stmtR = $db->prepare($sqlRel);
        $stmtR->execute(array($userId, $milkId));
    }

    echo json_encode(array("success" => true));

} catch (PDOException $e) {
    // Error 23000 suele ser por entrada duplicada (Unique key)
    $msg = ($e->getCode() == 23000) ? "El usuario o email ya existen." : $e->getMessage();
    echo json_encode(array("success" => false, "error" => "Error DB: " . $msg));
} catch (Exception $e) {
    echo json_encode(array("success" => false, "error" => $e->getMessage()));
}