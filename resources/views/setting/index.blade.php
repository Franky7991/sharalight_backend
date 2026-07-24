@extends('adminlte::page')
@section('title', 'Impostazioni')
@section('content_header')@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Impostazioni</h4>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- ── Sezione Magazzino ──────────────────────────────────────── --}}
                    <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size:.7rem; letter-spacing:.08em;">
                        <i class="fas fa-warehouse mr-1"></i> Magazzino
                    </h6>

                    @php $key = \App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL; @endphp
                    <div class="form-group">
                        <label for="{{ $key }}">Causale <em>Carico in Magazzino</em></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-arrow-circle-down text-success"></i></span>
                            </div>
                            <select id="{{ $key }}" name="{{ $key }}"
                                class="form-control @error($key) is-invalid @enderror">
                                <option value="">— Nessuna —</option>
                                @foreach($loadCausals as $c)
                                    <option value="{{ $c->id }}" {{ ($settings[$key] ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="form-text text-muted">Causale di default per i carichi manuali in magazzino.</small>
                    </div>

                    <hr>

                    {{-- ── Sezione Produzione ─────────────────────────────────────── --}}
                    <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size:.7rem; letter-spacing:.08em;">
                        <i class="fas fa-industry mr-1"></i> Produzione
                    </h6>

                    @php $key = \App\Models\Setting::KEY_PRODUCTION_UNLOAD_CAUSAL; @endphp
                    <div class="form-group">
                        <label for="{{ $key }}">Causale <em>Scarico per Produzione</em></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-arrow-circle-up text-danger"></i></span>
                            </div>
                            <select id="{{ $key }}" name="{{ $key }}"
                                class="form-control @error($key) is-invalid @enderror">
                                <option value="">— Nessuna —</option>
                                @foreach($unloadCausals as $c)
                                    <option value="{{ $c->id }}" {{ ($settings[$key] ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="form-text text-muted">Causale usata per scaricare le materie prime durante la produzione.</small>
                    </div>

                    @php $key = \App\Models\Setting::KEY_PRODUCTION_LOAD_CAUSAL; @endphp
                    <div class="form-group">
                        <label for="{{ $key }}">Causale <em>Carico per Produzione</em></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-arrow-circle-down text-success"></i></span>
                            </div>
                            <select id="{{ $key }}" name="{{ $key }}"
                                class="form-control @error($key) is-invalid @enderror">
                                <option value="">— Nessuna —</option>
                                @foreach($loadCausals as $c)
                                    <option value="{{ $c->id }}" {{ ($settings[$key] ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <small class="form-text text-muted">Causale usata per caricare il prodotto finito/semi-lavorato dopo la produzione.</small>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-save mr-1"></i> Salva
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@stop
