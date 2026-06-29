<?php
// Función para generar conexión con la base de datos
function crearConexion() {
    //Datos de la BD
    $servidor = "localhost";
    $usuario = "root";
    $password = "1234"; // <- Contraseña en caso de ser necesaria
    $basededatos = "inventariosimple";

    $conexion = mysqli_connect($servidor, $usuario, $password, $basededatos);
    if (!$conexion) {
        die("Conexión fallida: " . mysqli_connect_error());
    }

    return $conexion;
}

// Cierre de conexión
function cerrarConexion($conexion) {
    if ($conexion instanceof mysqli) {
        mysqli_close($conexion);
    }
}

// Automatización de la ejecución de querys
function ejecutarSql($conexion, $sql, $value) {
    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {
        if ($value !== null) 
            mysqli_stmt_bind_param($stmt, 'i', $value);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        $data = [];
        while ($row = mysqli_fetch_assoc($resultado))
            $data[] = $row;

        return $data;
    }

    return null;
}

// Consulta completa de items a la BD
function consultarTodo($conexion) {
    if ($conexion instanceof mysqli) {
        $sql = 'SELECT * FROM items';
        $data = [];

        $resultado = ejecutarSql($conexion, $sql, null);

        if ($resultado !== null)
            foreach ($resultado as $row) {
                $data[] = [
                    'id' => $row['id'],
                    'itemName' => $row['itemName'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'tags' => consultarTags($conexion, $row['id']) ?? ''
                ];
            }

        return $data;
    }

    return null;
}

// Función para consultar las etiquetas
function consultarTags($conexion, $id) {
    if ($conexion instanceof mysqli) {
        $sql = 'SELECT ti.tag_id AS id, t.tag, t.color 
            FROM tags_items AS ti
            INNER JOIN tags AS t
            ON ti.tag_id = t.id
            WHERE ti.item_id = ?';
        return ejecutarSql($conexion, $sql, $id);
    }

    return null;
}
?>