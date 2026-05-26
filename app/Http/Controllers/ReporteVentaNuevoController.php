<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\User;
use TCPDF;


class ReporteVentaNuevoController extends Controller
{


    public function index()
    {

        $usuarios = User::all();
        return view('reportes.vendidos', compact('usuarios'));
    }

   
}
