<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Tipos de Precio</h1>
        <a href="index.php?url=tipos_precio/create" class="btn btn-primary">Añadir Nuevo Tipo de Precio</a>
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
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="4">No hay tipos de precio registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id_tipo_precio']); ?></td>
                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=tipos_precio/edit&id=<?php echo $item['id_tipo_precio']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=tipos_precio/delete&id=<?php echo $item['id_tipo_precio']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este tipo de precio?');">Eliminar</a>
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
