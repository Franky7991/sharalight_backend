@extends('adminlte::page')

@section('title', 'Nuovo Utente')

@section('content_header')@stop

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h4 class="mb-0">Nuovo Utente</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">
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
                <div class="col-6">
                    <label for="name">Nome *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="form-control" placeholder="Nome" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="email">Email *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="form-control" placeholder="Email" required>
                    </div>
                </div>
                <div class="col-6">
                    <label for="password">Password *</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" id="password" name="password"
                            class="form-control" placeholder="Password" required>
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
                            <a href="{{ route('users.index') }}">
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
