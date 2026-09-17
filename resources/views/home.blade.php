@extends('adminlte::page')
@section('title', 'Dashboard')
@section('content_header')@stop

@section('content')

{{-- ================================================================
     Riga 1 – KPI cards
     ================================================================ --}}
<div class="row">
    <div class="col-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ordini Cliente</span>
                <span class="info-box-number">{{ $ordersTotal }}</span>
                <div class="progress"><div class="progress-bar bg-info" style="width:100%"></div></div>
                <span class="progress-description">{{ $ordersCreated }} creati &middot; {{ $ordersShipped }} spediti</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-industry"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Produzione</span>
                <span class="info-box-number">{{ $prodTotal }}</span>
                <div class="progress"><div class="progress-bar bg-warning" style="width:100%"></div></div>
                <span class="progress-description">{{ $prodInProcess }} in lav. &middot; {{ $prodCompleted }} completati</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Spedizioni</span>
                <span class="info-box-number">{{ $shipTotal }}</span>
                <div class="progress"><div class="progress-bar bg-success" style="width:100%"></div></div>
                <span class="progress-description">{{ $shipCreated }} in corso &middot; {{ $shipShipped }} spedite</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-box {{ $negativeStocks->isNotEmpty() ? 'bg-danger' : '' }}">
            <span class="info-box-icon {{ $negativeStocks->isNotEmpty() ? 'bg-danger' : 'bg-secondary' }}">
                <i class="fas fa-exclamation-triangle"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Stock Negativi</span>
                <span class="info-box-number">{{ $negativeStocks->count() }}</span>
                <div class="progress"><div class="progress-bar {{ $negativeStocks->isNotEmpty() ? 'bg-danger' : 'bg-secondary' }}" style="width:100%"></div></div>
                <span class="progress-description">prodotti sotto zero</span>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     Riga 1b – KPI secondari
     ================================================================ --}}
<div class="row">
    {{-- Produzioni completate questo mese --}}
    <div class="col-6 col-md-3">
        <div class="small-box bg-teal">
            <div class="inner">
                <h3>{{ $prodThisMonth }}</h3>
                <p>Prod. completate questo mese</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div class="small-box-footer">
                Mese scorso: <strong>{{ $prodLastMonth }}</strong>
                @if($prodThisMonth > $prodLastMonth)
                    <i class="fas fa-arrow-up ml-1"></i>
                @elseif($prodThisMonth < $prodLastMonth)
                    <i class="fas fa-arrow-down ml-1"></i>
                @endif
            </div>
        </div>
    </div>
    {{-- Ordini in attesa (non spediti) --}}
    <div class="col-6 col-md-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $ordersTotal - $ordersShipped }}</h3>
                <p>Ordini aperti (non spediti)</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="{{ route('customer-orders.index') }}" class="small-box-footer">
                Vai agli ordini <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    {{-- Ordini in ritardo --}}
    <div class="col-6 col-md-3">
        <div class="small-box {{ $lateOrders->isNotEmpty() ? 'bg-danger' : 'bg-secondary' }}">
            <div class="inner">
                <h3>{{ $lateOrders->count() }}</h3>
                <p>Ordini in ritardo</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <span class="small-box-footer">data consegna superata</span>
        </div>
    </div>
    {{-- Spedizioni aperte --}}
    <div class="col-6 col-md-3">
        <div class="small-box bg-indigo">
            <div class="inner">
                <h3>{{ $shipCreated }}</h3>
                <p>Spedizioni in corso</p>
            </div>
            <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            <a href="{{ route('shipments.index') }}" class="small-box-footer">
                Vai alle spedizioni <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- ================================================================
     Riga avvisi – checklist azioni pendenti
     ================================================================ --}}
@if($alerts->isNotEmpty())
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell mr-1 text-danger"></i>
                    Avvisi &amp; Azioni Pendenti
                    <span class="badge badge-danger ml-1">{{ $alerts->count() }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="alertsAccordion">
                    @foreach($alerts as $i => $alert)
                    @php
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
                    @endphp
                    <div class="card mb-0 border-0 border-bottom">
                        <div class="card-header p-0" id="alertHead{{ $i }}">
                            <button class="btn btn-block text-left d-flex align-items-center px-3 py-2"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#alertCollapse{{ $i }}"
                                    aria-expanded="{{ $i === 0 ? 'true' : 'false' }}"
                                    aria-controls="alertCollapse{{ $i }}">
                                <i class="{{ $alert['icon'] }} mr-2 text-{{ $alert['level'] === 'secondary' ? 'muted' : $alert['level'] }}"></i>
                                <span class="flex-grow-1 font-weight-bold">{{ $alert['title'] }}</span>
                                <span class="badge {{ $badgeClass }} ml-2">{{ $alert['items']->count() }}</span>
                                <i class="fas fa-chevron-down ml-2 small"></i>
                            </button>
                        </div>
                        <div id="alertCollapse{{ $i }}"
                             class="collapse {{ $i === 0 ? 'show' : '' }}"
                             aria-labelledby="alertHead{{ $i }}"
                             data-parent="#alertsAccordion">
                            <ul class="list-group list-group-flush">
                                @foreach($alert['items'] as $item)
                                <li class="list-group-item {{ $colClass }} py-1 px-4 d-flex align-items-center">
                                    <i class="fas fa-arrow-right mr-2 small"></i>
                                    <a href="{{ $item['url'] }}" class="text-dark">{{ $item['label'] }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ================================================================
     Riga 2 – Grafici principali
     ================================================================ --}}<div class="row">
    {{-- Movimenti 30 giorni --}}
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
    {{-- Donut ordini per stato --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Ordini per Stato</h5>
            </div>
            <div class="card-body d-flex justify-content-center pb-0">
                <canvas id="chartOrdersState" style="max-height:200px;"></canvas>
            </div>
            <div class="card-footer p-2">
                @foreach($ordersByState as $label => $count)
                <div class="d-flex justify-content-between small px-2">
                    <span>{{ $label }}</span><strong>{{ $count }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     Riga 3 – Trend ordini + Spedizioni per mese
     ================================================================ --}}
<div class="row">
    {{-- Ordini cliente per mese --}}
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
    {{-- Spedizioni per mese --}}
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

{{-- ================================================================
     Riga 4 – Top prodotti prodotti + Stock per magazzino
     ================================================================ --}}
<div class="row">
    {{-- Top 5 prodotti prodotti --}}
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
    {{-- Stock totale per magazzino --}}
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

{{-- ================================================================
     Riga 5 – Donut prodotti per tipo + Fabbisogno materie prime
     ================================================================ --}}
<div class="row">
    {{-- Prodotti per tipo --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">Prodotti per Tipo</h5>
            </div>
            <div class="card-body d-flex justify-content-center pb-0">
                <canvas id="chartProductsType" style="max-height:200px;"></canvas>
            </div>
            <div class="card-footer p-2">
                @foreach($productsByType as $label => $count)
                <div class="d-flex justify-content-between small px-2">
                    <span>{{ $label }}</span><strong>{{ $count }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    {{-- Fabbisogno materie prime --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">
                    <i class="fas fa-cubes mr-1 text-primary"></i>
                    Top 10 Fabbisogno Materie Prime (ordini aperti)
                </h5>
            </div>
            <div class="card-body p-0">
                @if($rawMaterialNeeds->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Nessun fabbisogno calcolato.</p>
                @else
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Prodotto</th>
                            <th class="text-right">Fabbisogno totale</th>
                            <th>U.M.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rawMaterialNeeds as $n)
                        <tr>
                            <td>{{ $n->product?->name ?? '-' }}</td>
                            <td class="text-right font-weight-bold">{{ number_format((float)$n->total, 2, ',', '.') }}</td>
                            <td>{{ $n->unitOfMeasure?->symbol ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     Riga 6 – Produzioni in lavorazione + Ordini in ritardo
     ================================================================ --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">
                    <i class="fas fa-industry mr-1 text-warning"></i>
                    Produzioni in Lavorazione
                    <span class="badge badge-warning ml-1">{{ $inProcessOrders->count() }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if($inProcessOrders->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Nessuna produzione in corso.</p>
                @else
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Progressivo</th><th>Magazzino</th><th>Data</th><th>Avanzamento</th></tr>
                    </thead>
                    <tbody>
                        @foreach($inProcessOrders as $po)
                        @php $pct = $po['pct']; $cls = $pct>=100?'bg-success':($pct>0?'bg-info progress-bar-striped':'bg-secondary'); @endphp
                        <tr>
                            <td><a href="/production-orders/{{ $po['id'] }}">{{ $po['progressive'] }}</a></td>
                            <td>{{ $po['warehouse'] }}</td>
                            <td>{{ $po['date'] }}</td>
                            <td style="min-width:110px;">
                                <div class="progress" style="height:18px;">
                                    <div class="progress-bar {{ $cls }}" style="width:{{ max($pct,15) }}%; min-width:2em;"
                                         aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">{{ $pct }}%</div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card {{ $lateOrders->isNotEmpty() ? 'border-danger' : '' }}">
            <div class="card-header pb-0 {{ $lateOrders->isNotEmpty() ? 'bg-danger text-white' : '' }}">
                <h5 class="mb-0">
                    <i class="fas fa-clock mr-1"></i> Ordini in Ritardo
                    @if($lateOrders->isNotEmpty())
                        <span class="badge badge-light ml-1">{{ $lateOrders->count() }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                @if($lateOrders->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Nessun ordine in ritardo.</p>
                @else
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Progressivo</th><th>Cliente</th><th>Data Ordine</th><th>Stato</th></tr>
                    </thead>
                    <tbody>
                        @foreach($lateOrders as $o)
                        <tr>
                            <td><a href="/customer-orders/{{ $o->id }}">{{ $o->progressive }}</a></td>
                            <td>{{ $o->user?->name ?? '-' }}</td>
                            <td class="text-danger font-weight-bold">{{ $o->order_date?->format('d/m/Y') }}</td>
                            <td><span class="badge badge-secondary">{{ $o->stateLabel() }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     Riga 7 – Top stock + Stock negativi
     ================================================================ --}}
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0"><i class="fas fa-boxes mr-1 text-success"></i> Top 10 Stock</h5>
            </div>
            <div class="card-body p-0">
                @if($topStocks->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Nessun prodotto in stock.</p>
                @else
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Prodotto</th><th class="text-right">Quantità</th><th>U.M.</th></tr>
                    </thead>
                    <tbody>
                        @foreach($topStocks as $s)
                        <tr>
                            <td>{{ $s->product?->name ?? '-' }}</td>
                            <td class="text-right">{{ number_format((float)$s->qnt, 2, ',', '.') }}</td>
                            <td>{{ $s->unitOfMeasure?->symbol ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card {{ $negativeStocks->isNotEmpty() ? 'border-danger' : '' }}">
            <div class="card-header pb-0">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle mr-1 {{ $negativeStocks->isNotEmpty() ? 'text-danger' : 'text-muted' }}"></i>
                    Stock Negativi
                </h5>
            </div>
            <div class="card-body p-0">
                @if($negativeStocks->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Nessun prodotto con stock negativo.</p>
                @else
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Prodotto</th><th>Magazzino</th><th class="text-right">Quantità</th><th>U.M.</th></tr>
                    </thead>
                    <tbody>
                        @foreach($negativeStocks as $s)
                        <tr>
                            <td>{{ $s->product?->name ?? '-' }}</td>
                            <td>{{ $s->warehouse?->name ?? '-' }}</td>
                            <td class="text-right text-danger font-weight-bold">{{ number_format((float)$s->qnt, 2, ',', '.') }}</td>
                            <td>{{ $s->unitOfMeasure?->symbol ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {

    // ── Dati dal server ───────────────────────────────────────────────────
    var movDays    = {!! json_encode($days->values()) !!};
    var loads      = {!! json_encode(array_values($loadsByDay->toArray())) !!};
    var unloads    = {!! json_encode(array_values($unloadsByDay->toArray())) !!};

    var movLabels  = movDays.map(function (d) { var p=d.split('-'); return p[2]+'/'+p[1]; });

    var orderMonthLabels  = {!! json_encode($orderMonthLabels->values()) !!};
    var ordersPerMonth    = {!! json_encode($ordersPerMonth->values()) !!};

    var shipMonthLabels   = {!! json_encode($shipMonthLabels->values()) !!};
    var shipmentsPerMonth = {!! json_encode($shipmentsPerMonth->values()) !!};

    var stateLabels = {!! json_encode(array_keys($ordersByState)) !!};
    var stateCounts = {!! json_encode(array_values($ordersByState)) !!};

    var topProdLabels = {!! json_encode($topProduced->map(fn($r) => $r->product?->name ?? '?')->values()) !!};
    var topProdData   = {!! json_encode($topProduced->map(fn($r) => round((float)$r->total_produced, 2))->values()) !!};

    var whLabels = {!! json_encode($stockByWarehouse->pluck('name')) !!};
    var whData   = {!! json_encode($stockByWarehouse->pluck('total')) !!};

    var ptLabels = {!! json_encode(array_keys($productsByType)) !!};
    var ptData   = {!! json_encode(array_values($productsByType)) !!};

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
            datasets: [{ data: stateCounts, backgroundColor:['#6c757d','#17a2b8','#ffc107','#6f42c1','#28a745'], borderWidth:1 }],
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
@stop
