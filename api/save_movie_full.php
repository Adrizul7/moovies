<?php
header('Content-Type: application/json');
ini_set('display_errors', 0); // Desactivamos errores visuales para no romper el JSON
error_reporting(E_ALL);

require_once __DIR__ . '/util/conec.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

try {
    if (!$data) {
        throw new Exception("No se recibieron datos válidos.");
    }

    $conexionObjeto = new ConexionBD();
    $db = $conexionObjeto->getConexion();
    $db->beginTransaction();

    $sqlMovie = "INSERT INTO movies (title, description, summary, image, trailer) VALUES (?, ?, ?, ?, ?)";
    $stmtMovie = $db->prepare($sqlMovie);
    $stmtMovie->execute(array(
        $data['title'] ?? null,
        $data['description'] ?? null,
        $data['summary'] ?? null,
        $data['image'] ?? null,
        $data['trailer'] ?? null
    ));

    $movieId = $db->lastInsertId();

    if (!empty($data['director_id'])) {
        $sqlCast = "INSERT INTO movie_cast (movie_id, person_id, role_id) VALUES (?, ?, ?)";
        $stmtCast = $db->prepare($sqlCast);
        $stmtCast->execute(array($movieId, $data['director_id'], 2)); 
    }

        if (!empty($data['actor_id'])) {
        $sqlCast = "INSERT INTO movie_cast (movie_id, person_id, role_id) VALUES (?, ?, ?)";
        $stmtCast = $db->prepare($sqlCast);
        $stmtCast->execute(array($movieId, $data['actor_id'], 1)); 
    }

        if (!empty($data['writer_id'])) {
        $sqlCast = "INSERT INTO movie_cast (movie_id, person_id, role_id) VALUES (?, ?, ?)";
        $stmtCast = $db->prepare($sqlCast);
        $stmtCast->execute(array($movieId, $data['writer_id'], 3)); 
    }

        if (!empty($data['composer_id'])) {
        $sqlCast = "INSERT INTO movie_cast (movie_id, person_id, role_id) VALUES (?, ?, ?)";
        $stmtCast = $db->prepare($sqlCast);
        $stmtCast->execute(array($movieId, $data['composer_id'], 4)); 
    }

    if (!empty($data['genre_id'])) {
        $sqlGenre = "INSERT INTO movies_genres (movie_id, genre_id) VALUES (?, ?)";
        $stmtGenre = $db->prepare($sqlGenre);
        $stmtGenre->execute(array($movieId, $data['genre_id']));
    }
    
    $db->commit();
    // Enviamos la respuesta y cortamos la ejecución inmediatamente
    echo json_encode(array("success" => true, "message" => "Película guardada correctamente"));
    exit; 

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(array("success" => false, "error" => $e->getMessage()));
    exit;
}