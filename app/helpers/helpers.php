<?php

use App\Models\Producto;
use App\Models\Rol;

function generarcodigo()
{
    do {
        $codigo = mt_rand(100000000000, 999999999999);
        $bandera = Producto::where('codigo', $codigo)->exists();
        
    } while ($bandera);
    
    return $codigo;
}

function obtenerIdUsuario()
{
    if (auth()->check()) {
        $idUsuario = auth()->id();
        // Hacer algo con $idUsuario
        return $idUsuario;
    } else {
        // El usuario no está autenticado
        // Puedes redireccionar a la página de inicio de sesión u otro manejo
    }
}
function verificaModelo(string $idusuario, string $nombremodelo)
    {
        $permisos = Rol::where('id_usuario', $idusuario)
            ->where('nombre_modelo', $nombremodelo)
            ->first();
        if ($permisos) {
            return true;
        }else{
            return false;
        }
    }



