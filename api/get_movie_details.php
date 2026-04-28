<?php
header('Content-Type: application/json');
require_once __DIR__ . '/util/conec.php';

try {
    $conexionObj = new ConexionBD();
    $db = $conexionObj->getConexion();
    $id = $_GET['id'];

    $sql = "SELECT m.*, 
                mc_dir.person_id  AS director_id,
                mc_act.person_id  AS actor_id,
                mc_wri.person_id  AS writer_id,
                mc_com.person_id  AS composer_id,
                mg.genre_id
            FROM movies m
            LEFT JOIN movie_cast mc_dir ON m.id = mc_dir.movie_id AND mc_dir.role_id = 2
            LEFT JOIN movie_cast mc_act ON m.id = mc_act.movie_id AND mc_act.role_id = 1
            LEFT JOIN movie_cast mc_wri ON m.id = mc_wri.movie_id AND mc_wri.role_id = 3
            LEFT JOIN movie_cast mc_com ON m.id = mc_com.movie_id AND mc_com.role_id = 4
            LEFT JOIN movies_genres mg  ON m.id = mg.movie_id
            WHERE m.id = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($movie);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
exit;