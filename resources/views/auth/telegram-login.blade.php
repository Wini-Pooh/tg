@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Вход в систему') }}</div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        <h5>Войдите через Telegram</h5>
                        <p class="text-muted">Быстрая и безопасная авторизация</p>
                    </div>

                    @if(config('app.env') === 'local')
                        <!-- Для локальной разработки -->
                        <div class="alert alert-info">
                            <strong>Режим разработки:</strong> Используйте форму ниже для тестирования
                        </div>
                        
                        <form method="POST" action="{{ route('telegram.dev.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="number" class="form-control" id="telegram_id" name="telegram_id" required value="123456789">
                            </div>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Имя</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required value="Test User">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username (необязательно)</label>
                                <input type="text" class="form-control" id="username" name="username" value="testuser">
                            </div>
                            <button type="submit" class="btn btn-primary telegram-login-button w-100">
                                <i class="bi bi-telegram"></i> Войти через Telegram (тест)
                            </button>
                        </form>
                        
                        <hr class="my-4">
                    @endif

                    <!-- Официальный Telegram Login Widget (для продакшена) -->
                    <div class="d-flex justify-content-center">
                        <div id="telegram-login-widget">
                            @if(config('app.env') !== 'local')
                                <script async src="https://telegram.org/js/telegram-widget.js?22" 
                                        data-telegram-login="{{ config('services.telegram.bot_username') }}" 
                                        data-size="large" 
                                        data-auth-url="{{ route('telegram.login') }}" 
                                        data-request-access="write">
                                </script>
                            @else
                                <div class="alert alert-warning">
                                    <strong>Telegram Widget:</strong> Не работает на localhost. 
                                    Для тестирования используйте форму выше или настройте ngrok.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger mt-3" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted small">
                            Нажимая кнопку "Войти через Telegram", вы соглашаетесь с нашими условиями использования
                        </p>
                        
                        @if(config('app.env') === 'local')
                            <div class="mt-3">
                                <h6>Инструкция для настройки:</h6>
                                <ol class="text-start small text-muted">
                                    <li>Установите ngrok: <code>npm install -g ngrok</code></li>
                                    <li>Запустите: <code>ngrok http 80</code></li>
                                    <li>Обновите APP_URL в .env на URL от ngrok</li>
                                    <li>В @BotFather установите домен для вашего бота</li>
                                </ol>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
