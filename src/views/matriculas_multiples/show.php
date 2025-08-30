<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <?php if (isset($grupo) && $grupo): ?>
        <h2>Detalle del Grupo de Matrícula #<?php echo htmlspecialchars($grupo['id_grupo_matricula']); ?></h2>

        <div class="info-section">
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($grupo['nombre_cliente']); ?></p>
            <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($grupo['fecha_creacion']); ?></p>
        </div>

        <h4>Matrículas Individuales en este Grupo:</h4>

        <table class="table-striped">
            <thead>
                <tr>
                    <th>ID Matrícula</th>
                    <th>Curso</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Precio Final</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($matriculas) && !empty($matriculas)): ?>
                    <?php foreach ($matriculas as $matricula): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($matricula['id_matricula']); ?></td>
                            <td><?php echo htmlspecialchars($matricula['curso_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($matricula['fecha_inicio']); ?></td>
                            <td><?php echo htmlspecialchars($matricula['fecha_fin']); ?></td>
                            <td>S/ <?php echo htmlspecialchars(number_format($matricula['precio_final'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($matricula['estado']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No se encontraron matrículas en este grupo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php else: ?>
        <h2>Grupo de Matrícula no encontrado</h2>
        <p>El grupo de matrícula que busca no existe o fue eliminado.</p>
    <?php endif; ?>

    <div class="actions" style="margin-top: 2rem;">
        <a href="index.php?url=matricula_multiple" class="btn btn-secondary">Volver a la Lista</a>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
