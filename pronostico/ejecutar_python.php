<?php

function pronosticarConPython(array $ventas, int $dias): ?array
{
    $raiz = dirname(__DIR__);

    $script = $raiz . DIRECTORY_SEPARATOR
        . 'python' . DIRECTORY_SEPARATOR
        . 'pronostico.py';

    if (!is_file($script)) {
        error_log('No se encontró el archivo python/pronostico.py.');
        return null;
    }

    // Permite configurar la ruta de Python en el servidor.
    $pythonConfigurado = getenv('PYTHON_BIN');

    if ($pythonConfigurado !== false && $pythonConfigurado !== '') {
        $python = $pythonConfigurado;
    } else {
        // Ruta habitual de Python en Windows.
        $pythonWindows = $raiz . DIRECTORY_SEPARATOR
            . '.venv' . DIRECTORY_SEPARATOR
            . 'Scripts' . DIRECTORY_SEPARATOR
            . 'python.exe';

        // Ruta prevista para el entorno Linux del despliegue.
        $pythonLinux = '/opt/venv/bin/python';

        if (PHP_OS_FAMILY === 'Windows' && is_file($pythonWindows)) {
            $python = $pythonWindows;
        } elseif (is_file($pythonLinux)) {
            $python = $pythonLinux;
        } else {
            $python = 'python3';
        }
    }

    $entrada = json_encode([
        'ventas' => array_values($ventas),
        'dias' => max(1, $dias)
    ]);

    if ($entrada === false) {
        error_log('No fue posible preparar los datos del pronóstico.');
        return null;
    }

    $descriptores = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $proceso = proc_open(
        [$python, $script],
        $descriptores,
        $tuberias,
        $raiz
    );

    if (!is_resource($proceso)) {
        error_log('No fue posible iniciar el proceso de Python.');
        return null;
    }

    fwrite($tuberias[0], $entrada);
    fclose($tuberias[0]);

    $salida = stream_get_contents($tuberias[1]);
    fclose($tuberias[1]);

    $error = stream_get_contents($tuberias[2]);
    fclose($tuberias[2]);

    $codigo = proc_close($proceso);

    if ($codigo !== 0) {
        error_log('Error al ejecutar Python: ' . $error);
        return null;
    }

    $resultado = json_decode($salida, true);

    if (
        !is_array($resultado)
        || !isset($resultado['demanda_predicha'])
        || !is_numeric($resultado['demanda_predicha'])
    ) {
        error_log('Python devolvió una respuesta no válida.');
        return null;
    }

    return $resultado;
}
