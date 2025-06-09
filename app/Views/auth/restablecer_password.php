
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metrix - Restablecer Contraseña</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2ECC71 0%, #27AE60 50%, #1E8449 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2ECC71, #27AE60, #1ABC9C, #16A085);
            border-radius: 25px 25px 0 0;
        }

        .container::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(46, 204, 113, 0.05) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            pointer-events: none;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        .logo h1 {
            background: linear-gradient(135deg, #2ECC71, #27AE60);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -2px;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logo .subtitle {
            color: #34495E;
            font-size: 1rem;
            font-weight: 500;
            opacity: 0.8;
        }

        .form-container {
            position: relative;
            z-index: 2;
        }

        .form-group {
            margin-bottom: 30px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 12px;
            color: #2C3E50;
            font-weight: 600;
            font-size: 1rem;
            transform: translateX(5px);
        }

        .input-container {
            position: relative;
        }

        input[type="password"] {
            width: 100%;
            padding: 18px 55px 18px 20px;
            border: 2px solid #E8F8F5;
            border-radius: 15px;
            font-size: 1.1rem;
            background: linear-gradient(145deg, #FFFFFF, #F8FFFD);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.08);
        }

        input[type="password"]:focus {
            border-color: #27AE60;
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.15), 
                        0 8px 25px rgba(46, 204, 113, 0.12);
            transform: translateY(-2px);
        }

        input[type="password"]:hover {
            border-color: #2ECC71;
            transform: translateY(-1px);
        }

        .eye-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.3rem;
            color: #95A5A6;
            transition: all 0.3s ease;
            padding: 5px;
            border-radius: 50%;
        }

        .eye-icon:hover {
            color: #27AE60;
            background: rgba(46, 204, 113, 0.1);
            transform: translateY(-50%) scale(1.1);
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #2ECC71, #27AE60);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.3);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(46, 204, 113, 0.4);
            background: linear-gradient(135deg, #27AE60, #2ECC71);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }

        .back-link a {
            color: #27AE60;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            padding: 12px 24px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .back-link a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(46, 204, 113, 0.1);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
            border-radius: 12px;
        }

        .back-link a:hover::before {
            transform: scaleX(1);
        }

        .back-link a:hover {
            color: #1E8449;
            transform: translateY(-2px);
        }

        .back-link a span {
            position: relative;
            z-index: 1;
        }

        /* Efectos de partículas flotantes */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            background: rgba(46, 204, 113, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 60px;
            height: 60px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 40px;
            height: 40px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 40px 25px;
                margin: 15px;
                border-radius: 20px;
            }
            
            .logo h1 {
                font-size: 2.5rem;
            }

            input[type="password"] {
                padding: 16px 50px 16px 18px;
                font-size: 1rem;
            }

            .submit-btn {
                padding: 16px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <div class="logo">
            <h1>METRIX</h1>
            <div class="subtitle">Restablecer Contraseña</div>
        </div>

        <div class="form-container">
            <form action="<?= base_url('recuperar-password/actualizar-password') ?>" method="post">
                <input type="hidden" name="token" value="<?= esc($token) ?>">
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña:</label>
                    <div class="input-container">
                        <input type="password" id="password" name="password" required>
                        <span class="eye-icon" onclick="togglePassword('password')">👁️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirmar Contraseña:</label>
                    <div class="input-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <span class="eye-icon" onclick="togglePassword('confirmPassword')">👁️</span>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Actualizar Contraseña
                </button>
            </form>
        </div>

        

    <script>
        // Solo función para mostrar/ocultar contraseña
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.getAttribute('type') === 'password') {
                input.setAttribute('type', 'text');
                icon.textContent = '🙈';
            } else {
                input.setAttribute('type', 'password');
                icon.textContent = '👁️';
            }
        }

        // Validación simple solo para que coincidan las contraseñas
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>