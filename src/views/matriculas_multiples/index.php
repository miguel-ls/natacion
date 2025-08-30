<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h2>Matrículas Múltiples</h2>
    <div class="actions">
        <a href="index.php?url=matricula_multiple/create" class="btn btn-success">Nueva Matrícula Múltiple</a>
    </div>

    <table class="table-striped">
        <thead>
            <tr>
                <th>ID Grupo</th>
                <th>Cliente</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($grupos) && !empty($grupos)): ?>
                <?php foreach ($grupos as $grupo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grupo['id_grupo_matricula']); ?></td>
                        <td><?php echo htmlspecialchars($grupo['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($grupo['fecha_creacion']); ?></td>
                        <td>
                            <a href="index.php?url=matricula_multiple/show&id=<?php echo htmlspecialchars($grupo['id_grupo_matricula']); ?>" class="btn btn-primary">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No hay matrículas múltiples registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
