@extends('adminlte::page')
@section('title', 'Modifica Categoria Prodotto')
@section('content_header')@stop

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Modifica Categoria Prodotto</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('product-categories.update', [$productCategory->id]) }}">
            @csrf
            @method('PUT')
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
                <div class="col-6">
                    <label for="name">Nome *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        </div>
                        <input type="text" id="name" name="name" value="{{ $productCategory->name }}"
                            class="form-control" placeholder="Nome" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="unit_of_measure_id">Unità di Misura *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                        </div>
                        <select id="unit_of_measure_id" name="unit_of_measure_id" class="form-control" required>
                            <option value="">-- Seleziona --</option>
                            @foreach($unitOfMeasures as $uom)
                                <option value="{{ $uom->id }}" {{ $productCategory->unit_of_measure_id == $uom->id ? 'selected' : '' }}>
                                    {{ $uom->name }} ({{ $uom->symbol }})
                                </option>
                            @endforeach
                        </select>
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
                            <a href="{{ route('product-categories.index') }}">
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
