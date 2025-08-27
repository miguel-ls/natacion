<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <h1>Reporte de Ventas por Curso</h1>

    <div class="filter-container">
        <form action="index.php" method="GET">
            <input type="hidden" name="url" value="reportes/ventasPorCurso">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Generar Reporte</button>
        </form>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Curso</th>
                <th>Cantidad de Matrículas</th>
                <th>Total Base</th>
                <th>Total Descuentos</th>
                <th>Total Ventas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reporte_data)): ?>
                <tr>
                    <td colspan="5">No se encontraron resultados para el período seleccionado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reporte_data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['cantidad_matriculas']); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['total_base'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['total_descuentos'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['total_ventas'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
