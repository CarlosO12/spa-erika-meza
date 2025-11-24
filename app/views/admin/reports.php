<?php
requireAdmin();

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/controllers/AdminController.php';

$database = new Database();
$db = $database->getConnection();
$adminController = new AdminController();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$revenueReport = $adminController->generateRevenueReport($startDate, $endDate);
$servicesReport = $adminController->generateServicesReport($startDate, $endDate);

$totalRevenue = 0;
$totalAppointments = 0;
foreach ($revenueReport as $day) {
    $totalRevenue += $day['ingresos'];
    $totalAppointments += $day['total_citas'];
}

$mostPopular = !empty($servicesReport) ? $servicesReport[0] : null;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-gradient">
                <i class="bi bi-graph-up"></i> Reportes y Estadísticas
            </h1>
            <p class="text-muted">Analiza el rendimiento de tu negocio</p>
        </div>
    </div>
    
    <!-- Selector de Rango de Fechas -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin-reports">
                
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo $startDate; ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo $endDate; ?>" required>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Generar Reporte
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetDates()"
                        title="Refrescar Fechas">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                
                <div class="col-md-3 text-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                            <i class="bi bi-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                            <i class="bi bi-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-info" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumen General -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Ingresos Totales</p>
                            <h3 class="fw-bold mb-0 text-primary">
                                <?php echo formatPrice($totalRevenue); ?>
                            </h3>
                            <small class="text-muted">
                                <?php echo formatDate($startDate); ?> - <?php echo formatDate($endDate); ?>
                            </small>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Citas Completadas</p>
                            <h3 class="fw-bold mb-0 text-success">
                                <?php echo $totalAppointments; ?>
                            </h3>
                            <small class="text-muted">En el período seleccionado</small>
                        </div>
                        <div class="stats-icon bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Promedio por Cita</p>
                            <h3 class="fw-bold mb-0 text-info">
                                <?php 
                                $avg = $totalAppointments > 0 ? $totalRevenue / $totalAppointments : 0;
                                echo formatPrice($avg); 
                                ?>
                            </h3>
                            <small class="text-muted">Ticket promedio</small>
                        </div>
                        <div class="stats-icon bg-info">
                            <i class="bi bi-calculator"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Servicio Top</p>
                            <h6 class="fw-bold mb-0 text-warning">
                                <?php echo $mostPopular ? e($mostPopular['nombre']) : 'N/A'; ?>
                            </h6>
                            <small class="text-muted">
                                <?php echo $mostPopular ? $mostPopular['total_citas'] . ' citas' : ''; ?>
                            </small>
                        </div>
                        <div class="stats-icon bg-warning">
                            <i class="bi bi-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-bar-chart"></i> Ingresos por Día
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-pie-chart"></i> Distribución
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reporte de Servicios -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-list-check"></i> Reporte por Servicios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Total Citas</th>
                                    <th>Ingresos Estimados</th>
                                    <th>% del Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicesReport as $service): ?>
                                <?php
                                $serviceRevenue = $service['precio'] * $service['total_citas'];
                                $percentage = $totalRevenue > 0 ? ($serviceRevenue / $totalRevenue) * 100 : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo e($service['nombre']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo e($service['categoria']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatPrice($service['precio']); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $service['total_citas']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?php echo formatPrice($serviceRevenue); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?php echo $percentage; ?>%"
                                                 aria-valuenow="<?php echo $percentage; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo number_format($percentage, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($service['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td>
                                        <strong class="badge bg-primary">
                                            <?php echo $totalAppointments; ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?php echo formatPrice($totalRevenue); ?>
                                        </strong>
                                    </td>
                                    <td><strong>100%</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reporte Detallado por Fecha -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-calendar-week"></i> Ingresos Diarios Detallados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Día</th>
                                    <th>Citas Completadas</th>
                                    <th>Ingresos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($revenueReport as $day): ?>
                                <tr>
                                    <td><?php echo formatDate($day['fecha']); ?></td>
                                    <td>
                                        <?php 
                                        $dayOfWeek = date('w', strtotime($day['fecha']));
                                        echo DAYS_OF_WEEK[$dayOfWeek];
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $day['total_citas']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?php echo formatPrice($day['ingresos']); ?>
                                        </strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const revenueData = <?php echo json_encode($revenueReport); ?>;
const servicesData = <?php echo json_encode($servicesReport); ?>;

// Gráfico de Ingresos por Día
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => new Date(d.fecha).toLocaleDateString('es-CO', {month: 'short', day: 'numeric'})),
        datasets: [{
            label: 'Ingresos',
            data: revenueData.map(d => d.ingresos),
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Ingresos: ' + new Intl.NumberFormat('es-CO', {
                            style: 'currency',
                            currency: 'COP',
                            minimumFractionDigits: 0
                        }).format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-CO');
                    }
                }
            }
        }
    }
});

// Gráfico de Distribución por Categorías
const categories = {};
servicesData.forEach(service => {
    if (!categories[service.categoria]) {
        categories[service.categoria] = 0;
    }
    categories[service.categoria] += service.total_citas;
});

const distributionCtx = document.getElementById('distributionChart').getContext('2d');
const distributionChart = new Chart(distributionCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(categories),
        datasets: [{
            data: Object.values(categories),
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(72, 187, 120, 0.8)',
                'rgba(66, 153, 225, 0.8)',
                'rgba(237, 137, 54, 0.8)',
                'rgba(245, 101, 101, 0.8)',
                'rgba(159, 122, 234, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function resetDates() {
    document.getElementById('start_date').value = '<?php echo date('Y-m-01'); ?>';
    document.getElementById('end_date').value = '<?php echo date('Y-m-d'); ?>';
}

function exportReport(format) {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (format === 'excel') {
        window.location.href = `<?php echo BASE_URL; ?>/index.php?action=export-csv&type=appointments&start=${startDate}&end=${endDate}`;
    } else if (format === 'pdf') {
        window.location.href = `<?php echo BASE_URL; ?>/index.php?action=export-pdf&type=appointments&start=${startDate}&end=${endDate}`;
    }
}
</script>

<style>
@media print {
    .btn, .card-header, nav, footer {
        display: none !important;
    }
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
}
</style>