<?php
require_once __DIR__ . '/../partials/header.php';
// Variables pasadas desde el controlador:
// $horarios, $cursos, $id_profesor, $id_curso, $estado, $profesor_seleccionado
?>

<style>
/* Fix para que el autocompletado de jQuery UI aparezca sobre otros elementos */
.ui-autocomplete {
    z-index: 1050;
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
}
.search-details {
    margin-top: 10px;
    padding: 10px;
    background-color: #e9ecef;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
</style>

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
            <label for="profesor_search">Profesor:</label>
            <input type="text" id="profesor_search" class="form-control"
                   placeholder="Escriba nombre o apellido..."
                   value="<?php echo $profesor_seleccionado ? htmlspecialchars($profesor_seleccionado['nombres'] . ' ' . $profesor_seleccionado['apellidos']) : ''; ?>">
            <input type="hidden" name="id_profesor" id="id_profesor" value="<?php echo htmlspecialchars($id_profesor); ?>">
            <div id="profesor_details" class="search-details" style="<?php echo $profesor_seleccionado ? '' : 'display: none;'; ?>">
                <?php if ($profesor_seleccionado): ?>
                    <strong>Profesor Seleccionado:</strong> <?php echo htmlspecialchars($profesor_seleccionado['nombres'] . ' ' . $profesor_seleccionado['apellidos']); ?> (ID: <?php echo $profesor_seleccionado['id_profesor']; ?>)
                <?php endif; ?>
            </div>
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

<script>
$(function() {
    $("#profesor_search").autocomplete({
        source: "index.php?url=profesores/search",
        minLength: 2,
        select: function(event, ui) {
            $("#id_profesor").val(ui.item.id);
            $("#profesor_search").val(ui.item.value);
            $("#profesor_details").html("<strong>Profesor Seleccionado:</strong> " + ui.item.value + " (ID: " + ui.item.id + ")").show();
            return false; // previene que el valor por defecto de jQuery UI se ponga en el input
        },
        focus: function(event, ui) {
            // Sincroniza el valor del input con el elemento enfocado en la lista
            $("#profesor_search").val(ui.item.value);
            return false;
        }
    });

    // Limpiar el ID oculto y los detalles si el usuario borra el campo de búsqueda
    $("#profesor_search").on('input', function() {
        if ($(this).val() === '') {
            $("#id_profesor").val('0'); // Usar '0' para "todos"
            $("#profesor_details").hide().html('');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
