<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Admin Log in</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #333333;
            font-family: Arial, Helvetica, sans-serif;
            color: #ffffff;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 32px;
        }

        .login-box {
            width: 356px;
            min-height: 400px;
            background: #080808;
            border-radius: 4px;
            box-shadow: 0 0 14px rgba(0, 0, 0, 0.7);
            overflow: hidden;
            position: relative;
        }

        .login-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 95px;
            width: 120px;
            height: 100%;
            background: linear-gradient(110deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0));
            transform: skewX(-22deg);
            pointer-events: none;
        }

        .login-header {
            height: 60px;
            border-bottom: 1px solid #222222;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-title {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
            text-shadow: 1px 1px 2px #000000;
        }

        .login-flag {
            position: absolute;
            right: 16px;
            top: 13px;
            width: 38px;
            height: 27px;
            background: #0b7a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-flag img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-version {
            position: absolute;
            right: 7px;
            top: 64px;
            font-size: 14px;
            color: #ffffff;
            font-weight: bold;
        }

        .login-form {
            padding: 25px 24px 0 24px;
            position: relative;
            z-index: 2;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            color: #777777;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            height: 43px;
            border: 2px solid #c8d2df;
            border-radius: 5px;
            background: #e8f0fb;
            color: #000000;
            font-size: 16px;
            padding: 0 8px;
            outline: none;
        }

        .form-input:focus {
            border-color: #ffffff;
            box-shadow: 0 0 3px #ffffff;
        }

        .login-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 28px;
        }

        .login-button {
            width: 92px;
            height: 31px;
            border: 1px solid #0c5fb5;
            border-radius: 4px;
            background: linear-gradient(#1da2ff, #0076df);
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-shadow: 1px 1px 1px #003b78;
        }

        .login-button:hover {
            background: linear-gradient(#42b4ff, #008cff);
        }

        .login-footer {
            position: absolute;
            left: 18px;
            bottom: 28px;
            font-size: 23px;
            font-weight: bold;
            color: #ffffff;
            text-shadow: 2px 2px 2px #000000;
        }

        .login-errors {
            margin: 14px 24px 0 24px;
            padding: 9px 10px;
            background: #3b0d0d;
            border: 1px solid #b52a2a;
            color: #ffffff;
            font-size: 13px;
            border-radius: 4px;
            position: relative;
            z-index: 3;
        }

        .login-errors ul {
            margin: 0;
            padding-left: 18px;
        }

        @media (max-width: 480px) {
            .login-page {
                padding-top: 20px;
            }

            .login-box {
                width: calc(100% - 28px);
            }
        }
    </style>
</head>

<body>

    <div class="login-page">

        <div class="login-box">

            <div class="login-header">
                <div class="login-title">
                    Admin Log in
                </div>

                <div class="login-flag">
                    <img src="{{ asset('img/brasil.svg') }}" alt="BR">
                </div>
            </div>

            <div class="login-version">
                Version 4.7.5
            </div>

            @if ($errors->any())
                <div class="login-errors">
                    <ul>
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label class="form-label">
                        USER NAME
                    </label>

                    <input
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        class="form-input"
                        autocomplete="username"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">
                        PASSWORD
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-input"
                        autocomplete="current-password"
                    >
                </div>

                <div class="login-actions">
                    <button type="submit" class="login-button">
                        Login
                    </button>
                </div>
            </form>

            <div class="login-footer">
                cybercafe.kisknet.com.br
            </div>

        </div>

    </div>

</body>

</html>