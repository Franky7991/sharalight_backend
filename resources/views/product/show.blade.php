@extends('adminlte::page')
@section('title', 'Modifica Prodotto')
@section('content_header')@stop

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Modifica Prodotto</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('products.update', [$product->id]) }}">
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
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                        </div>
                        <input type="text" id="name" name="name" value="{{ $product->name }}"
                            class="form-control" placeholder="Nome" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="product_category_id">Categoria *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                        </div>
                        <select id="product_category_id" name="product_category_id" class="form-control" required>
                            <option value="">-- Seleziona --</option>
                            @foreach($productCategories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->product_category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <label>Prodotto Finito</label>
                    <div class="mb-3">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="finished_product" value="0">
                            <input type="checkbox" class="custom-control-input" id="finished_product"
                                name="finished_product" value="1"
                                {{ $product->finished_product ? 'checked' : '' }}>
                            <label class="custom-control-label" for="finished_product">
                                Sì, è un prodotto finito
                            </label>
                        </div>
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
                            <a href="{{ route('products.index') }}">
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
