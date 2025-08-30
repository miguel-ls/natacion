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
        <h1>Gestión de Sub Areas</h1>
        <a href="index.php?url=carriles/create" class="btn btn-primary">Añadir Nueva Sub Area</a>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="filter-container" style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <form action="index.php" method="GET">
            <input type="hidden" name="url" value="carriles">
            <label for="search">Buscar por Area o N° Sub Area:</label>
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
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
                <th>Area</th>
                <th>Número de Sub Area</th>
                <th>Capacidad Máxima</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($carriles)): ?>
                <tr>
                    <td colspan="5">No hay sub areas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($carriles as $carril): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($carril['id_carril']); ?></td>
                        <td><?php echo htmlspecialchars($carril['piscina_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($carril['numero_carril']); ?></td>
                        <td><?php echo htmlspecialchars($carril['capacidad_maxima']); ?></td>
                        <td class="action-links">
                            <a href="index.php?url=carriles/edit&id=<?php echo $carril['id_carril']; ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?url=carriles/delete&id=<?php echo $carril['id_carril']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar esta sub area?');">Eliminar</a>
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
