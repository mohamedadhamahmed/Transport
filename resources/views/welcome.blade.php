<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NEW VISION</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fb;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .container {
            text-align: center;
        }
        .container img {
            max-width: 420px;
            width: 90%;
        }
        .actions {
            margin-top: 30px;
        }
        .actions a {
            display: inline-block;
            padding: 10px 28px;
            margin: 0 8px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-login {
            color: #2b2f6a;
            border: 1px solid #2b2f6a;
        }
        .btn-register {
            background: #2b2f6a;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('images/logo.jpeg') }}" alt="NEW VISION">
        <div class="actions">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-register">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn-login">تسجيل الدخول</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-register">إنشاء حساب</a>
                @endif
            @endauth
        </div>
    </div>
</body>
</html>