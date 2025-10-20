<?php

namespace App\services;

use App\Http\Controllers\RolController;

class PermisoService
{
    protected $rolController;

    public function __construct(RolController $rolController)
    {
        $this->rolController = $rolController;
    }

    public function verificarPermiso(string $nombremodelo, string $accion)
    {
        // Llamar al método verificarPermiso del controlador RolController
        return $this->rolController->verificarPermiso($nombremodelo, $accion);
    }
}
