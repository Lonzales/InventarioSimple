<?php
// Función para generar conexión con la base de datos
function crearConexion() {
    //Datos de la BD
    $servidor = "localhost";
    $usuario = "root";
    $password = ""; // <- Contraseña en caso de ser necesaria
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
        mysqli_stmt_close($stmt);

        return $data;
    }

    return null;
}

function ejecutarSqlEliminar($conexion, $sql, $value) {
    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $value);
        mysqli_stmt_execute($stmt);
        $filasAfectadas = mysqli_affected_rows($conexion);
        mysqli_stmt_close($stmt);

        return $filasAfectadas > 0 ?
            ['success' => true, 'message' => 'Eliminado con exito']
            : ['success' => false, 'message' => 'Dato no encontrado'];
    }

    return ['success' => false, 'message' => 'Error al preparar consulta'];
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

        return empty($data) ? ['success' => false, 'message' => "There's no data available"] : ['success' => true, 'message' => 'Data fetched succesfully', 'data' => $data];
    }

    return ['success' => false, 'message' => 'Database preparation error'];
}

function consultarTodoTags($conexion) {
    if ($conexion instanceof mysqli) {
        $sql = 'SELECT * FROM tags';
        $data = ejecutarSql($conexion, $sql, null) ?? []; 
        
        return empty($data) ? ['success' => false, 'message' => 'No data available'] : ['success' => true, 'message' => 'Tags fetched succesfully', 'data' => $data];
    }
    
    return ['success' => false, 'message' => 'Database preparation error'];
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

function agregarItem($conexion, $itemName, $description, $price, $tags) {
    if ($conexion instanceof mysqli) {
        $sql = 'INSERT INTO items (itemName, description, price) VALUES (?, ?, ?)';
        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssd', $itemName, $description, $price);
            mysqli_stmt_execute($stmt);
            $itemId = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

            if ($tags !== null) {
                $sqlTag = 'INSERT INTO tags_items (item_id, tag_id) VALUES (?, ?)';
                $stmtTag = mysqli_prepare($conexion, $sqlTag);
                foreach ($tags as $tagId) {
                    if ($stmtTag) {
                        mysqli_stmt_bind_param($stmtTag, 'ii', $itemId, $tagId);
                        mysqli_stmt_execute($stmtTag);
                    }
                }
                mysqli_stmt_close($stmtTag);
            }

            return ['success' => true, 'message' => 'Item added successfully'];
        }
    }

    return ['success' => false, 'message' => 'Error adding item'];
}

function eliminarItem($conexion, $id) {
    if ($conexion instanceof mysqli) {
        $sql = 'DELETE FROM items
            WHERE id = ?';
    
        return ejecutarSqlEliminar($conexion, $sql, $id);
    }
}
?>