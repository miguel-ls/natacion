<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Lista de Precios por Curso</h1>
        <a href="index.php?url=precios_cursos/create" class="btn btn-primary">Añadir Nuevo Precio</a>
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
                <th>Curso</th>
                <th>Tipo de Precio</th>
                <th>Precio</th>
                <th>Vigencia Inicio</th>
                <th>Vigencia Fin</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="7">No hay precios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id_precio_curso']); ?></td>
                        <td><?php echo htmlspecialchars($item['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($item['tipo_precio_nombre']); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($item['precio'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($item['fecha_inicio']))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($item['fecha_fin']))); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=precios_cursos/edit&id=<?php echo $item['id_precio_curso']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=precios_cursos/delete&id=<?php echo $item['id_precio_curso']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este precio?');">Eliminar</a>
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
