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

    <div class="dashboard-actions" style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
        <button id="refresh-btn" class="btn btn-secondary">Actualizar Listado</button>
        <span id="countdown-timer" style="font-style: italic;"></span>
    </div>

    <div id="schedules-container">
        <?php require __DIR__ . '/dashboard/_schedules_list.php'; ?>
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
    const schedulesContainer = document.getElementById('schedules-container');
    const enrollButton = document.getElementById('generate-enrollment-btn');
    const refreshButton = document.getElementById('refresh-btn');
    const countdownTimer = document.getElementById('countdown-timer');
    let selectedHorarioId = null;

    // --- Lógica de selección ---
    schedulesContainer.addEventListener('click', function(event) {
        const card = event.target.closest('.schedule-card');
        if (!card) return;

        document.querySelectorAll('.schedule-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedHorarioId = card.dataset.idHorario;
        enrollButton.disabled = false;
    });

    // --- Lógica de matrícula ---
    enrollButton.addEventListener('click', function() {
        if (selectedHorarioId) {
            window.location.href = `index.php?url=matriculas/create&id_horario=${selectedHorarioId}`;
        }
    });

    // --- Lógica de actualización ---
    const REFRESH_INTERVAL = 5; // en segundos
    let countdown = REFRESH_INTERVAL;
    let timerInterval;

    function refreshSchedules() {
        // Deshabilitar botón de matrícula y resetear selección
        enrollButton.disabled = true;
        selectedHorarioId = null;

        countdownTimer.textContent = 'Actualizando...';
        fetch('index.php?url=dashboard/getAvailableSchedules')
            .then(response => response.text())
            .then(html => {
                schedulesContainer.innerHTML = html;
                resetTimer();
            })
            .catch(error => {
                console.error('Error al actualizar los horarios:', error);
                countdownTimer.textContent = 'Error al actualizar.';
            });
    }

    function updateCountdown() {
        countdown--;
        countdownTimer.textContent = `Actualizando en ${countdown}s...`;
        if (countdown <= 0) {
            refreshSchedules();
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        countdown = REFRESH_INTERVAL;
        countdownTimer.textContent = `Actualizando en ${countdown}s...`;
        timerInterval = setInterval(updateCountdown, 1000);
    }

    refreshButton.addEventListener('click', function() {
        refreshSchedules();
    });

    // Iniciar el temporizador
    resetTimer();
});
</script>

<?php
// Incluir el pie de página
require_once __DIR__ . '/partials/footer.php';
?>
