<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Reutilizamos los estilos de la tabla */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; display: inline-block; margin-bottom: 3px;}
.btn-primary { background-color: #337ab7; }
.btn-info { background-color: #5bc0de; }
.btn-warning { background-color: #f0ad4e; }
.btn-danger { background-color: #d9534f; }
.action-links a { margin-right: 5px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
.status-activa { color: green; font-weight: bold; }
.status-vigente { color: blue; font-weight: bold; }
.status-anulada { color: red; font-weight: bold; }
.status-finalizada { color: grey; font-weight: bold; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Matrículas</h1>
        <a href="index.php?url=matriculas/create" class="btn btn-primary">Nueva Matrícula</a>
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
                <th>Alumno</th>
                <th>Curso</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($matriculas)): ?>
                <tr>
                    <td colspan="8">No hay matrículas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($matriculas as $matricula): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($matricula['id_matricula']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['alumno_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_inicio']))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_fin']))); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($matricula['precio_final'], 2)); ?></td>
                        <td>
                            <span class="status-<?php echo strtolower($matricula['estado']); ?>">
                                <?php echo htmlspecialchars(ucfirst($matricula['estado'])); ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="index.php?url=matriculas/show&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-info">Ver Días</a>
                            <?php if ($matricula['estado'] !== 'anulada' && $matricula['estado'] !== 'finalizada'): ?>
                                <a href="index.php?url=matriculas/edit&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-warning">Cambiar Hor.</a>
                                <a href="index.php?url=matriculas/cancel&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea anular esta matrícula?');">Anular</a>
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
