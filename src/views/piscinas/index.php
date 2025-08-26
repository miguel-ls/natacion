<?php
require_once __DIR__ . '/../partials/header.php';
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
        <h1>Gestión de Piscinas</h1>
        <a href="index.php?url=piscinas/create" class="btn btn-primary">Añadir Nueva Piscina</a>
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
                <th>Tipo de Piscina</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($piscinas)): ?>
                <tr>
                    <td colspan="4">No hay piscinas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($piscinas as $piscina): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($piscina['id_piscina']); ?></td>
                        <td><?php echo htmlspecialchars($piscina['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($piscina['tipo_piscina_nombre']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=piscinas/edit&id=<?php echo $piscina['id_piscina']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=piscinas/delete&id=<?php echo $piscina['id_piscina']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar esta piscina?');">Eliminar</a>
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
