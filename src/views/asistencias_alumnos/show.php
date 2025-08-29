<?php
require_once __DIR__ . '/../partials/header.php';
// Variables pasadas desde el controlador:
// $matricula, $dias_clase
?>

<div class="container">
    <div class="page-header">
        <h1>Marcar Asistencia del Cliente</h1>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if ($matricula): ?>
    <div class="attendance-info">
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($matricula['alumno_nombres'] . ' ' . $matricula['alumno_apellidos']); ?></p>
        <p><strong>Curso:</strong> <?php echo htmlspecialchars($matricula['curso_nombre']); ?></p>
        <p><strong>Periodo:</strong> <?php echo date('d/m/Y', strtotime($matricula['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($matricula['fecha_fin'])); ?></p>
    </div>

    <table class="table table-bordered table-striped attendance-table">
        <thead>
            <tr>
                <th>Fecha de Clase</th>
                <th>Estado</th>
                <th>Acciones</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dias_clase as $dia): ?>
            <tr id="row-<?php echo $dia['id_matricula_dia']; ?>">
                <td><?php echo date('d/m/Y', strtotime($dia['fecha_clase'])); ?> (<?php echo ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][date('w', strtotime($dia['fecha_clase']))]; ?>)</td>
                <td class="status-cell">
                    <?php
                    $estado_class = 'secondary'; // Default
                    if ($dia['estado'] === 'asistio') $estado_class = 'primary';
                    elseif ($dia['estado'] === 'falto') $estado_class = 'warning';
                    elseif ($dia['estado'] === 'postergada') $estado_class = 'info';
                    elseif ($dia['estado'] === 'recuperada') $estado_class = 'success';
                    elseif ($dia['estado'] === 'programada') $estado_class = 'light';
                    ?>
                    <span class="badge badge-<?php echo $estado_class; ?>">
                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dia['estado']))); ?>
                    </span>
                </td>
                <td class="actions-cell">
                    <button class="btn btn-primary btn-sm" onclick="marcarAsistencia(<?php echo $dia['id_matricula_dia']; ?>, 'asistio')">Asistió</button>
                    <button class="btn btn-warning btn-sm" onclick="marcarAsistencia(<?php echo $dia['id_matricula_dia']; ?>, 'falto')">Faltó</button>
                    <button class="btn btn-info btn-sm" onclick="marcarAsistencia(<?php echo $dia['id_matricula_dia']; ?>, 'postergada')">Postergada</button>
                </td>
                <td class="obs-cell">
                    <textarea class="form-control" onchange="guardarObservaciones(<?php echo $dia['id_matricula_dia']; ?>)"><?php echo htmlspecialchars($dia['observacion'] ?? ''); ?></textarea>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No se encontró la matrícula especificada.</p>
    <?php endif; ?>

    <a href="index.php?url=asistencias_alumnos" class="btn btn-secondary">Volver al listado</a>
</div>

<script>
function marcarAsistencia(id_dia, estado) {
    const observaciones = document.querySelector(`#row-${id_dia} textarea`).value;
    guardarAsistencia(id_dia, estado, observaciones);
}

function guardarObservaciones(id_dia) {
    const estadoActualBadge = document.querySelector(`#row-${id_dia} .status-cell .badge`);
    // Extraer el estado del texto del badge, puede ser "Programada", "Asistio", etc.
    const estadoActual = estadoActualBadge.textContent.trim().toLowerCase().replace(' ', '_');

    // Solo guardar si ya hay un estado significativo
    if (estadoActual !== 'programada' && estadoActual !== 'no_marcado') {
        const observaciones = document.querySelector(`#row-${id_dia} textarea`).value;
        guardarAsistencia(id_dia, estadoActual, observaciones);
    }
}

function guardarAsistencia(id_dia, estado, observaciones) {
    const csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

    const formData = new FormData();
    formData.append('id_matricula_dia', id_dia);
    formData.append('estado', estado);
    formData.append('observaciones', observaciones);
    formData.append('csrf_token', csrf_token);

    fetch('index.php?url=asistencias_alumnos/save', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar la UI dinámicamente
            const statusCell = document.querySelector(`#row-${id_dia} .status-cell`);
            let estadoClass = 'secondary';
            if (estado === 'asistio') estadoClass = 'primary';
            else if (estado === 'falto') estadoClass = 'warning';
            else if (estado === 'postergada') estadoClass = 'info';
            else if (estado === 'recuperada') estadoClass = 'success';
            else if (estado === 'programada') estadoClass = 'light';

            const estadoText = estado.charAt(0).toUpperCase() + estado.slice(1).replace('_', ' ');
            statusCell.innerHTML = `<span class="badge badge-${estadoClass}">${estadoText}</span>`;
        } else {
            alert('Error al guardar la asistencia: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error de red.');
    });
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
