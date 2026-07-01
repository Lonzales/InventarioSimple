<?php
require_once 'DBconexion.php'; // Uso de funciones de DBconexion

// Función para simplificar la consulta de items
function consultarItemsBD() {
    $conexion = crearConexion();
    $resultado = consultarTodo($conexion);
    cerrarConexion($conexion);
    return $resultado;
}

// Función para consultar Tags
function consultarTagsBD() {
    $conexion = crearConexion();
    $resultado = consultarTodoTags($conexion);
    cerrarConexion($conexion);
    return $resultado;
}

function agregarItemBD($itemName, $description, $price, $tags) {
    $conexion = crearConexion();
    $resultado = agregarItem($conexion, $itemName, $description, $price, $tags);
    cerrarConexion($conexion);
    return $resultado;
}

function eliminarItemBD($itemId) {
    $conexion = crearConexion();
    $resultado = eliminarItem($conexion, $itemId);
    cerrarConexion($conexion);
    return $resultado;
}

// Evaluación de la función requerida
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    header('Content-Type: application/json');
    
    switch ($accion) {
        case 'consultarItems':
            try {
                echo json_encode(consultarItemsBD());
                exit;
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'There was an error while conecting to the data base.']);
                exit;
            }
        case 'consultarTags':
            try {
                echo json_encode(consultarTagsBD());
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'There was an error while conecting to the data base.']);
                exit;
            }
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    header('Content-Type: application/json');

    switch ($accion) {
        case 'agregarItem':
            try {
                $itemId = $_POST['itemId'] ?? '';
                $itemName = $_POST['itemName'] ?? '';
                $itemDescription = $_POST['itemDesc'] ?? '';
                $itemPrice = $_POST['itemPrice'] ?? '';
                $tags = $_POST['tags'] ?? null; // Asegúrate de que los tags se envíen como un array

                if (empty($itemName) || empty($itemDescription) || empty($itemPrice)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
                    exit;
                }
                echo json_encode(agregarItemBD($itemName, $itemDescription, $itemPrice, $tags));
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'There was an error while connecting to the database.']);
                exit;
            }
        case 'eliminarItem':
            try {
                $itemId = $_POST['itemId'] ?? '';

                if (empty($itemId)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Missing item ID.']);
                    exit;
                }
                echo json_encode(eliminarItemBD($itemId));
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'There was an error while connecting to the database.']);
                exit;
            }
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}
?>