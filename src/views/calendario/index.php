<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Estilos para el calendario */
.container {
    padding: 2rem;
}
#calendar {
    max-width: 1100px;
    margin: 0 auto;
}
/* Forzar el ajuste de texto en los eventos del calendario */
.fc-event-title {
    white-space: normal !important; /* Permite que el texto se ajuste en múltiples líneas */
    overflow-wrap: break-word;
    font-size: 0.85em;
    line-height: 1.3;
}
/* Opcional: Dar un poco más de altura a las celdas para que quepa el texto */
.fc .fc-daygrid-day-frame {
    min-height: 110px;
}
</style>

<div class="container">
    <h2>Calendario de Clases</h2>
    <div id="calendar"></div> <!-- El calendario se renderizará aquí -->
</div>

<!-- Incluimos la librería FullCalendar -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.19/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        eventDisplay: 'block', // Muestra el evento como un bloque de color
        initialView: 'dayGridMonth',
        locale: 'es', // Establecer el idioma a español
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        // El backend ahora envía el 'title' y los colores directamente.
        // FullCalendar los usará por defecto.
        events: 'index.php?url=calendario/getEventos'
    });

    calendar.render();
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
