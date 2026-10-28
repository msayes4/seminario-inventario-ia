<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
        SELECT id, nombre, correo, password, rol
        FROM usuarios
        WHERE correo = ?
        AND estado = 1
        LIMIT 1
    ");

    $stmt->bind_param("s", $correo);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    $passwordCorrecta = false;
    $esHashAntiguo = false;

    if ($user) {

        // Comprobar contraseñas modernas.
        if (password_verify($password, $user['password'])) {

            $passwordCorrecta = true;

        // Compatibilidad con las contraseñas SHA-256 existentes.
        } elseif (
            preg_match('/^[a-f0-9]{64}$/i', $user['password']) &&
            hash_equals(
                strtolower($user['password']),
                hash('sha256', $password)
            )
        ) {

            $passwordCorrecta = true;
            $esHashAntiguo = true;
        }
    }

    if ($user && $passwordCorrecta) {

        // Actualizar la contraseña antigua a un hash moderno.
        if ($esHashAntiguo) {

            $nuevoHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $actualizar = $conn->prepare("
                UPDATE usuarios
                SET password = ?
                WHERE id = ?
            ");

            $actualizar->bind_param(
                "si",
                $nuevoHash,
                $user['id']
            );

            $actualizar->execute();
            $actualizar->close();
        }

        // Renovar el identificador de sesión.
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];

        header('Location: /dashboard/index.php');
        exit;

    } else {

        $error = 'Correo o contraseña incorrectos.';
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Inventario IA - Iniciar sesión</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f2f5f8;
        }

        .login {
            max-width: 420px;
            margin: 9vh auto;
        }

        .brand {
            font-weight: 800;
        }
    </style>
</head>

<body>

<div class="login">

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <h2 class="brand mb-1">Inventario IA</h2>

            <p class="text-muted">
                Gestión inteligente de inventarios
            </p>

            <?php if ($error !== ''): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>

            <form method="post">

                <label class="form-label">
                    Correo electrónico
                </label>

                <input
                    class="form-control mb-3"
                    type="email"
                    name="correo"
                    value="<?= htmlspecialchars($correo) ?>"
                    autocomplete="username"
                    required
                >

                <label class="form-label">
                    Contraseña
                </label>

                <input
                    class="form-control mb-3"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >
                    Iniciar sesión
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
