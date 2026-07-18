@extends('adminlte::page')

@section('title', 'Home')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="mb-0">Benvenuto, <strong>{{ Auth::user()->name }}</strong>!</p>
            </div>
        </div>
    </div>
</div>
@stop
