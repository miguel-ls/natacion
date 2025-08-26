<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Estilos para la tabla y acciones */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; }
.btn-primary { background-color: #337ab7; }
.btn-warning { background-color: #f0ad4e; }
.btn-danger { background-color: #d9534f; }
.action-links a { margin-right: 5px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Alumnos</h1>
        <a href="index.php?url=alumnos/create" class="btn btn-primary">Añadir Nuevo Alumno</a>
    </div>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Documento</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alumnos)): ?>
                <tr>
                    <td colspan="7">No hay alumnos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($alumno['id_alumno']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['nombres']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['apellidos']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['documento_identidad']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['email']); ?></td>
                        <td><?php echo htmlspecialchars($alumno['telefono']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=alumnos/edit&id=<?php echo $alumno['id_alumno']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=alumnos/delete&id=<?php echo $alumno['id_alumno']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este alumno?');">Eliminar</a>
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
