
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ابدأ التحدث</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chat-link {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .chat-link:hover {
            background-color: #0056b3;
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
            <h1>welcome {{ Auth::user()->name }}</h1>
            <a href="http://127.0.0.1:8000/chatify/{{ $id }}" class="chat-link">Start talking</a>
        </div>
    </div>
</body>
</html>