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

                    {{-- ── Causale Carico in Magazzino ───────────────────────────── --}}
                    <div class="form-group">
                        <label for="{{ \App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL }}">
                            Causale <em>Carico in Magazzino</em>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-arrow-circle-down"></i>
                                </span>
                            </div>
                            <select
                                id="{{ \App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL }}"
                                name="{{ \App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL }}"
                                class="form-control @error(\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL) is-invalid @enderror">
                                <option value="">— Nessuna —</option>
                                @foreach($causals as $causal)
                                    <option value="{{ $causal->id }}"
                                        {{ ($settings[\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL] ?? '') == $causal->id ? 'selected' : '' }}>
                                        {{ $causal->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error(\App\Models\Setting::KEY_WAREHOUSE_LOAD_CAUSAL)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Causale usata di default per i movimenti di carico in magazzino.
                        </small>
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
