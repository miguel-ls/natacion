<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h2>Mantenimiento de Tipos de Documento</h2>
    <p>Aquí puede gestionar los tipos de documento de identidad utilizados en el sistema.</p>
    <a href="index.php?url=tipos_documento/create" class="btn btn-success mb-3">Añadir Nuevo Tipo</a>

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
                <th>Longitud</th>
                <th>Código SUNAT</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tipos_documento)): ?>
                <tr>
                    <td colspan="5" class="text-center">No se encontraron tipos de documento.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tipos_documento as $tipo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tipo['id']); ?></td>
                        <td><?php echo htmlspecialchars($tipo['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($tipo['longitud']); ?></td>
                        <td><?php echo htmlspecialchars($tipo['sunat']); ?></td>
                        <td>
                            <a href="index.php?url=tipos_documento/edit&id=<?php echo $tipo['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="index.php?url=tipos_documento/delete&id=<?php echo $tipo['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de que desea eliminar este tipo de documento?');">Eliminar</a>
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
