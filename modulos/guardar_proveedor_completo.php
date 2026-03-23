<?php
include '../includes/conexion.php';

// --- CASO 1: CAMBIAR ESTADO (Activar/Desactivar) ---
if (isset($_GET['id_estado'])) {
    $id = intval($_GET['id_estado']);
    $nuevo_estado = intval($_GET['set']); 

    try {
        $sql = "UPDATE proveedores SET estado = ? WHERE id_proveedor = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevo_estado, $id]);

        $msg = ($nuevo_estado == 1) ? 'activado' : 'desactivado';
        header("Location: ../gestion_proveedores.php?prov=" . $msg);
        exit();

    } catch (PDOException $e) {
        header("Location: ../gestion_proveedores.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// --- CASO 2: GUARDAR O EDITAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    // Capturamos el ID oculto del modal para saber si es EDITAR o NUEVO
    $id = !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null;
    
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $categorias = isset($_POST['categorias']) ? $_POST['categorias'] : [];

    try {
        $pdo->beginTransaction();

        if ($id) {
            // --- LÓGICA DE EDICIÓN (UPDATE) ---
            $sql = "UPDATE proveedores SET rut = ?, nombre = ?, telefono = ? WHERE id_proveedor = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rut, $nombre, $telefono, $id]);

            // Sincronizar categorías: Borramos las anteriores y re-insertamos las nuevas
            $stmt_del = $pdo->prepare("DELETE FROM proveedor_especialidad WHERE id_proveedor = ?");
            $stmt_del->execute([$id]);
            
            if (!empty($categorias)) {
                $stmt_rel = $pdo->prepare("INSERT INTO proveedor_especialidad (id_proveedor, id_categoria) VALUES (?, ?)");
                foreach ($categorias as $cat_id) {
                    $stmt_rel->execute([$id, $cat_id]);
                }
            }
            $res = "editado";

        } else {
            // --- LÓGICA DE NUEVO (INSERT) ---
            $stmt = $pdo->prepare("INSERT INTO proveedores (rut, nombre, telefono, estado) VALUES (?, ?, ?, 1)");
            $stmt->execute([$rut, $nombre, $telefono]);
            $id_nuevo = $pdo->lastInsertId();

            if (!empty($categorias)) {
                $stmt_rel = $pdo->prepare("INSERT INTO proveedor_especialidad (id_proveedor, id_categoria) VALUES (?, ?)");
                foreach ($categorias as $cat_id) {
                    $stmt_rel->execute([$id_nuevo, $cat_id]);
                }
            }
            $res = "ok";
        }

        $pdo->commit();
        header("Location: ../gestion_proveedores.php?prov=" . $res);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: ../gestion_proveedores.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} // <--- Aquí cerraba el IF de POST

// Si llega aquí sin entrar en los casos anteriores, redirigir por seguridad
header("Location: ../gestion_proveedores.php");
exit();
?>