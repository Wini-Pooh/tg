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
                            @if(Auth::user()->telegram_photo_url)
                                <img src="{{ Auth::user()->telegram_photo_url }}" alt="Avatar" class="rounded-circle mb-3" width="100" height="100">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                    <i class="bi bi-person-fill text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4>Добро пожаловать, {{ Auth::user()->name }}!</h4>
                            
                            <div class="mt-3">
                                <strong>Telegram ID:</strong> {{ Auth::user()->telegram_id }}<br>
                                @if(Auth::user()->telegram_username)
                                    <strong>Username:</strong> @{{ Auth::user()->telegram_username }}<br>
                                @endif
                                <strong>Email:</strong> {{ Auth::user()->email }}<br>
                                <strong>Дата регистрации:</strong> {{ Auth::user()->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Вы успешно авторизованы через Telegram!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
