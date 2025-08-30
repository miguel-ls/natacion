<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h2>Gestión de Tipos de Profesor</h2>
    <p>Aquí puede gestionar los tipos de profesor.</p>
    <a href="index.php?url=tipos_profesor/create" class="btn btn-success mb-3">Añadir Nuevo Tipo</a>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="3" class="text-center">No se encontraron tipos de profesor.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                        <td>
                            <a href="index.php?url=tipos_profesor/edit&id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="index.php?url=tipos_profesor/delete&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar este tipo de profesor?');">Eliminar</a>
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
