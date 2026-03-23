<?php
include '../includes/conexion.php';

// --- CASO 1: ELIMINAR ---
if (isset($_GET['delete_id'])) {
    // Convertimos a entero para limpiar el dato
    $id = intval($_GET['delete_id']); 
    
    try {
        $sql = "DELETE FROM categorias_mantencion WHERE id_categoria = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        // rowCount nos dice si realmente se borró una fila
        if ($stmt->rowCount() > 0) {
            header("Location: ../gestion_proveedores.php?cat=deleted");
        } else {
            // Si redirige aquí, es porque el ID que mandó el JS no existe en la tabla
            header("Location: ../gestion_proveedores.php?error=No se encontro la categoria con ID: " . $id);
        }
        exit();

    } catch (PDOException $e) {
        // Error de integridad (si la categoría tiene un proveedor asignado)
        header("Location: ../gestion_proveedores.php?error=No se puede eliminar: esta asignada a un proveedor.");
        exit();
    }
}

// --- CASO 2: GUARDAR ---
if (isset($_POST['nombre_categoria']) && !empty(trim($_POST['nombre_categoria']))) {
    $nombre = trim($_POST['nombre_categoria']);

    try {
        $sql = "INSERT INTO categorias_mantencion (nombre_categoria) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre]);

        header("Location: ../gestion_proveedores.php?cat=ok");
        exit();
    } catch (PDOException $e) {
        header("Location: ../gestion_proveedores.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// Si no hay acción, volvemos
header("Location: ../gestion_proveedores.php");
exit();
?>