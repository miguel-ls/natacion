<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
.chart-container {
    width: 100%;
    margin: 1.5rem auto;
    padding: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
#ventasMensualesChartContainer {
    max-width: 600px; /* Reducido al 50% (aprox) de 900px y centrado */
}
.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); /* Reducido al 40% */
    gap: 3.5rem; /* Aumentada más la separación */
}
.form-control { /* Estilo básico para los select */
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
}
</style>

<div class="container">
    <div class="page-header">
        <h1>Panel de Control</h1>
    </div>

    <!-- Filtros -->
    <div class="filter-container" style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <form action="index.php" method="GET" style="display: flex; gap: 1rem; align-items: flex-end;">
            <input type="hidden" name="url" value="inicio">
            <div>
                <label for="year" style="font-weight: bold;">Año:</label>
                <select name="year" id="year" class="form-control">
                    <?php foreach ($years as $year_option): ?>
                        <option value="<?php echo $year_option['anio']; ?>" <?php echo ($year_option['anio'] == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year_option['anio']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="month" style="font-weight: bold;">Mes:</label>
                <select name="month" id="month" class="form-control">
                    <option value="0" <?php echo ($selected_month == 0) ? 'selected' : ''; ?>>Todos</option>
                    <?php foreach ($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo ($num == $selected_month) ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div id="ventasMensualesChartContainer" class="chart-container">
        <h3>Ventas Mensuales por Curso (<?php echo $selected_year; ?>)</h3>
        <canvas id="ventasMensualesChart"></canvas>
    </div>

    <div class="chart-grid">
        <div class="chart-container">
            <h3>Ventas por Curso (<?php echo $title_period_text; ?>)</h3>
            <canvas id="ventasPorCursoChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Ventas por Forma de Pago (<?php echo $title_period_text; ?>)</h3>
            <canvas id="ventasPorFormaPagoChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Ventas por Tipo de Area (<?php echo $title_period_text; ?>)</h3>
            <canvas id="ventasPorPiscinaChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Ventas por Tipo de Curso (<?php echo $title_period_text; ?>)</h3>
            <canvas id="ventasPorTipoCursoChart"></canvas>
        </div>
    </div>
</div>

<!-- Incluir Chart.js y el plugin de datalabels desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Registrar el plugin globalmente
    Chart.register(ChartDataLabels);

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
        const data = rawData.map(d => parseFloat(d[dataField]) || 0);

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
                                    label += `S/ ${context.raw}`;
                                }
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(2) + '%' : '0%';
                            return percentage;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
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

    // --- Gráfico 5: Ventas por Tipo de Curso (Circular) ---
    createPieChart('ventasPorTipoCursoChart', chartData.ventas_por_tipo_curso, 'tipo_curso', 'total_ventas');
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
