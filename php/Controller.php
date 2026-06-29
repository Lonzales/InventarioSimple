<?php
require_once 'DBconexion.php'; // Uso de funciones de DBconexion

// Función para simplificar la consulta
function consultar() {
    $conexion = crearConexion();
    $resultado = consultarTodo($conexion);
    cerrarConexion($conexion);
    return $resultado;
}

// Evaluación de la función requerida
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    header('Content-Type: application/json');

    if ($accion === 'consultar') {
        try {
            $resultado = consultar();
            if ($resultado !== null) {
                echo json_encode(['success' => true, 'data' => $resultado]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => "There's no data available."]);
                exit;
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'There was an error while conecting to the data base.']);
            exit;
        }
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}
?>