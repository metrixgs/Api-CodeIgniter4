<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Estamos en Mantenimiento</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .maintenance-container {
                text-align: center;
                padding: 40px;
                background-color: white;
                border-radius: 10px;
                box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            }
            h1 {
                font-size: 2.5rem;
                margin-bottom: 20px;
            }
            p {
                font-size: 1.2rem;
                color: #6c757d;
            }
            .logo {
                max-width: 150px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="maintenance-container">
            <img src="<?= base_url(); ?>public/assets/plataforma/assets/img/mecanico.png" alt="Logo" class="logo"> <!-- Puedes poner tu logo -->
            <h1>Estamos en Mantenimiento</h1>
            <p>Estamos trabajando para mejorar tu experiencia. Vuelve a visitarnos pronto.</p>
            <p class="text-muted">Gracias por tu paciencia.</p>
        </div>

        <!-- Bootstrap JS (opcional, pero útil para componentes interactivos) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
