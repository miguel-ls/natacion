<?php
// Este es un parcial de vista que solo renderiza la grilla de horarios.
// Se espera que la variable $schedules esté disponible desde el controlador que lo llama.
?>
<div class="schedule-grid">
    <?php if (empty($schedules)): ?>
        <p>No hay cursos con vacantes disponibles en este momento.</p>
    <?php else: ?>
        <?php foreach ($schedules as $schedule): ?>
            <div class="schedule-card" data-id-horario="<?php echo $schedule['id_horario']; ?>">
                <div class="card-header">
                    <h4><?php echo htmlspecialchars($schedule['curso_nombre']); ?></h4>
                </div>
                <div class="card-body">
                    <p><strong>Profesor:</strong> <?php echo htmlspecialchars($schedule['profesor_nombre']); ?></p>
                    <p><strong>Periodo:</strong> <?php echo ($schedule['fecha_inicio_curso'] && $schedule['fecha_fin_curso']) ? htmlspecialchars(date('d/m/Y', strtotime($schedule['fecha_inicio_curso']))) . ' - ' . htmlspecialchars(date('d/m/Y', strtotime($schedule['fecha_fin_curso']))) : 'No definido'; ?></p>
                    <p><strong>Horario:</strong> <?php echo htmlspecialchars($schedule['tipo_horario_nombre']); ?> (<?php echo htmlspecialchars($schedule['hora_inicio'] . ' - ' . $schedule['hora_fin']); ?>)</p>
                    <p><strong>Ubicación:</strong> Piscina <?php echo htmlspecialchars($schedule['piscina_nombre']); ?>, Carril <?php echo htmlspecialchars($schedule['numero_carril']); ?></p>
                </div>
                <div class="card-footer">
                    <span class="vacantes">Vacantes: <?php echo htmlspecialchars($schedule['vacantes_disponibles']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
