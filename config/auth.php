<?php

session_start();

/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /auth/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Verificar si el usuario tiene uno de los roles permitidos
|--------------------------------------------------------------------------
*/

function tieneRol($rolesPermitidos): bool
{
    if (!isset($_SESSION['rol'])) {
        return false;
    }

    if (!is_array($rolesPermitidos)) {
        $rolesPermitidos = [$rolesPermitidos];
    }

    return in_array($_SESSION['rol'], $rolesPermitidos, true);
}

/*
|--------------------------------------------------------------------------
| Exigir un rol específico
|--------------------------------------------------------------------------
*/

function exigirRol($rolesPermitidos): void
{
    if (!tieneRol($rolesPermitidos)) {

        http_response_code(403);

        echo '
        <!doctype html>

        <html lang="es">

        <head>

            <meta charset="utf-8">

            <meta name="viewport" content="width=device-width,initial-scale=1">

            <title>Acceso denegado</title>

            <link
                href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
                rel="stylesheet"
            >

        </head>

        <body class="bg-light">

            <div class="container py-5">

                <div class="card shadow-sm border-0">

                    <div class="card-body text-center p-5">

                        <h1 class="text-danger">
                            403
                        </h1>

                        <h3>
                            Acceso denegado
                        </h3>

                        <p class="text-muted">
                            No tienes permisos para acceder a esta sección.
                        </p>

                        <a
                            href="/dashboard/index.php"
                            class="btn btn-primary"
                        >
                            Volver al Dashboard
                        </a>

                    </div>

                </div>

            </div>

        </body>

        </html>
        ';

        exit;
    }
}
?>