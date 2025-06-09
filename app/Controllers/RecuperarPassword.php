<?php

namespace App\Controllers;

use App\Models\UsuariosModel;
use CodeIgniter\Controller;
use Config\Services;

class RecuperarPassword extends Controller
{
    public function mostrarFormulario()
    {
        return view('auth/recuperar_password');
    }

     public function enviarToken()
{
    $request = $this->request;

    // Detectar si es JSON
    if ($request->is('json')) {
        $data = $request->getJSON(true); // obtener array
        $correo = trim($data['correo'] ?? '');
    } else {
        $correo = trim($request->getPost('correo'));
    }

    if (empty($correo)) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Debes ingresar tu correo electrónico.'
        ]);
    }

    $usuarioModel = new \App\Models\UsuariosModel();

    $usuario = $usuarioModel
        ->where('correo', $correo)
        ->where('cuenta_activada', 1)
        ->first();

    if (!$usuario) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'El correo no está registrado o la cuenta no está activada.'
        ]);
    }

    // Generar token
    $token = bin2hex(random_bytes(32));
    $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $usuarioModel->update($usuario['id'], [
        'reset_token' => $token,
        'token_expiry' => $fechaExpiracion
    ]);

    $link = base_url("recuperar-password/restablecer/{$token}");

    // Configurar y enviar correo
    $configEmail = config('Email');
    $email = \Config\Services::email();

    $email->setFrom($configEmail->fromEmail, $configEmail->fromName);
    $email->setTo($correo);
    $email->setSubject('Recuperación de contraseña');
    $email->setMessage(view('auth/email_recuperar', ['link' => $link, 'nombre' => $usuario['nombre']]));

    if ($email->send()) {
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Revisa tu correo, por favor.'
        ]);
    } else {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'No se pudo enviar el correo.',
            'debug' => $email->printDebugger(['headers'])
        ]);
    }
}

    public function mostrarFormularioRestablecer($token)
    {
        $usuarioModel = new UsuariosModel();

        $usuario = $usuarioModel
            ->where('reset_token', $token)
            ->where('token_expiry >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$usuario) {
            return view('auth/token_invalido');
        }

        return view('auth/restablecer_password', ['token' => $token]);
    }

    public function actualizarPassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        if (empty($token) || empty($password)) {
            return redirect()->back()->with('error', 'Debes ingresar la nueva contraseña.');
        }

        $usuarioModel = new UsuariosModel();

        $usuario = $usuarioModel
            ->where('reset_token', $token)
            ->where('token_expiry >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$usuario) {
            return view('auth/token_invalido');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $usuarioModel->update($usuario['id'], [
            'contrasena' => $hash,
            'reset_token' => null,
            'token_expiry' => null
        ]);

        return redirect()->to(base_url('login'))->with('success', 'Tu contraseña ha sido actualizada correctamente. Ahora puedes iniciar sesión.');
    }
}
