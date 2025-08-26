<?php
require_once __DIR__ . '/../partials/header.php';

// Helper function to display days correctly according to MySQL DAYOFWEEK()
function format_dias_semana($dias_str) {
    if (empty($dias_str)) return 'N/A';
    // Mapa correcto: 1=Dom, 2=Lun, 3=Mar, 4=Mié, 5=Jue, 6=Vie, 7=Sáb
    $dias_map = [
        '1' => 'Dom',
        '2' => 'Lun',
        '3' => 'Mar',
        '4' => 'Mié',
        '5' => 'Jue',
        '6' => 'Vie',
        '7' => 'Sáb'
    ];
    $dias_arr = explode(',', $dias_str);
    $dias_formatted = [];
    foreach ($dias_arr as $dia) {
        if (isset($dias_map[$dia])) {
            $dias_formatted[] = $dias_map[$dia];
        }
    }
    return implode(', ', $dias_formatted);
}
?>

<style>
/* Reutilizamos los estilos de la tabla */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; }
.btn-primary { background-color: #337ab7; }
.btn-warning { background-color: #f0ad4e; }
.btn-danger { background-color: #d9534f; }
.action-links a { margin-right: 5px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Tipos de Horario</h1>
        <a href="index.php?url=tipos_horario/create" class="btn btn-primary">Añadir Nuevo Tipo</a>
    </div>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Días de la Semana</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tipos_horario)): ?>
                <tr>
                    <td colspan="4">No hay tipos de horario registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tipos_horario as $tipo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tipo['id_tipo_horario']); ?></td>
                        <td><?php echo htmlspecialchars($tipo['nombre']); ?></td>
                        <td><?php echo format_dias_semana($tipo['dias_semana']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=tipos_horario/edit&id=<?php echo $tipo['id_tipo_horario']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=tipos_horario/delete&id=<?php echo $tipo['id_tipo_horario']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este tipo de horario?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
