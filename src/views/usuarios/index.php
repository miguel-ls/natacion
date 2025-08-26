<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Reutilizamos los estilos de la tabla */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; }
.btn-primary { background-color: #337ab7; }
.btn-warning { background-color: #f0ad4e; }
.btn-danger { background-color: #d9534f; }
.btn-disabled { background-color: #ccc; cursor: not-allowed; }
.action-links a { margin-right: 5px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <a href="index.php?url=usuarios/create" class="btn btn-primary">Añadir Nuevo Usuario</a>
    </div>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message" style="color: #a94442; background-color: #f2dede; border: 1px solid #ebccd1; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="5">No hay usuarios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=usuarios/edit&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                            <?php if ($usuario['id_usuario'] != $_SESSION['user_id']): ?>
                                <a href="index.php?url=usuarios/delete&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este usuario?');">Eliminar</a>
                            <?php else: ?>
                                <a class="btn btn-disabled" title="No puedes eliminar tu propio usuario">Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
