@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Авторизация через Telegram') }}</div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <h4>Добро пожаловать!</h4>
                        <p class="text-muted">Для входа в приложение используйте Telegram</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="telegram-login-widget" data-telegram-login="{{ config('services.telegram.bot_username') }}" 
                         data-size="large" 
                         data-auth-url="{{ route('telegram.callback') }}" 
                         data-request-access="write">
                    </div>

                    <div class="mt-4">
                        <p class="text-muted small">
                            Нажимая кнопку "Авторизоваться", вы соглашаетесь с передачей ваших данных из Telegram
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script async src="https://telegram.org/js/telegram-widget.js?22"></script>
@endsection

@section('styles')
<style>
.telegram-login-widget {
    display: inline-block;
    margin: 20px 0;
}

.card-body {
    padding: 2rem;
}

.card-header {
    background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
    color: white;
    font-weight: 600;
}
</style>
@endsection
