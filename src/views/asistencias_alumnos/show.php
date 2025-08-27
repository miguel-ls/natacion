<?php
require_once __DIR__ . '/../partials/header.php';
// Variables pasadas desde el controlador:
// $matricula, $dias_clase
?>

<div class="container">
    <div class="page-header">
        <h1>Marcar Asistencia de Alumno</h1>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if ($matricula): ?>
    <div class="attendance-info">
        <p><strong>Alumno:</strong> <?php echo htmlspecialchars($matricula['alumno_nombres'] . ' ' . $matricula['alumno_apellidos']); ?></p>
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
            <?php foreach ($dias_clase as $fecha => $data): ?>
            <tr id="row-<?php echo $fecha; ?>">
                <td><?php echo date('d/m/Y', strtotime($fecha)); ?> (<?php echo ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][date('w', strtotime($fecha))]; ?>)</td>
                <td class="status-cell">
                    <?php
                    $estado_class = 'secondary'; // Default for 'no_marcado'
                    if ($data['estado'] === 'asistio') {
                        $estado_class = 'primary';
                    } elseif ($data['estado'] === 'falto') {
                        $estado_class = 'warning';
                    } elseif ($data['estado'] === 'postergado') {
                        $estado_class = 'info';
                    }
                    ?>
                    <span class="badge badge-<?php echo $estado_class; ?>">
                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $data['estado']))); ?>
                    </span>
                </td>
                <td class="actions-cell">
                    <button class="btn btn-primary btn-sm" onclick="marcarAsistencia('<?php echo $fecha; ?>', 'asistio')">Asistió</button>
                    <button class="btn btn-warning btn-sm" onclick="marcarAsistencia('<?php echo $fecha; ?>', 'falto')">Faltó</button>
                    <button class="btn btn-info btn-sm" onclick="marcarAsistencia('<?php echo $fecha; ?>', 'postergado')">Postergado</button>
                </td>
                <td class="obs-cell">
                    <textarea class="form-control" onchange="guardarObservaciones('<?php echo $fecha; ?>')"><?php echo htmlspecialchars($data['observaciones']); ?></textarea>
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
function marcarAsistencia(fecha, estado) {
    const observaciones = document.querySelector(`#row-${fecha} textarea`).value;
    guardarAsistencia(fecha, estado, observaciones);
}

function guardarObservaciones(fecha) {
    const estadoActual = document.querySelector(`#row-${fecha} .status-cell .badge`).textContent.trim().toLowerCase().replace(' ', '_');
    if (estadoActual !== 'no_marcado') {
        const observaciones = document.querySelector(`#row-${fecha} textarea`).value;
        guardarAsistencia(fecha, estadoActual, observaciones);
    }
}

function guardarAsistencia(fecha, estado, observaciones) {
    const id_matricula = <?php echo $matricula['id_matricula']; ?>;
    const id_alumno = <?php echo $matricula['id_alumno']; ?>;
    const csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';

    const formData = new FormData();
    formData.append('id_matricula', id_matricula);
    formData.append('id_alumno', id_alumno);
    formData.append('fecha_clase', fecha);
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
            const statusCell = document.querySelector(`#row-${fecha} .status-cell`);
            let estadoClass = estado.toLowerCase().replace('_', '-');
            let estadoText = estado.charAt(0).toUpperCase() + estado.slice(1).replace('_', ' ');
            statusCell.innerHTML = `<span class="badge badge-${estadoClass}">${estadoText}</span>`;
            // Opcional: mostrar un mensaje de éxito
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
