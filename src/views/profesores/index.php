<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Reutilizamos los estilos de la tabla de alumnos */
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
.status-activo { color: green; font-weight: bold; }
.status-inactivo { color: red; font-weight: bold; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Profesores</h1>
        <a href="index.php?url=profesores/create" class="btn btn-primary">Añadir Nuevo Profesor</a>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="filter-container" style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <form action="index.php" method="GET">
            <input type="hidden" name="url" value="profesores">
            <label for="search">Buscar Profesor:</label>
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Nombre, apellido, email...">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
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
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($profesores)): ?>
                <tr>
                    <td colspan="7">No hay profesores registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($profesores as $profesor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($profesor['id_profesor']); ?></td>
                        <td><?php echo htmlspecialchars($profesor['nombres']); ?></td>
                        <td><?php echo htmlspecialchars($profesor['apellidos']); ?></td>
                        <td><?php echo htmlspecialchars($profesor['email']); ?></td>
                        <td><?php echo htmlspecialchars($profesor['telefono']); ?></td>
                        <td>
                            <span class="status-<?php echo $profesor['estado'] === 'activo' ? 'activo' : 'inactivo'; ?>">
                                <?php echo htmlspecialchars(ucfirst($profesor['estado'])); ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="index.php?url=profesores/edit&id=<?php echo $profesor['id_profesor']; ?>" class="btn btn-warning">Editar</a>
                            <?php if ($profesor['estado'] === 'activo'): ?>
                                <a href="index.php?url=profesores/delete&id=<?php echo $profesor['id_profesor']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea marcar este profesor como inactivo?');">Desactivar</a>
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
