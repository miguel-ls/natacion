<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
.chart-container {
    width: 100%;
    max-width: 900px;
    margin: 2rem auto;
    padding: 2rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}
.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}
</style>

<div class="container">
    <div class="page-header">
        <h1>Inicio</h1>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="chart-container">
        <h3>Ventas Mensuales por Curso (Año Actual)</h3>
        <canvas id="ventasMensualesChart"></canvas>
    </div>

    <div class="chart-grid">
        <div class="chart-container">
            <h3>Ventas por Curso (Año Actual)</h3>
            <canvas id="ventasPorCursoChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Ventas por Forma de Pago (Año Actual)</h3>
            <canvas id="ventasPorFormaPagoChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Ventas por Tipo de Piscina (Año Actual)</h3>
            <canvas id="ventasPorPiscinaChart"></canvas>
        </div>
    </div>
</div>

<!-- Incluir Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener datos desde PHP
    const chartData = <?php echo json_encode($chart_data ?? []); ?>;

    // --- Colores para los gráficos ---
    const backgroundColors = [
        'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(199, 199, 199, 0.7)', 'rgba(83, 102, 255, 0.7)',
        'rgba(40, 159, 64, 0.7)', 'rgba(210, 99, 132, 0.7)'
    ];

    // --- Gráfico 1: Ventas Mensuales por Curso (Barras Apiladas) ---
    const ventasMensualesData = chartData.ventas_mensuales_por_curso || [];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const cursos = [...new Set(ventasMensualesData.map(d => d.curso_nombre))];
    const datasetsVentasMensuales = cursos.map((curso, index) => {
        const data = meses.map((_, mesIndex) => {
            const mesNum = mesIndex + 1;
            const record = ventasMensualesData.find(d => d.mes == mesNum && d.curso_nombre === curso);
            return record ? record.total_ventas : 0;
        });
        return {
            label: curso,
            data: data,
            backgroundColor: backgroundColors[index % backgroundColors.length],
        };
    });

    if (document.getElementById('ventasMensualesChart')) {
        new Chart(document.getElementById('ventasMensualesChart'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: datasetsVentasMensuales
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
    }

    // --- Función auxiliar para gráficos circulares ---
    function createPieChart(canvasId, rawData, labelField, dataField) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !rawData || rawData.length === 0) return;

        const labels = rawData.map(d => d[labelField]);
        const data = rawData.map(d => d[dataField]);

        new Chart(canvas, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? (context.raw / total * 100).toFixed(2) : 0;
                                    label += `S/ ${context.raw} (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- Gráfico 2: Ventas por Curso (Circular) ---
    createPieChart('ventasPorCursoChart', chartData.ventas_por_curso, 'curso_nombre', 'total_ventas');

    // --- Gráfico 3: Ventas por Forma de Pago (Circular) ---
    createPieChart('ventasPorFormaPagoChart', chartData.ventas_por_forma_pago, 'forma_pago', 'total_ventas');

    // --- Gráfico 4: Ventas por Tipo de Piscina (Circular) ---
    createPieChart('ventasPorPiscinaChart', chartData.ventas_por_piscina, 'tipo_piscina', 'total_ventas');
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
