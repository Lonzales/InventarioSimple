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

// Automatización de la ejecución de querys de consulta
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

// Función complementaria para ejecutar consultas de eliminación
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

// Función para consultar todas las tags
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

// Función para agregar items
function agregarItem($conexion, $itemName, $description, $price, $tags) {
    if ($conexion instanceof mysqli) {
        $sql = 'INSERT INTO items (itemName, description, price) VALUES (?, ?, ?)';
        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssd', $itemName, $description, $price);
            mysqli_stmt_execute($stmt);
            $itemId = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

            if ($tags !== null) // Si tiene tags, son agregadas a la tabla intermedia del item en cuestion
                agregarTagsaItem($conexion, $itemId, $tags);

            return ['success' => true, 'message' => 'Item added successfully'];
        }
    }

    return ['success' => false, 'message' => 'Error adding item'];
}

// Función para actualizar items
function actualizarItem($conexion, $itemId, $itemName, $description, $price, $tags) {
    if ($conexion instanceof mysqli) {
        $sql = 'UPDATE items SET itemName = ?, description = ?, price = ? WHERE id = ?';
        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssdi', $itemName, $description, $price, $itemId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            $sqlDeleteTags = 'DELETE FROM tags_items WHERE item_id = ?'; // Se eliminan las tags existentes para el item antes de agregar las nuevas
            $stmtDeleteTags = mysqli_prepare($conexion, $sqlDeleteTags);
            if ($stmtDeleteTags) {
                mysqli_stmt_bind_param($stmtDeleteTags, 'i', $itemId);
                mysqli_stmt_execute($stmtDeleteTags);
                mysqli_stmt_close($stmtDeleteTags);
            }
            
            if ($tags !== null) // Si tiene tags, son agregadas a la tabla intermedia del item en cuestion
                agregarTagsaItem($conexion, $itemId, $tags);
        }

        return ['success' => true, 'message' => 'Item updated successfully'];
    }
    return ['success' => false, 'message' => 'Error updating item'];
}

// Función para eliminar un item
function eliminarItem($conexion, $id) {
    if ($conexion instanceof mysqli) {
        $sql = 'DELETE FROM items
            WHERE id = ?';
    
        return ejecutarSqlEliminar($conexion, $sql, $id);
    }
}

// Función intermedia para agregar tags a un item
function agregarTagsaItem($conexion, $itemId, $tags) {
    if ($conexion instanceof mysqli) {
        $sqlTag = 'INSERT INTO tags_items (item_id, tag_id) VALUES (?, ?)';
        $stmtTag = mysqli_prepare($conexion, $sqlTag);
        if ($stmtTag) {
            foreach ($tags as $tagId) { // Se agregan las tags a la tabla intermedia
                mysqli_stmt_bind_param($stmtTag, 'ii', $itemId, $tagId);
                mysqli_stmt_execute($stmtTag);
            }
        }
        mysqli_stmt_close($stmtTag);
    }
}
?>