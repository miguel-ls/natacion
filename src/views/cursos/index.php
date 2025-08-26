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
.action-links a { margin-right: 5px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Cursos</h1>
        <a href="index.php?url=cursos/create" class="btn btn-primary">Añadir Nuevo Curso</a>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="filter-container" style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <form action="index.php" method="GET">
            <input type="hidden" name="url" value="cursos">
            <label for="search">Buscar Curso:</label>
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Nombre o descripción...">
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
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cursos)): ?>
                <tr>
                    <td colspan="4">No hay cursos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($cursos as $curso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($curso['id_curso']); ?></td>
                        <td><?php echo htmlspecialchars($curso['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($curso['descripcion']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=cursos/edit&id=<?php echo $curso['id_curso']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=cursos/delete&id=<?php echo $curso['id_curso']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este curso?');">Eliminar</a>
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
