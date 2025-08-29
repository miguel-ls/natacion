<?php
require_once __DIR__ . '/../partials/header.php';
?>

<style>
.container { padding: 2rem; }
.details-card { background-color: #f9f9f9; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
.details-card h2 { margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 0.5rem; }
.details-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; }
.detail-item { background-color: #fff; padding: 1rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.detail-item strong { display: block; color: #555; margin-bottom: 0.25rem; }
.table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
.table th { background-color: #f2f2f2; }
.btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; color: white; display: inline-block; }
.btn-info { background-color: #5bc0de; }
.status-activa { color: green; font-weight: bold; }
.status-vigente { color: blue; font-weight: bold; }
.status-anulada { color: red; font-weight: bold; }
.status-finalizada { color: grey; font-weight: bold; }
</style>

<div class="container">
    <a href="index.php?url=alumnos" class="btn btn-primary" style="margin-bottom: 1rem;">Volver a la Lista</a>

    <div class="details-card">
        <h2>Detalles del Cliente</h2>
        <div class="details-grid">
            <div class="detail-item"><strong>ID:</strong> <?php echo htmlspecialchars($alumno['id_alumno']); ?></div>
            <div class="detail-item"><strong>Nombres:</strong> <?php echo htmlspecialchars($alumno['nombres']); ?></div>
            <div class="detail-item"><strong>Apellidos:</strong> <?php echo htmlspecialchars($alumno['apellidos']); ?></div>
            <div class="detail-item"><strong>Documento:</strong> <?php echo htmlspecialchars($alumno['documento_identidad']); ?></div>
            <div class="detail-item"><strong>Email:</strong> <?php echo htmlspecialchars($alumno['email']); ?></div>
            <div class="detail-item"><strong>Teléfono:</strong> <?php echo htmlspecialchars($alumno['telefono']); ?></div>
            <div class="detail-item"><strong>Fecha Nacimiento:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($alumno['fecha_nacimiento']))); ?></div>
            <div class="detail-item"><strong>Grupo Sanguíneo:</strong> <?php echo htmlspecialchars($alumno['grupo_sanguineo']); ?></div>
            <div class="detail-item"><strong>Dirección:</strong> <?php echo htmlspecialchars($alumno['direccion']); ?></div>
            <div class="detail-item"><strong>Tutor:</strong> <?php echo htmlspecialchars($alumno['nombre_padre_tutor']); ?></div>
            <div class="detail-item"><strong>Tel. Emergencia:</strong> <?php echo htmlspecialchars($alumno['telefono_emergencia']); ?></div>
        </div>
    </div>

    <h3>Cursos Matriculados</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID Matrícula</th>
                <th>Curso</th>
                <th>Profesor</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Descuento</th>
                <th>Precio Final</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($matriculas)): ?>
                <tr>
                    <td colspan="9">Este cliente no tiene matrículas registradas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($matriculas as $matricula): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($matricula['id_matricula']); ?></td>
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
                        <td>
                            <a href="index.php?url=matriculas/show&id=<?php echo $matricula['id_matricula']; ?>" class="btn btn-info">Ver Matrícula</a>
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
