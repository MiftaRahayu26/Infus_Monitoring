<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - JoMonitor | Sistem Monitoring Infus RS Manguharjo Madiun</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #2a9d8f;
            --primary-dark: #21867a;
            --primary-light: #e9f5f3;
            --accent: #264653;
            --text-dark: #2d3748;
            --text-light: #718096;
            --white: #ffffff;
            --bg-light: #f7fafc;
            --error: #e53e3e;
            --success: #38a169;
            --warning: #ecc94b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-light), var(--white));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            background: var(--white);
            padding: 40px 35px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
        }

        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(42, 157, 143, 0.3);
        }

        .login-logo h2 {
            font-size: 1.8rem;
            color: var(--primary);
            font-weight: 700;
        }

        .login-logo span {
            display: block;
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 2px;
        }

        h3 {
            color: var(--accent);
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            color: var(--text-dark);
            font-size: 0.85rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-with-icon {
            position: relative;
            transition: all 0.3s ease;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .input-with-icon input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
        }

        .input-with-icon input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(42, 157, 143, 0.1);
        }

        .input-with-icon input:focus + i {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: var(--white);
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(42, 157, 143, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            opacity: 0.8;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            background: #fed7d7;
            color: var(--error);
            border-left: 4px solid var(--error);
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            text-align: left;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .alert.show {
            display: flex;
            animation: shake 0.5s ease-in-out;
        }

        .alert i {
            font-size: 1rem;
        }

        .demo-info {
            background: linear-gradient(135deg, var(--primary-light), #f0f9f6);
            border-radius: 12px;
            padding: 15px;
            margin-top: 25px;
            border-left: 4px solid var(--primary);
        }

        .demo-info h4 {
            color: var(--primary-dark);
            font-size: 0.85rem;
            margin-bottom: 8px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-info p {
            color: var(--text-dark);
            font-size: 0.75rem;
            margin-bottom: 5px;
            text-align: left;
        }

        .demo-info p:last-child {
            margin-bottom: 0;
        }

        .back-home {
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .back-home:hover {
            color: var(--primary-dark);
            gap: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .loading-spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 0.8s linear infinite;
            display: none;
        }

        .btn-login.loading .loading-spinner {
            display: inline-block;
        }

        .btn-login.loading i.fa-sign-in-alt {
            display: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .logo-icon {
                width: 50px;
                height: 50px;
                font-size: 22px;
            }
            
            .login-logo h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="logo-icon">
                <i class="fas fa-syringe"></i>
            </div>
        </div>

        <h3>Selamat Datang 👋</h3>
        <p class="subtitle">Sistem Monitoring Infus Real-time</p>

        <div class="alert" id="errorAlert">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorMessage">
                @if ($errors->any())
                    {{ $errors->first() }}
                @elseif (session('error'))
                    {{ session('error') }}
                @else
                    Username atau password salah
                @endif
            </span>
        </div>

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" 
                        placeholder="Masukkan email" 
                        value="{{ old('email') }}" required>
                </div>
                @error('email')
                    <span class="text-danger text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="mr-2">
                    <span class="text-sm">Ingat saya</span>
                </label>
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">
                <span class="loading-spinner"></span>
                <i class="fas fa-sign-in-alt"></i>
                <span id="buttonText">Masuk ke Dashboard</span>
            </button>
        </form>



       
    </div>

    <script>
        // Tampilkan error jika ada
        @if ($errors->any() || session('error'))
            document.getElementById('errorAlert').classList.add('show');
        @endif

        // Fungsi loading saat submit
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const buttonText = document.getElementById('buttonText');

        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                
                if (!username || !password) {
                    e.preventDefault();
                    const errorAlert = document.getElementById('errorAlert');
                    const errorMessage = document.getElementById('errorMessage');
                    errorMessage.textContent = 'Username dan password harus diisi!';
                    errorAlert.classList.add('show');
                    
                    setTimeout(() => {
                        errorAlert.classList.remove('show');
                    }, 3000);
                    return;
                }
                
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
                buttonText.textContent = 'Memproses...';
            });
        }

        // Animasi input focus
        document.querySelectorAll('.input-with-icon').forEach(parent => {
            const input = parent.querySelector('input');
            if (input) {
                input.addEventListener('focus', function () {
                    parent.style.transform = 'scale(1.02)';
                });
                input.addEventListener('blur', function () {
                    parent.style.transform = 'scale(1)';
                });
            }
        });

        // Sembunyikan alert setelah 3 detik jika muncul dari awal
        const errorAlert = document.getElementById('errorAlert');
        if (errorAlert.classList.contains('show')) {
            setTimeout(() => {
                errorAlert.classList.remove('show');
            }, 4000);
        }
    </script>
</body>
</html>