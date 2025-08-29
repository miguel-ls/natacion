<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vista Previa - Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        .header { text-align: center; margin-bottom: 2rem; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .report-table th, .report-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .report-table th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; background-color: #f2f2f2; }
        .no-print { margin: 2rem 0; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 4px; color: white; margin: 0 10px; }
        .btn-pdf { background-color: #dc3545; }
        .btn-excel { background-color: #28a745; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Periodo del <?php echo htmlspecialchars(date('d/m/Y', strtotime($_GET['fecha_inicio']))); ?> al <?php echo htmlspecialchars(date('d/m/Y', strtotime($_GET['fecha_fin']))); ?></p>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Fecha Matrícula</th>
                <th>Alumno</th>
                <th>Curso</th>
                <th>Forma de Pago</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Precio Original</th>
                <th>Descuento</th>
                <th>Precio Final</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_ventas = 0;
            $total_descuentos = 0;
            $total_base = 0;
            if (empty($reporte_data)):
            ?>
                <tr>
                    <td colspan="9">No se encontraron resultados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reporte_data as $row):
                    $total_base += $row['precio_base'];
                    $total_descuentos += $row['descuento'];
                    $total_ventas += $row['precio_final'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['fecha_matricula']))); ?></td>
                        <td><?php echo htmlspecialchars($row['alumno_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['forma_pago_nombre'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['fecha_inicio']))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['fecha_fin']))); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['precio_base'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['descuento'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['precio_final'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" style="text-align: right;"><strong>Totales:</strong></td>
                <td><strong>S/ <?php echo htmlspecialchars(number_format($total_base, 2)); ?></strong></td>
                <td><strong>S/ <?php echo htmlspecialchars(number_format($total_descuentos, 2)); ?></strong></td>
                <td><strong>S/ <?php echo htmlspecialchars(number_format($total_ventas, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="no-print">
        <a href="index.php?url=reportes/exportarVentas&<?php echo $filters; ?>" class="btn btn-excel">Exportar a Excel</a>
    </div>

</body>
</html>
