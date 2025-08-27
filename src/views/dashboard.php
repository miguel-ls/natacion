<?php
// Incluir el controlador de autenticación para usar el método checkAuth
require_once __DIR__ . '/../controllers/AuthController.php';
$auth = new AuthController();
$auth->checkAuth(); // Proteger esta página

// Incluir la cabecera de la página
require_once __DIR__ . '/partials/header.php';
?>

<div class="dashboard-container" style="padding: 2rem;">
    <h2>Panel de Administración</h2>
    <p>¡Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    <p>Desde aquí podrás gestionar todo el sistema de matrículas de natación.</p>

    <p>A continuación se muestran los horarios con vacantes disponibles. Seleccione uno para iniciar una matrícula rápida.</p>

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

    <div class="enrollment-action" style="margin-top: 2rem; text-align: center;">
        <button id="generate-enrollment-btn" class="btn btn-primary" disabled>Generar Matrícula</button>
    </div>
</div>

<style>
.schedule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}
.schedule-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: flex;
    flex-direction: column;
}
.schedule-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.schedule-card.selected {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.25);
    transform: translateY(-5px);
}
.card-header {
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #ddd;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
.card-header h4 {
    margin: 0;
    font-size: 1.1rem;
}
.card-body {
    padding: 1rem;
    flex-grow: 1;
}
.card-body p {
    margin: 0 0 0.5rem;
    font-size: 0.9rem;
}
.card-footer {
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-top: 1px solid #ddd;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    text-align: right;
}
.vacantes {
    font-weight: bold;
    color: #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.querySelector('.schedule-grid');
    const enrollButton = document.getElementById('generate-enrollment-btn');
    let selectedHorarioId = null;

    grid.addEventListener('click', function(event) {
        const card = event.target.closest('.schedule-card');
        if (!card) return;

        // Remove selection from all other cards
        document.querySelectorAll('.schedule-card').forEach(c => {
            c.classList.remove('selected');
        });

        // Add selection to the clicked card
        card.classList.add('selected');
        selectedHorarioId = card.dataset.idHorario;

        // Enable the button
        enrollButton.disabled = false;
    });

    enrollButton.addEventListener('click', function() {
        if (selectedHorarioId) {
            window.location.href = `index.php?url=matriculas/create&id_horario=${selectedHorarioId}`;
        }
    });
});
</script>

<?php
// Incluir el pie de página
require_once __DIR__ . '/partials/footer.php';
?>
