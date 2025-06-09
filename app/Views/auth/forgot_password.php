<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña </title>
</head>
<body>
    <form action="<?= site_url('auth/enviar-recuperacion') ?>" method="post">
    <label>Correo electrónico:</label>
    <input type="email" name="correo" required>
    <button type="submit">Enviar instrucciones</button>
</form>

</body>
</html>