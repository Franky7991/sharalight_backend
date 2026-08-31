
<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content_header'); ?><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="row">
    <div class="col-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ordini Cliente</span>
                <span class="info-box-number"><?php echo e($ordersTotal); ?></span>
                <div class="progress"><div class="progress-bar bg-info" style="width:100%"></div></div>
                <span class="progress-description"><?php echo e($ordersCreated); ?> creati &middot; <?php echo e($ordersShipped); ?> spediti</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-industry"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Produzione</span>
                <span class="info-box-number"><?php echo e($prodTotal); ?></span>
                <div class="progress"><div class="progress-bar bg-warning" style="width:100%"></div></div>
                <span class="progress-description"><?php echo e($prodInProcess); ?> in lav. &middot; <?php echo e($prodCompleted); ?> completati</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Spedizioni</span>
                <span class="info-box-number"><?php echo e($shipTotal); ?></span>
                <div class="progress"><div class="progress-bar bg-success" style="width:100%"></div></div>
                <span class="progress-description"><?php echo e($shipCreated); ?> in corso &middot; <?php echo e($shipShipped); ?> spedite</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box <?php echo e($negativeStocks->isNotEmpty() ? 'bg-danger' : ''); ?>">
            <span class="info-box-icon <?php echo e($negativeStocks->isNotEmpty() ? 'bg-danger' : 'bg-secondary'); ?>">
                <i class="fas fa-exclamation-triangle"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Stock Negativi</span>
                <span class="info-box-number"><?php echo e($negativeStocks->count()); ?></span>
                <div class="progress"><div class="progress-bar <?php echo e($negativeStocks->isNotEmpty() ? 'bg-danger' : 'bg-secondary'); ?>" style="width:100%"></div></div>
                <span class="progress-description">prodotti sotto zero</span>
            </div>
        </div>
    </div>
</div>


<div class="row">
    
    <div class="col-6 col-md-3">
        <div class="small-box bg-teal">
            <div class="inner">
                <h3><?php echo e($prodThisMonth); ?></h3>
                <p>Prod. completate questo mese</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div class="small-box-footer">
                Mese scorso: <strong><?php echo e($prodLastMonth); ?></strong>
                <?php if($prodThisMonth > $prodLastMonth): ?>
                    <i class="fas fa-arrow-up ml-1"></i>
                <?php elseif($prodThisMonth < $prodLastMonth): ?>
                    <i class="fas fa-arrow-down ml-1"></i>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-md-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?php echo e($ordersTotal - $ordersShipped); ?></h3>
                <p>Ordini aperti (non spediti)</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="<?php echo e(route('customer-orders.index')); ?>" class="small-box-footer">
                Vai agli ordini <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-6 col-md-3">
        <div class="small-box <?php echo e($lateOrders->isNotEmpty() ? 'bg-danger' : 'bg-secondary'); ?>">
            <div class="inner">
                <h3><?php echo e($lateOrders->count()); ?></h3>
                <p>Ordini in ritardo</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <span class="small-box-footer">data consegna superata</span>
        </div>
    </div>
    
    <div class="col-6 col-md-3">
        <div class="small-box bg-indigo">
            <div class="inner">
                <h3><?php echo e($shipCreated); ?></h3>
                <p>Spedizioni in corso</p>
            </div>
            <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            <a href="<?php echo e(route('shipments.index')); ?>" class="small-box-footer">
                Vai alle spedizioni <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>


<?php if($alerts->isNotEmpty()): ?>
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell mr-1 text-danger"></i>
                    Avvisi &amp; Azioni Pendenti
                    <span class="badge badge-danger ml-1"><?php echo e($alerts->count()); ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="alertsAccordion">
                    <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $colClass = match($alert['level']) {
                            'danger'    => 'list-group-item-danger',
                            'warning'   => 'list-group-item-warning',
                            'info'      => 'list-group-item-info',
                            default     => 'list-group-item-secondary',
                        };
                        $badgeClass = match($alert['level']) {
                            'danger'  => 'badge-danger',
                            'warning' => 'badge-warning',
                            'info'    => 'badge-info',
                            default   => 'badge-secondary',
                        };
                    ?>
                    <div class="card mb-0 border-0 border-bottom">
                        <div class="card-header p-0" id="alertHead<?php echo e($i); ?>">
                            <button class="btn btn-block text-left d-flex align-items-center px-3 py-2"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#alertCollapse<?php echo e($i); ?>"
                                    aria-expanded="<?php echo e($i === 0 ? 'true' : 'false'); ?>"
                                    aria-controls="alertCollapse<?php echo e($i); ?>">
                                <i class="<?php echo e($alert['icon']); ?> mr-2 text-<?php echo e($alert['level'] === 'secondary' ? 'muted' : $alert['level']); ?>"></i>
                                <span class="flex-grow-1 font-weight-bold"><?php echo e($alert['title']); ?></span>
                                <span class="badge <?php echo e($badgeClass); ?> ml-2"><?php echo e($alert['items']->count()); ?></span>
                                <i class="fas fa-chevron-down ml-2 small"></i>
                            </button>
                        </div>
                        <div id="alertCollapse<?php echo e($i); ?>"
                             class="collapse <?php echo e($i === 0 ? 'show' : ''); ?>"
                             aria-labelledby="alertHead<?php echo e($i); ?>"
                             data-parent="#alertsAccordion">
                            <ul class="list-group list-group-flush">
                                <?php $__currentLoopData = $alert['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="list-group-item <?php echo e($colClass); ?> py-1 px-4 d-flex align-items-center">
                                    <i class="fas fa-arrow-right mr-2 small"></i>
                                    <a href="<?php echo e($item['url']); ?>" class="text-dark"><?php echo e($item['label']); ?></a>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Movimenti – ultimi 30 giorni</h5>
            </div>
            <div class="card-body">
                <canvas id="chartMovements" height="90"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Ordini per Stato</h5>
            </div>
            <div class="card-body d-flex justify-content-center pb-0">
                <canvas id="chartOrdersState" style="max-height:200px;"></canvas>
            </div>
            <div class="card-footer p-2">
                <?php $__currentLoopData = $ordersByState; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex justify-content-between small px-2">
                    <span><?php echo e($label); ?></span><strong><?php echo e($count); ?></strong>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>


<div class="row">
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Ordini Cliente – ultimi 12 mesi</h5>
            </div>
            <div class="card-body">
                <canvas id="chartOrdersMonth" height="120"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Spedizioni – ultimi 6 mesi</h5>
            </div>
            <div class="card-body">
                <canvas id="chartShipmentsMonth" height="120"></canvas>
            </div>
        </div>
    </div>
</div>


<div class="row">
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0"><i class="fas fa-trophy mr-1 text-warning"></i> Top 5 Prodotti Prodotti</h5>
            </div>
            <div class="card-body">
                <canvas id="chartTopProduced" height="140"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0"><i class="fas fa-warehouse mr-1 text-success"></i> Stock per Magazzino</h5>
            </div>
            <div class="card-body">
                <canvas id="chartStockWarehouse" height="140"></canvas>
            </div>
        </div>
    </div>
</div>


<div class="row">
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Prodotti per Tipo</h5>
            </div>
            <div class="card-body d-flex justify-content-center pb-0">
                <canvas id="chartProductsType" style="max-height:200px;"></canvas>
            </div>
            <div class="card-footer p-2">
                <?php $__currentLoopData = $productsByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex justify-content-between small px-2">
                    <span><?php echo e($label); ?></span><strong><?php echo e($count); ?></strong>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">
                    <i class="fas fa-cubes mr-1 text-primary"></i>
                    Top 10 Fabbisogno Materie Prime (ordini aperti)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if($rawMaterialNeeds->isEmpty()): ?>
                    <p class="text-muted text-center py-3 mb-0">Nessun fabbisogno calcolato.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Prodotto</th>
                            <th class="text-right">Fabbisogno totale</th>
                            <th>U.M.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rawMaterialNeeds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($n->product?->name ?? '-'); ?></td>
                            <td class="text-right font-weight-bold"><?php echo e(number_format((float)$n->total, 2, ',', '.')); ?></td>
                            <td><?php echo e($n->unitOfMeasure?->symbol ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">
                    <i class="fas fa-industry mr-1 text-warning"></i>
                    Produzioni in Lavorazione
                    <span class="badge badge-warning ml-1"><?php echo e($inProcessOrders->count()); ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if($inProcessOrders->isEmpty()): ?>
                    <p class="text-muted text-center py-3 mb-0">Nessuna produzione in corso.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Progressivo</th><th>Magazzino</th><th>Data</th><th>Avanzamento</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $inProcessOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $pct = $po['pct']; $cls = $pct>=100?'bg-success':($pct>0?'bg-info progress-bar-striped':'bg-secondary'); ?>
                        <tr>
                            <td><a href="/production-orders/<?php echo e($po['id']); ?>"><?php echo e($po['progressive']); ?></a></td>
                            <td><?php echo e($po['warehouse']); ?></td>
                            <td><?php echo e($po['date']); ?></td>
                            <td style="min-width:110px;">
                                <div class="progress" style="height:18px;">
                                    <div class="progress-bar <?php echo e($cls); ?>" style="width:<?php echo e(max($pct,15)); ?>%; min-width:2em;"
                                         aria-valuenow="<?php echo e($pct); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($pct); ?>%</div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card <?php echo e($lateOrders->isNotEmpty() ? 'border-danger' : ''); ?>">
            <div class="card-header pb-0 <?php echo e($lateOrders->isNotEmpty() ? 'bg-danger text-white' : ''); ?>">
                <h5 class="mb-0">
                    <i class="fas fa-clock mr-1"></i> Ordini in Ritardo
                    <?php if($lateOrders->isNotEmpty()): ?>
                        <span class="badge badge-light ml-1"><?php echo e($lateOrders->count()); ?></span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if($lateOrders->isEmpty()): ?>
                    <p class="text-muted text-center py-3 mb-0">Nessun ordine in ritardo.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Progressivo</th><th>Cliente</th><th>Data Ordine</th><th>Stato</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $lateOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><a href="/customer-orders/<?php echo e($o->id); ?>"><?php echo e($o->progressive); ?></a></td>
                            <td><?php echo e($o->user?->name ?? '-'); ?></td>
                            <td class="text-danger font-weight-bold"><?php echo e($o->order_date?->format('d/m/Y')); ?></td>
                            <td><span class="badge badge-secondary"><?php echo e($o->stateLabel()); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0"><i class="fas fa-boxes mr-1 text-success"></i> Top 10 Stock</h5>
            </div>
            <div class="card-body p-0">
                <?php if($topStocks->isEmpty()): ?>
                    <p class="text-muted text-center py-3 mb-0">Nessun prodotto in stock.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Prodotto</th><th class="text-right">Quantità</th><th>U.M.</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $topStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($s->product?->name ?? '-'); ?></td>
                            <td class="text-right"><?php echo e(number_format((float)$s->qnt, 2, ',', '.')); ?></td>
                            <td><?php echo e($s->unitOfMeasure?->symbol ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card <?php echo e($negativeStocks->isNotEmpty() ? 'border-danger' : ''); ?>">
            <div class="card-header pb-0">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle mr-1 <?php echo e($negativeStocks->isNotEmpty() ? 'text-danger' : 'text-muted'); ?>"></i>
                    Stock Negativi
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if($negativeStocks->isEmpty()): ?>
                    <p class="text-muted text-center py-3 mb-0">Nessun prodotto con stock negativo.</p>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Prodotto</th><th>Magazzino</th><th class="text-right">Quantità</th><th>U.M.</th></tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $negativeStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($s->product?->name ?? '-'); ?></td>
                            <td><?php echo e($s->warehouse?->name ?? '-'); ?></td>
                            <td class="text-right text-danger font-weight-bold"><?php echo e(number_format((float)$s->qnt, 2, ',', '.')); ?></td>
                            <td><?php echo e($s->unitOfMeasure?->symbol ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {

    // ── Dati dal server ───────────────────────────────────────────────────
    var movDays    = <?php echo json_encode($days->values()); ?>;
    var loads      = <?php echo json_encode(array_values($loadsByDay->toArray())); ?>;
    var unloads    = <?php echo json_encode(array_values($unloadsByDay->toArray())); ?>;

    var movLabels  = movDays.map(function (d) { var p=d.split('-'); return p[2]+'/'+p[1]; });

    var orderMonthLabels  = <?php echo json_encode($orderMonthLabels->values()); ?>;
    var ordersPerMonth    = <?php echo json_encode($ordersPerMonth->values()); ?>;

    var shipMonthLabels   = <?php echo json_encode($shipMonthLabels->values()); ?>;
    var shipmentsPerMonth = <?php echo json_encode($shipmentsPerMonth->values()); ?>;

    var stateLabels = <?php echo json_encode(array_keys($ordersByState)); ?>;
    var stateCounts = <?php echo json_encode(array_values($ordersByState)); ?>;

    var topProdLabels = <?php echo json_encode($topProduced->map(fn($r) => $r->product?->name ?? '?')->values()); ?>;
    var topProdData   = <?php echo json_encode($topProduced->map(fn($r) => round((float)$r->total_produced, 2))->values()); ?>;

    var whLabels = <?php echo json_encode($stockByWarehouse->pluck('name')); ?>;
    var whData   = <?php echo json_encode($stockByWarehouse->pluck('total')); ?>;

    var ptLabels = <?php echo json_encode(array_keys($productsByType)); ?>;
    var ptData   = <?php echo json_encode(array_values($productsByType)); ?>;

    // ── Palette colori ────────────────────────────────────────────────────
    var palette = ['#007bff','#28a745','#ffc107','#dc3545','#17a2b8','#6f42c1','#fd7e14','#20c997'];

    // ── 1. Movimenti 30 giorni ────────────────────────────────────────────
    new Chart(document.getElementById('chartMovements'), {
        type: 'bar',
        data: {
            labels: movLabels,
            datasets: [
                { label: 'Carichi',  data: loads,   backgroundColor: 'rgba(40,167,69,.7)',  borderColor: 'rgba(40,167,69,1)',  borderWidth:1 },
                { label: 'Scarichi', data: unloads, backgroundColor: 'rgba(220,53,69,.7)', borderColor: 'rgba(220,53,69,1)', borderWidth:1 },
            ],
        },
        options: { responsive:true, plugins:{ legend:{position:'top'} }, scales:{ y:{beginAtZero:true} } },
    });

    // ── 2. Donut ordini per stato ─────────────────────────────────────────
    new Chart(document.getElementById('chartOrdersState'), {
        type: 'doughnut',
        data: {
            labels: stateLabels,
            datasets: [{ data: stateCounts, backgroundColor:['#6c757d','#17a2b8','#ffc107','#28a745'], borderWidth:1 }],
        },
        options: { responsive:true, plugins:{ legend:{position:'bottom'} }, cutout:'65%' },
    });

    // ── 3. Ordini per mese (line) ─────────────────────────────────────────
    new Chart(document.getElementById('chartOrdersMonth'), {
        type: 'line',
        data: {
            labels: orderMonthLabels,
            datasets: [{
                label: 'Ordini', data: ordersPerMonth,
                borderColor: '#007bff', backgroundColor: 'rgba(0,123,255,.15)',
                tension: 0.3, fill: true, pointRadius: 4,
            }],
        },
        options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true, ticks:{stepSize:1}} } },
    });

    // ── 4. Spedizioni per mese (bar) ──────────────────────────────────────
    new Chart(document.getElementById('chartShipmentsMonth'), {
        type: 'bar',
        data: {
            labels: shipMonthLabels,
            datasets: [{
                label: 'Spedizioni', data: shipmentsPerMonth,
                backgroundColor: 'rgba(40,167,69,.7)', borderColor:'rgba(40,167,69,1)', borderWidth:1,
            }],
        },
        options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true, ticks:{stepSize:1}} } },
    });

    // ── 5. Top 5 prodotti prodotti (horizontal bar) ───────────────────────
    new Chart(document.getElementById('chartTopProduced'), {
        type: 'bar',
        data: {
            labels: topProdLabels,
            datasets: [{
                label: 'Quantità prodotta', data: topProdData,
                backgroundColor: palette.slice(0, topProdData.length),
                borderWidth: 1,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display:false } },
            scales: { x: { beginAtZero:true } },
        },
    });

    // ── 6. Stock per magazzino (bar) ──────────────────────────────────────
    new Chart(document.getElementById('chartStockWarehouse'), {
        type: 'bar',
        data: {
            labels: whLabels,
            datasets: [{
                label: 'Qtà totale', data: whData,
                backgroundColor: 'rgba(23,162,184,.7)', borderColor:'rgba(23,162,184,1)', borderWidth:1,
            }],
        },
        options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true} } },
    });

    // ── 7. Donut prodotti per tipo ────────────────────────────────────────
    new Chart(document.getElementById('chartProductsType'), {
        type: 'doughnut',
        data: {
            labels: ptLabels,
            datasets: [{ data: ptData, backgroundColor:['#6c757d','#ffc107','#28a745'], borderWidth:1 }],
        },
        options: { responsive:true, plugins:{ legend:{position:'bottom'} }, cutout:'60%' },
    });

})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\shara_light\backend\resources\views\home.blade.php ENDPATH**/ ?>