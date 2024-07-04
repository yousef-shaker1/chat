<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            margin-top: 50px;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 600px; margin: auto; padding: 20px;">
        <h2 class="text-center">تسجيل الدخول</h2>
        <?php //if (!empty($_POST['submit'])){?>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
        </form>
        <div class="mt-3 text-center">
            <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
        </div>
        <div class="mt-3 text-center">
            <a href="{{ route('register') }}">ليس لديك حساب؟ قم بالتسجيل الآن</a>
        </div>
    </div>
</body>
</html>