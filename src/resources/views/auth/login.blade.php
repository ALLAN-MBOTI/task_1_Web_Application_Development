{{--
============================================================================
SAP Business One Styled Login Interface
============================================================================
Page Overview:
 - Served at the base URL (`/`).
 - Provides standard credentials authentication (Email & Password).
 - Features themed styling matching the desktop application layout.
 - Automatically redirects authorized users directly to the Invoice route.
============================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAP Business One - Login</title>
    <style>
        body { font-family: Arial, Tahoma, sans-serif; font-size: 11px; background-color: #2b3a4e; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background-color: #f2f3f5; border: 1px solid #707070; width: 360px; box-shadow: 4px 4px 10px rgba(0,0,0,0.5); }
        .login-header { background: linear-gradient(to bottom, #3f51b5, #1a237e); color: white; padding: 6px 10px; font-weight: bold; font-size: 12px; display: flex; justify-content: space-between; }
        .login-body { padding: 20px; }
        .form-group { margin-bottom: 12px; display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 4px; font-weight: bold; color: #333; }
        .form-group input { border: 1px solid #999; padding: 5px; font-size: 11px; }
        .btn-login { background-color: #2b5797; color: white; border: 1px solid #1e395f; padding: 6px 14px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; }
        .btn-login:hover { background-color: #1e395f; }
        .error-msg { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 8px; margin-bottom: 12px; font-size: 10px; }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-header">
        <span>SAP Business One - Authentication</span>
        <span>&#9633; &times;</span>
    </div>
    <div class="login-body">
        @if ($errors->any())
            <div class="error-msg">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.perform') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Log In</button>
        </form>
    </div>
</div>

</body>
</html>