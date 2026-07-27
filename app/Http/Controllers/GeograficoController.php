<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use Illuminate\Http\Request;

class GeograficoController extends Controller
{
    public function departamentos()
    {
        $departamentos = Departamento::orderBy('codigo')->get(['codigo', 'nombre']);
        return response()->json($departamentos);
    }

    public function distritos(Request $request)
    {
        
        $request->validate(['departamento_codigo' => 'required|integer']);

        $distritos = Distrito::where('departamento_codigo', $request->departamento_codigo)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);

        return response()->json($distritos);
    }

    public function ciudades(Request $request)
    {
        if ($request->has('distrito_id')) {
            $distritoId = $request->distrito_id;
        } elseif ($request->has('departamento_codigo') && $request->has('distrito_codigo')) {
            $distrito = Distrito::where('codigo', $request->distrito_codigo)
                ->where('departamento_codigo', $request->departamento_codigo)
                ->first();
            if (!$distrito) {
                return response()->json([]);
            }
            $distritoId = $distrito->id;
        } else {
            return response()->json([]);
        }

        $ciudades = Ciudad::where('distrito_id', $distritoId)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);

        return response()->json($ciudades);
    }
}
