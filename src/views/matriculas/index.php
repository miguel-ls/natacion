<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* Reutilizamos los estilos de la tabla */
.container { padding: 2rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; display: inline-block; margin-bottom: 3px;}
.btn-primary { background-color: #337ab7; }
.btn-info { background-color: #5bc0de; }
.btn-warning { background-color: #f0ad4e; }
.btn-danger { background-color: #d9534f; }
.action-links a { margin-right: 5px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
.status-activa { color: green; font-weight: bold; }
.status-vigente { color: blue; font-weight: bold; }
.status-anulada { color: red; font-weight: bold; }
.status-finalizada { color: grey; font-weight: bold; }
</style>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Matrículas</h1>
        <a href="index.php?url=matriculas/create" class="btn btn-primary">Nueva Matrícula</a>
    </div>

    <!-- Filtros de Búsqueda Avanzada -->
    <div class="filter-container" style="background-color: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <form action="index.php" method="GET" class="form-filters">
            <input type="hidden" name="url" value="matriculas">
            <div class="filter-row">
                <div class="filter-group search-container full-width">
                    <label for="alumno_search">Alumno:</label>
                    <input type="text" id="alumno_search" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($filters['alumno_nombre'] ?? ''); ?>" autocomplete="off">
                    <input type="hidden" id="id_alumno" name="id_alumno" value="<?php echo htmlspecialchars($filters['id_alumno'] ?? '0'); ?>">
                    <div id="alumno-search-results" class="search-results"></div>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group search-container full-width">
                    <label for="curso_search">Curso:</label>
                    <input type="text" id="curso_search" class="form-control" placeholder="Buscar por curso..." value="<?php echo htmlspecialchars($filters['curso_nombre'] ?? ''); ?>" autocomplete="off">
                    <input type="hidden" id="id_curso" name="id_curso" value="<?php echo htmlspecialchars($filters['id_curso'] ?? '0'); ?>">
                    <div id="curso-search-results" class="search-results"></div>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="fecha_inicio_desde">Desde:</label>
                    <input type="date" name="fecha_inicio_desde" id="fecha_inicio_desde" value="<?php echo htmlspecialchars($filters['fecha_inicio_desde'] ?? ''); ?>">
                </div>
                <div class="filter-group">
                    <label for="fecha_inicio_hasta">Hasta:</label>
                    <input type="date" name="fecha_inicio_hasta" id="fecha_inicio_hasta" value="<?php echo htmlspecialchars($filters['fecha_inicio_hasta'] ?? ''); ?>">
                </div>
                <div class="filter-group">
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado">
                        <option value="Todos" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                        <option value="Activa" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Activa') ? 'selected' : ''; ?>>Activa</option>
                        <option value="Vigente" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Vigente') ? 'selected' : ''; ?>>Vigente</option>
                        <option value="Anulada" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Anulada') ? 'selected' : ''; ?>>Anulada</option>
                        <option value="Finalizada" <?php echo (isset($filters['estado']) && $filters['estado'] == 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .form-filters {
            display: flex;
            flex-direction: column; /* Cambiado a columna */
            gap: 1rem;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            flex-grow: 1; /* Para que los grupos se expandan */
        }
        .filter-group.full-width {
            width: 100%;
            flex-basis: 100%; /* Ocupa toda la fila */
        }
        .filter-group button {
             align-self: flex-end; /* Alinea el botón a la derecha en su contenedor */
        }
        .filter-group label {
            margin-bottom: 0.25rem;
            font-weight: bold;
        }
        .search-container {
            position: relative;
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            border: 1px solid #ccc;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }
        .search-result-item {
            padding: 10px;
            cursor: pointer;
        }
        .search-result-item:hover {
            background-color: #f0f0f0;
        }
    </style>

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
                <th>Alumno</th>
                <th>Curso</th>
                <th>Profesor</th>
                <th>Inicio Matrícula</th>
                <th>Fin Matrícula</th>
                <th>Descuento</th>
                <th>Precio Final</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($matriculas)): ?>
                <tr>
                    <td colspan="10">No hay matrículas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($matriculas as $matricula): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($matricula['id_matricula']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['alumno_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['curso_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($matricula['profesor_nombre']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_inicio']))); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($matricula['fecha_fin']))); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($matricula['descuento'], 2)); ?></td>
                        <td>S/ <?php echo htmlspecialchars(number_format($matricula['precio_final'], 2)); ?></td>
                        <td>
                            <span class="status-<?php echo strtolower($matricula['estado']); ?>">
                                <?php echo htmlspecialchars(ucfirst($matricula['estado'])); ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="index.php?url=matriculas/show&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-info">Ver Días</a>
                            <?php if ($matricula['estado'] !== 'anulada' && $matricula['estado'] !== 'finalizada'): ?>
                                <a href="index.php?url=matriculas/edit&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-warning">Cambiar Hor.</a>
                                <a href="index.php?url=matriculas/cancel&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-secondary" onclick="return confirm('¿Está seguro de que desea anular esta matrícula?');">Anular</a>

                                <!-- Formulario para Eliminación Permanente -->
                                <form action="index.php?url=matriculas/delete" method="POST" style="display:inline-block; margin-left: 5px;">
                                    <input type="hidden" name="id_matricula" value="<?php echo $matricula['id_matricula']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¡ADVERTENCIA! Está a punto de ELIMINAR PERMANENTEMENTE esta matrícula y todos sus datos asociados. Esta acción no se puede deshacer. ¿Está absolutamente seguro?');">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
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

    // Lógica para búsqueda de cursos
    const cursoSearchInput = document.getElementById('curso_search');
    const cursoSearchResults = document.getElementById('curso-search-results');
    const cursoHiddenInputId = document.getElementById('id_curso');

    cursoSearchInput.addEventListener('keyup', function() {
        const term = this.value;
        if (term.length < 2) {
            cursoSearchResults.innerHTML = '';
            return;
        }
        fetch(`index.php?url=cursos/search&term=${term}`)
            .then(response => response.json())
            .then(data => {
                cursoSearchResults.innerHTML = '';
                data.forEach(curso => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    item.textContent = curso.nombre;
                    item.dataset.id = curso.id_curso;
                    item.addEventListener('click', function() {
                        cursoSearchInput.value = this.textContent;
                        cursoHiddenInputId.value = this.dataset.id;
                        cursoSearchResults.innerHTML = '';
                    });
                    cursoSearchResults.appendChild(item);
                });
            });
    });

    // Ocultar resultados si se hace clic fuera
    document.addEventListener('click', function(e) {
        if (!alumnoSearchInput.contains(e.target)) {
            alumnoSearchResults.innerHTML = '';
        }
        if (!cursoSearchInput.contains(e.target)) {
            cursoSearchResults.innerHTML = '';
        }
    });
});
</script>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
