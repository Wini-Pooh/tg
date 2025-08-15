@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Личный кабинет') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                <i class="bi bi-person-fill text-white" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h4>Добро пожаловать, {{ Auth::user()->name }}!</h4>
                            
                            <div class="mt-3">
                                <strong>Email:</strong> {{ Auth::user()->email }}<br>
                                <strong>Дата регистрации:</strong> {{ Auth::user()->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Вы успешно авторизованы!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
