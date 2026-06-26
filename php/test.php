<?php
    function alerta() {
        return "Mensaje de php";
    }

    function etiqueta() {
        return "<h2>Respuesta obtenida</h2>";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        $resultado = '';

        header('Content-Type: application/json');

        if ($accion === 'alerta') {
            $resultado = alerta();
            echo json_encode(['resultado' => $resultado, 'accion' => $accion]);
            exit;
        }

        if ($accion === 'etiqueta') {
            $resultado = etiqueta();
            echo json_encode(['resultado' => $resultado, 'accion' => $accion]);
            exit;
        }

        echo json_encode(['error' => 'Acción no válida']);
        exit;
    }
?>