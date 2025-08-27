<?php
require_once __DIR__ . '/../partials/header.php';
// Variables pasadas desde el controlador:
// $horarios, $cursos, $profesores, $id_profesor, $id_curso, $estado
?>

<div class="container">
    <div class="page-header">
        <h1>Control de Asistencia de Profesores</h1>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Formulario de Filtros -->
    <form action="index.php" method="GET" class="filter-form form-inline mb-4">
        <input type="hidden" name="url" value="asistencias_profesor">

        <div class="form-group">
            <label for="id_profesor">Profesor:</label>
            <select name="id_profesor" id="id_profesor" class="form-control">
                <option value="0">Todos</option>
                <?php foreach ($profesores as $profesor): ?>
                    <option value="<?php echo $profesor['id_profesor']; ?>" <?php echo ($id_profesor == $profesor['id_profesor']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_curso">Curso:</label>
            <select name="id_curso" id="id_curso" class="form-control">
                <option value="0">Todos</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?php echo $curso['id_curso']; ?>" <?php echo ($id_curso == $curso['id_curso']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($curso['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado" class="form-control">
                <option value="Todos" <?php echo ($estado == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                <option value="Futuro" <?php echo ($estado == 'Futuro') ? 'selected' : ''; ?>>Futuro</option>
                <option value="En Curso" <?php echo ($estado == 'En Curso') ? 'selected' : ''; ?>>En Curso</option>
                <option value="Finalizado" <?php echo ($estado == 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="index.php?url=asistencias_profesor" class="btn btn-secondary">Limpiar Filtros</a>
    </form>

    <!-- Tabla de Horarios Asignados -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Profesor</th>
                <th>Curso</th>
                <th>Horario</th>
                <th>Periodo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($horarios)): ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron horarios con los filtros seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($horarios as $h): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($h['profesor_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($h['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($h['tipo_horario_nombre'] . ' (' . date('g:i A', strtotime($h['hora_inicio'])) . ' - ' . date('g:i A', strtotime($h['hora_fin'])) . ')'); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($h['fecha_inicio']))) . ' - ' . htmlspecialchars(date('d/m/Y', strtotime($h['fecha_fin']))); ?></td>
                        <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $h['estado_calculado'])); ?>"><?php echo htmlspecialchars($h['estado_calculado']); ?></span></td>
                        <td>
                            <a href="index.php?url=asistencias_profesor/show&id=<?php echo $h['id_horario']; ?>" class="btn btn-info btn-sm">Marcar Asistencia</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
