<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Chat App</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .welcome-container {
            margin-top: 100px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        img {
            width: 200px; /* تحديد عرض الصورة */
            height: auto; /* الارتفاع يتم ضبطه تلقائيًا بناءً على العرض */
            border-radius: 50%; /* جعل الصورة دائرية */
        }
    </style>
</head>

<body>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="container welcome-container text-center">
            <img src='https://almsaeyimages.akhbarelyom.com/large/20230530131656542.jpg'>
            <h1>Welcome to Chat App</h1>
            <p class="lead">Connect with friends, share photos, and more.</p>
            <a href="{{ route('login') }}" class="btn btn-primary mr-2">Login</a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary">Register</a>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>