<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Añadimos algunos estilos básicos para el calendario */
.container {
    padding: 2rem;
}
#calendar {
    max-width: 1100px;
    margin: 0 auto;
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
        initialView: 'dayGridMonth',
        locale: 'es', // Establecer el idioma a español
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Opciones de vista
        },
        events: 'index.php?url=calendario/getEventos'
    });

    calendar.render();
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
