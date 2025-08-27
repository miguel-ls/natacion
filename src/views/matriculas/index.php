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

    <!-- Filtros de Búsqueda Avanzada -->
    <div class="filter-container" style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <form action="index.php" method="GET" class="form-filters">
            <input type="hidden" name="url" value="matriculas">
            <div class="filter-group">
                <label for="id_alumno">Alumno:</label>
                <select name="id_alumno" id="id_alumno">
                    <option value="0">Todos</option>
                    <?php foreach ($alumnos as $alumno): ?>
                        <option value="<?php echo $alumno['id_alumno']; ?>" <?php echo (isset($filters['id_alumno']) && $filters['id_alumno'] == $alumno['id_alumno']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="id_curso">Curso:</label>
                <select name="id_curso" id="id_curso">
                    <option value="0">Todos</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id_curso']; ?>" <?php echo (isset($filters['id_curso']) && $filters['id_curso'] == $curso['id_curso']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($curso['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="fecha_inicio_desde">Desde:</label>
                <input type="date" name="fecha_inicio_desde" id="fecha_inicio_desde" value="<?php echo htmlspecialchars($filters['fecha_inicio_desde'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label for="fecha_inicio_hasta">Hasta:</label>
                <input type="date" name="fecha_inicio_hasta" id="fecha_inicio_hasta" value="<?php echo htmlspecialchars($filters['fecha_inicio_hasta'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label for="estado">Estado:</label>
                <select name="estado" id="estado">
                    <option value="Todos" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                    <option value="Activa" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Activa') ? 'selected' : ''; ?>>Activa</option>
                    <option value="Vigente" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Vigente') ? 'selected' : ''; ?>>Vigente</option>
                    <option value="Anulada" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Anulada') ? 'selected' : ''; ?>>Anulada</option>
                    <option value="Finalizada" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <style>
        .form-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            margin-bottom: 0.25rem;
            font-weight: bold;
        }
    </style>

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
                <th>Inicio Matrícula</th>
                <th>Fin Matrícula</th>
                <th>Inicio Curso</th>
                <th>Fin Curso</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($matriculas)): ?>
                <tr>
                    <td colspan="10">No hay matrículas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($matriculas as $matricula): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($matricula['id_matricula']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['alumno_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_inicio']))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_fin']))); ?></td>
                        <td><?php echo $matricula['fecha_inicio_curso'] ? htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_inicio_curso']))) : 'N/A'; ?></td>
                        <td><?php echo $matricula['fecha_fin_curso'] ? htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_fin_curso']))) : 'N/A'; ?></td>
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
                                <a href="index.php?url=matriculas/cancel&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-secondary" onclick="return confirm('¿Está seguro de que desea anular esta matrícula?');">Anular</a>

                                <!-- Formulario para Eliminación Permanente -->
                                <form action="index.php?url=matriculas/delete" method="POST" style="display:inline-block; margin-left: 5px;">
                                    <input type="hidden" name="id_matricula" value="<?php echo $matricula['id_matricula']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¡ADVERTENCIA! Está a punto de ELIMINAR PERMANENTEMENTE esta matrícula y todos sus datos asociados. Esta acción no se puede deshacer. ¿Está absolutamente seguro?');">Eliminar</button>
                                </form>
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
