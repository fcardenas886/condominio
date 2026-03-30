<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/conexion.php';

$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'crear':
        try {
            $pdo->beginTransaction(); // Iniciamos transacción

            // 1. Insertar el Arrendatario
            $sql_arr = "INSERT INTO arrendatarios (nombre, rut, telefono, correo, activo) VALUES (?, ?, ?, ?, 1)";
            $stmt_arr = $pdo->prepare($sql_arr);
            $stmt_arr->execute([
                $_POST['nombre'], 
                $_POST['rut'], 
                $_POST['telefono'], 
                $_POST['correo']
            ]);
            
            $id_arrendatario = $pdo->lastInsertId(); // Obtenemos el ID recién creado

            // 2. Verificar si se activó el Perfil Público
            if (isset($_POST['crear_perfil']) && $_POST['crear_perfil'] == '1') {
                $password = $_POST['password_usuario'];
                $email = $_POST['correo'];
                $nombre = $_POST['nombre'];

                // Insertar en la tabla usuarios vinculando con id_entidad_asociada
                $sql_usu = "INSERT INTO usuarios (nombre_completo, email, password_hash, rol, id_entidad_asociada, activo) 
                            VALUES (?, ?, ?, 'arrendatario', ?, 1)";
                $stmt_usu = $pdo->prepare($sql_usu);
                
                // Nota: Usamos el pass directo como en tu imagen, pero lo ideal es password_hash()
                $stmt_usu->execute([
                    $nombre, 
                    $email, 
                    $password, 
                    $id_arrendatario
                ]);
            }

            $pdo->commit(); // Si todo salió bien, guardamos cambios
            header("Location: ../mantencion_arrendatarios.php?success=1");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack(); // Si algo falla, deshacemos todo
            header("Location: ../mantencion_arrendatarios.php?error=db&msg=" . urlencode($e->getMessage()));
            exit();
        }
        break;

    case 'editar':
        try {
            $id_arrendatario = $_POST['id_arrendatario'] ?? '';
            $nombre = $_POST['nombre'];
            $rut = $_POST['rut'];
            $telefono = $_POST['telefono'];
            $correo = $_POST['correo'];
            $crear_perfil = isset($_POST['crear_perfil']) ? 1 : 0;
            $password = $_POST['password_usuario'] ?? '';

            if (empty($id_arrendatario)) die("Error: No se recibió el ID.");

            $pdo->beginTransaction();

            // 1. Actualizar datos básicos del Arrendatario
            $sql = "UPDATE arrendatarios SET nombre = ?, rut = ?, telefono = ?, correo = ? WHERE id_arrendatario = ?";
            $pdo->prepare($sql)->execute([$nombre, $rut, $telefono, $correo, $id_arrendatario]);

            // 2. Manejo del Perfil de Usuario
            // Revisamos si ya existe un usuario vinculado a este arrendatario
            $checkUser = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_entidad_asociada = ? AND rol = 'user'");
            $checkUser->execute([$id_arrendatario]);
            $usuarioExistente = $checkUser->fetch();

            if ($crear_perfil) {
                if ($usuarioExistente) {
                    // ESCENARIO A: Ya existe, actualizamos sus datos y aseguramos que esté activo
                    $sql_upd_usu = "UPDATE usuarios SET nombre_completo = ?, email = ?, activo = 1";
                    $params_usu = [$nombre, $correo];
                    
                    // Si el admin escribió una nueva contraseña, la actualizamos
                    if (!empty($password)) {
                        $sql_upd_usu .= ", password_hash = ?";
                        $params_usu[] = $password; 
                    }
                    
                    $sql_upd_usu .= " WHERE id_entidad_asociada = ? AND rol = 'user'";
                    $params_usu[] = $id_arrendatario;
                    
                    $pdo->prepare($sql_upd_usu)->execute($params_usu);
                } else {
                    // ESCENARIO B: No existía y el switch se activó, lo creamos
                    $sql_ins_usu = "INSERT INTO usuarios (nombre_completo, email, password_hash, rol, id_entidad_asociada, activo) 
                                    VALUES (?, ?, ?, 'arrendatario', ?, 1)";
                    $pdo->prepare($sql_ins_usu)->execute([$nombre, $correo, $password, $id_arrendatario]);
                }
            } else {
                // ESCENARIO C: El switch está apagado. Si existía usuario, lo desactivamos
                if ($usuarioExistente) {
                    $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id_entidad_asociada = ? AND rol = 'user'")
                        ->execute([$id_arrendatario]);
                }
            }

            $pdo->commit();
            header("Location: ../mantencion_arrendatarios.php?success=2");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error al editar: " . $e->getMessage());
        }
        break;

    case 'eliminar':
        try {
            $id = $_GET['id'] ?? '';
            $pdo->beginTransaction();

            // Desactivar Arrendatario
            $pdo->prepare("UPDATE arrendatarios SET activo = 0 WHERE id_arrendatario = ?")->execute([$id]);
            
            // Desactivar Usuario asociado (si existe)
            $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id_entidad_asociada = ? AND rol = 'user'")->execute([$id]);

            $pdo->commit();
            header("Location: ../mantencion_arrendatarios.php?success=3");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            header("Location: ../mantencion_arrendatarios.php?error=2");
            exit();
        }
        break;

    default:
        die("Acción no válida.");
        break;
}