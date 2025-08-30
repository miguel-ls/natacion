<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Estilos consistentes para la tabla y filtros */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; display: inline-block; }
.btn-primary { background-color: #337ab7; }
.btn-success { background-color: #28a745; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }

/* Estilos para los filtros */
.filter-container { background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.form-filters { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; flex-grow: 1; }
.filter-group label { margin-bottom: 0.25rem; font-weight: bold; }
.search-container { position: relative; }
.search-results { position: absolute; top: 100%; left: 0; background-color: white; border: 1px solid #ccc; width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000; }
.search-result-item { padding: 10px; cursor: pointer; }
.search-result-item:hover { background-color: #f0f0f0; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Matrículas Múltiples</h1>
        <a href="index.php?url=matricula_multiple/create" class="btn btn-success">Nueva Matrícula Múltiple</a>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="filter-container">
        <form action="index.php" method="GET" class="form-filters">
            <input type="hidden" name="url" value="matricula_multiple">
            <div class="filter-group search-container" style="flex-basis: 40%;">
                <label for="alumno_search">Cliente:</label>
                <input type="text" id="alumno_search" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($filters['alumno_nombre'] ?? ''); ?>" autocomplete="off">
                <input type="hidden" id="id_alumno" name="id_alumno" value="<?php echo htmlspecialchars($filters['id_alumno'] ?? '0'); ?>">
                <div id="alumno-search-results" class="search-results"></div>
            </div>
            <div class="filter-group">
                <label for="fecha_desde">Desde:</label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="<?php echo htmlspecialchars($filters['fecha_desde'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <label for="fecha_hasta">Hasta:</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?php echo htmlspecialchars($filters['fecha_hasta'] ?? ''); ?>">
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID Grupo</th>
                <th>Cliente</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($grupos) && !empty($grupos)): ?>
                <?php foreach ($grupos as $grupo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grupo['id_grupo_matricula']); ?></td>
                        <td><?php echo htmlspecialchars($grupo['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($grupo['fecha_creacion']))); ?></td>
                        <td>
                            <a href="index.php?url=matricula_multiple/show&id=<?php echo htmlspecialchars($grupo['id_grupo_matricula']); ?>" class="btn btn-primary">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No se encontraron grupos de matrícula con los filtros seleccionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para búsqueda de alumnos
    const alumnoSearchInput = document.getElementById('alumno_search');
    const alumnoSearchResults = document.getElementById('alumno-search-results');
    const alumnoHiddenInputId = document.getElementById('id_alumno');

    alumnoSearchInput.addEventListener('keyup', function() {
        const term = this.value;
        if (term.length < 2) {
            alumnoSearchResults.innerHTML = '';
            return;
        }
        fetch(`index.php?url=alumnos/search&term=${term}`)
            .then(response => response.json())
            .then(data => {
                alumnoSearchResults.innerHTML = '';
                data.forEach(alumno => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    item.textContent = `${alumno.nombres} ${alumno.apellidos} (${alumno.documento_identidad || 'N/D'})`;
                    item.dataset.id = alumno.id_alumno;
                    item.addEventListener('click', function() {
                        alumnoSearchInput.value = this.textContent;
                        alumnoHiddenInputId.value = this.dataset.id;
                        alumnoSearchResults.innerHTML = '';
                    });
                    alumnoSearchResults.appendChild(item);
                });
            });
    });

    // Ocultar resultados si se hace clic fuera
    document.addEventListener('click', function(e) {
        if (alumnoSearchInput && !alumnoSearchInput.contains(e.target)) {
            alumnoSearchResults.innerHTML = '';
        }
    });
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
