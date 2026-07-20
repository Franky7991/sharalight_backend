@extends('adminlte::page')
@section('title', 'Modifica Prodotto')
@section('content_header')@stop

@section('content')
<div class="row">

    {{-- Colonna sinistra: dati prodotto --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="mb-0">Dati Prodotto</h4>
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

                    <div class="form-group">
                        <label for="name">Nome *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-box"></i></span>
                            </div>
                            <input type="text" id="name" name="name" value="{{ $product->name }}"
                                class="form-control" placeholder="Nome" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_category_id">Categoria *</label>
                        <div class="input-group">
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

                    <div class="form-group">
                        <label for="type">Tipo *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <select id="type" name="type" class="form-control" required>
                                <option value="">-- Seleziona --</option>
                                @foreach($productTypes as $value => $label)
                                    <option value="{{ $value }}" {{ $product->type === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block btn-sm">
                                <i class="fa fa-save"></i> Salva
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('products.index') }}">
                                <button type="button" class="btn btn-danger btn-block btn-sm">
                                    <i class="fa fa-backward"></i> Indietro
                                </button>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Colonna destra: tabs --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    @if($product->hasRecipe())
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-recipe" data-toggle="tab" href="#pane-recipe" role="tab">
                            <i class="fas fa-list-ul mr-1"></i> Ricetta
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="productTabsContent">
                    @if($product->hasRecipe())
                    <div class="tab-pane fade show active" id="pane-recipe" role="tabpanel">
                        @include('product.tabs.recipe', ['product' => $product, 'productCategories' => $productCategories])
                    </div>
                    @else
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        La tab Ricetta è disponibile solo per Semi Lavorati e Prodotti Finiti.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
@if($product->hasRecipe())
<style>
    /* Fix backdrop per modal annidate (Bootstrap 4) */
    .modal-backdrop + .modal-backdrop { z-index: 1055; }
    #modal-detail-pick { z-index: 1060; }
    #modal-detail-pick + .modal-backdrop { z-index: 1055; }
</style>
@include('product.tabs.recipe_js', ['product' => $product])
@endif
@stop
