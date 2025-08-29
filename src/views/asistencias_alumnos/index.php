<?php
require_once __DIR__ . '/../partials/header.php';
// Variables pasadas desde el controlador:
// $matriculas, $alumnos, $cursos, $id_alumno, $id_curso, $estado
?>

<div class="container">
    <div class="page-header">
        <h1>Control de Asistencia de Clientes</h1>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Formulario de Filtros -->
    <form action="index.php" method="GET" class="filter-form form-inline mb-4">
        <input type="hidden" name="url" value="asistencias_alumnos">

        <div class="form-group">
            <label for="id_alumno">Cliente:</label>
            <select name="id_alumno" id="id_alumno" class="form-control">
                <option value="0">Todos</option>
                <?php foreach ($alumnos as $alumno): ?>
                    <option value="<?php echo $alumno['id_alumno']; ?>" <?php echo ($id_alumno == $alumno['id_alumno']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']); ?>
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
            <label for="estado">Estado Matrícula:</label>
            <select name="estado" id="estado" class="form-control">
                <option value="Todos" <?php echo ($estado == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                <option value="activa" <?php echo ($estado == 'activa') ? 'selected' : ''; ?>>Activa</option>
                <option value="vigente" <?php echo ($estado == 'vigente') ? 'selected' : ''; ?>>Vigente</option>
                <option value="finalizada" <?php echo ($estado == 'finalizada') ? 'selected' : ''; ?>>Finalizada</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="index.php?url=asistencias_alumnos" class="btn btn-secondary">Limpiar Filtros</a>
    </form>

    <!-- Tabla de Matrículas -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>Curso</th>
                <th>Horario</th>
                <th>Periodo de Matrícula</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($matriculas)): ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron matrículas con los filtros seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($matriculas as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['alumno_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($m['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($m['tipo_horario_nombre'] . ' (' . date('g:i A', strtotime($m['hora_inicio'])) . ' - ' . date('g:i A', strtotime($m['hora_fin'])) . ')'); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($m['fecha_inicio']))) . ' - ' . htmlspecialchars(date('d/m/Y', strtotime($m['fecha_fin']))); ?></td>
                        <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $m['estado_calculado'])); ?>"><?php echo htmlspecialchars(ucfirst($m['estado_calculado'])); ?></span></td>
                        <td>
                            <a href="index.php?url=asistencias_alumnos/show&id=<?php echo $m['id_matricula']; ?>" class="btn btn-info btn-sm">Marcar Asistencia</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
