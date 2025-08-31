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
.fc-daygrid-event-dot {
    display: none;
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
        eventDisplay: 'block',
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'index.php?url=calendario/getEventos',
        eventContent: function(arg) {
            let props = arg.event.extendedProps;

            // Formateo de la hora
            let startTime = new Date(arg.event.start).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
            let endTime = new Date(arg.event.end).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
            let timeRange = startTime + ' - ' + endTime;

            let subAreaInfo = props.sub_area_descripcion + ' - ' + props.sub_area_numero;

            let html = `
                <div style="font-size: 0.8em; line-height: 1.2;">
                    ${timeRange}<br>
                    <strong>${props.curso_nombre}</strong><br>
                    ${props.area_nombre}<br>
                    ${subAreaInfo}<br>
                    Prof: ${props.profesor_nombre}<br>
                    Alum: ${props.alumno_nombre}
                </div>
            `;

            return { html: html };
        }
    });

    calendar.render();
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
