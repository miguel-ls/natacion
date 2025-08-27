<?php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Reporte de Ventas por Piscina y Carril</h1>
    </div>

    <div class="filter-container">
        <h4>Filtros del Reporte</h4>
        <form class="filter-form" action="index.php" method="GET">
            <input type="hidden" name="url" value="reportes/ventasPorPiscinaCarril">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Generar Reporte</button>
        </form>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Piscina</th>
                <th>Carril</th>
                <th>Cantidad de Matrículas</th>
                <th>Total Base</th>
                <th>Total Descuentos</th>
                <th>Total Ventas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reporte_data)): ?>
                <tr>
                    <td colspan="6">No se encontraron resultados para el período seleccionado.</td>
                </tr>
            <?php else: ?>
                <?php
                $total_base = 0;
                $total_descuentos = 0;
                $total_ventas = 0;
                foreach ($reporte_data as $row):
                    $total_base += $row['total_base'];
                    $total_descuentos += $row['total_descuentos'];
                    $total_ventas += $row['total_ventas'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['piscina_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['numero_carril']); ?></td>
                        <td><?php echo htmlspecialchars($row['cantidad_matriculas']); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['total_base'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['total_descuentos'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['total_ventas'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Totales:</strong></td>
                <td><strong>S/ <?php echo htmlspecialchars(number_format($total_base, 2)); ?></strong></td>
                <td><strong>S/ <?php echo htmlspecialchars(number_format($total_descuentos, 2)); ?></strong></td>
                <td><strong>S/ <?php echo htmlspecialchars(number_format($total_ventas, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
