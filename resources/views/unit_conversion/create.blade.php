@extends('adminlte::page')
@section('title', 'Nuova Conversione')
@section('content_header')@stop

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Nuova Conversione Unità di Misura</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('unit-conversions.store') }}">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">

                {{-- Da --}}
                <div class="col-6">
                    <label for="from_unit_of_measure_id">Da Unità di Misura *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                        </div>
                        <select id="from_unit_of_measure_id" name="from_unit_of_measure_id" class="form-control" required>
                            <option value="">-- Seleziona --</option>
                            @foreach($unitOfMeasures as $uom)
                                <option value="{{ $uom->id }}" {{ old('from_unit_of_measure_id') == $uom->id ? 'selected' : '' }}>
                                    {{ $uom->name }} ({{ $uom->symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-6">
                    <label for="from_quantity">Quantità Da *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        </div>
                        <input type="text" id="from_quantity" name="from_quantity"
                            value="{{ old('from_quantity') }}"
                            class="form-control" placeholder="0,00" autocomplete="off" required>
                    </div>
                </div>

                {{-- A --}}
                <div class="col-6">
                    <label for="to_unit_of_measure_id">A Unità di Misura *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                        </div>
                        <select id="to_unit_of_measure_id" name="to_unit_of_measure_id" class="form-control" required>
                            <option value="">-- Seleziona --</option>
                            @foreach($unitOfMeasures as $uom)
                                <option value="{{ $uom->id }}" {{ old('to_unit_of_measure_id') == $uom->id ? 'selected' : '' }}>
                                    {{ $uom->name }} ({{ $uom->symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-6">
                    <label for="to_quantity">Quantità A *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        </div>
                        <input type="text" id="to_quantity" name="to_quantity"
                            value="{{ old('to_quantity') }}"
                            class="form-control" placeholder="0,00" autocomplete="off" required>
                    </div>
                </div>

                <div class="col-12">
                    <div class="row">
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-save"></i> Salva
                            </button>
                        </div>
                        <div class="col-3">
                            <a href="{{ route('unit-conversions.index') }}">
                                <button type="button" class="btn btn-danger btn-block btn-sm">
                                    <i class="fa fa-backward"></i> Indietro
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function () {
    function formatIt(input) {
        $(input).on('blur', function () {
            var raw = $(this).val().trim().replace(/\./g, '').replace(',', '.');
            var n   = parseFloat(raw);
            if (!isNaN(n)) {
                $(this).val(n.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        });
        $(input).on('keypress', function (e) {
            if (!/[\d,\.]/.test(String.fromCharCode(e.which))) e.preventDefault();
        });
    }
    formatIt('#from_quantity');
    formatIt('#to_quantity');
});
</script>
@stop
