<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
.container { padding: 2rem; }
.page-header h1 { margin-bottom: 1rem; }
.filter-container { background-color: #f9f9f9; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
.filter-form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; }
.report-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.report-table th, .report-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.report-table th { background-color: #f2f2f2; }
.total-row { font-weight: bold; background-color: #f2f2f2; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Reporte de Ventas</h1>
    </div>

    <div class="filter-container">
        <h4>Filtros del Reporte</h4>
        <form class="filter-form" action="index.php" method="GET">
            <input type="hidden" name="url" value="reportes/ventas">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
            </div>
            <div class="form-group">
                <label for="id_alumno">Alumno</label>
                <select name="id_alumno">
                    <option value="0">Todos</option>
                    <?php foreach ($alumnos as $alumno): ?>
                        <option value="<?php echo $alumno['id_alumno']; ?>" <?php echo ($id_alumno == $alumno['id_alumno']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_curso">Curso</label>
                <select name="id_curso">
                    <option value="0">Todos</option>
                     <?php foreach ($cursos as $curso): ?>
                        <option value="<?php echo $curso['id_curso']; ?>" <?php echo ($id_curso == $curso['id_curso']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($curso['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_forma_pago">Forma de Pago</label>
                <select name="id_forma_pago">
                    <option value="0">Todas</option>
                    <?php foreach ($formas_pago as $forma): ?>
                        <option value="<?php echo $forma['id_forma_pago']; ?>" <?php echo ($id_forma_pago == $forma['id_forma_pago']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($forma['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self: flex-end;">Generar Reporte</button>
        </form>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Fecha Matrícula</th>
                <th>Alumno</th>
                <th>Curso</th>
                <th>Forma de Pago</th>
                <th>Precio Final</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_ventas = 0;
            if (empty($reporte_data)):
            ?>
                <tr>
                    <td colspan="5">No se encontraron resultados para los filtros seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reporte_data as $row):
                    $total_ventas += $row['precio_final'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($row['fecha_matricula']))); ?></td>
                        <td><?php echo htmlspecialchars($row['alumno_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['forma_pago_nombre']); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($row['precio_final'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total de Ventas:</td>
                <td>S/ <?php echo htmlspecialchars(number_format($total_ventas, 2)); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
