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
</style>

<div class="container">
    <div class="page-header">
        <h1>Reporte de Horas por Profesor</h1>
    </div>

    <div class="filter-container">
        <h4>Filtros del Reporte</h4>
        <form class="filter-form" action="index.php" method="GET">
            <input type="hidden" name="url" value="reportes/profesores">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
            </div>
            <div class="form-group">
                <label for="id_profesor">Profesor</label>
                <select name="id_profesor" id="id_profesor">
                    <option value="0">Todos los Profesores</option>
                    <?php foreach ($profesores as $profesor): ?>
                        <option value="<?php echo $profesor['id_profesor']; ?>" <?php echo (isset($id_profesor) && $profesor['id_profesor'] == $id_profesor) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self: flex-end;">Generar Reporte</button>
        </form>
    </div>

     <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <table class="report-table">
        <thead>
            <tr>
                <th>Profesor</th>
                <th>Total de Clases Asistidas</th>
                <th>Total de Horas Trabajadas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reporte_data)): ?>
                <tr>
                    <td colspan="3">No se encontraron resultados para el rango de fechas seleccionado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reporte_data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['profesor_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['clases_asistidas']); ?></td>
                        <td><?php echo htmlspecialchars($row['horas_trabajadas']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
