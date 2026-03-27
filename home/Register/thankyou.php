<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .tick {
            font-size: 100px;
            color: green;
        }
        h1 {
            margin-top: 10px;
            color: #333;
        }
        p {
            color: #666;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 22px;
            background-color: #0077cc;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 0 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #005fa3;
        }
        .btn-outline {
            background: #6c757d;
        }
        .btn-outline:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
<div class="box">
    <div class="tick">✓</div>
    <h1>Thank You!</h1>
    <p>You have been successfully registered for this programme.</p>
    <div class="buttons">
        <a href="main.php" class="btn">Register Again!</a>
        <a href="../index.php" class="btn btn-outline">Back to Home</a>
    </div>
</div>
</body>
</html>
