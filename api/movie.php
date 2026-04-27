<?php
header('Content-Type: application/json');
require_once __DIR__ . '/util/conec.php';

try {
    $conexionObj = new ConexionBD();
    $db = $conexionObj->getConexion();
    
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$id) {
        echo json_encode(["success" => false, "error" => "No ID provided"]);
        exit;
    }

    // 1. Obtener datos de la película
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movie) {
        echo json_encode(["success" => false, "error" => "Movie not found"]);
        exit;
    }

    // 2. Obtener comentarios (Asegúrate que la tabla se llame 'comments')
    $sql_comm = "SELECT * FROM comments WHERE movie_id = ? ORDER BY id DESC";
    $stmt_comm = $db->prepare($sql_comm);
    $stmt_comm->execute([$id]);
    $comments = $stmt_comm->fetchAll(PDO::FETCH_ASSOC);

    // 3. Respuesta unificada que el JS sí entiende
    echo json_encode([
        "success" => true,
        "movie" => $movie,
        "comments" => $comments
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}