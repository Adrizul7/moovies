<?php
header('Content-Type: application/json');
require_once __DIR__ . '/util/conec.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    $conexionObjeto = new ConexionBD();
    $db = $conexionObjeto->getConexion();
    $db->beginTransaction();

    // 1. Actualizar datos básicos en 'movies'
    $sql = "UPDATE movies SET title=?, description=?, summary=?, image=?, trailer=? WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(
        $data['title'], $data['description'], $data['summary'], 
        $data['image'], $data['trailer'], $data['id']
    ));

    // 2. Actualizar Director en 'movie_cast' (Borrar el viejo de esa peli y poner el nuevo)
    $db->prepare("DELETE FROM movie_cast WHERE movie_id = ? AND role_id = 2")->execute(array($data['id']));
    $db->prepare("INSERT INTO movie_cast (movie_id, person_id, role_id) VALUES (?, ?, 2)")
       ->execute(array($data['id'], $data['director_id']));

    // 3. Actualizar Género en 'movies_genres'
    $db->prepare("DELETE FROM movies_genres WHERE movie_id = ?")->execute(array($data['id']));
    $db->prepare("INSERT INTO movies_genres (movie_id, genre_id) VALUES (?, ?)")
       ->execute(array($data['id'], $data['genre_id']));

$db->commit();
    // ANTES: echo json_encode(array("status" => "success"));
    echo json_encode(array("success" => true, "message" => "Película actualizada correctamente."));

} catch (Exception $e) {
    if(isset($db)) $db->rollBack();
    http_response_code(500);
    // ANTES: echo json_encode(array("status" => "error", "message" => $e->getMessage()));
    echo json_encode(array("success" => false, "error" => $e->getMessage()));
}
exit;  