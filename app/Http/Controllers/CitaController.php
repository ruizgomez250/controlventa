<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Opcion;
use App\Models\Cliente;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $heads = [
            'Código', 'Fecha', 'Hora', 'Estado', 'Tipo de Consulta', 'Paciente', 'Propietario', 'Acción'
        ];
        return view('citas.index', ['heads' => $heads]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $opcionestado = Opcion::where('id_dominio', 10)->orderBy('descripcion', 'asc')->get();
        $opciontipoconsulta = Opcion::where('id_dominio', 11)->orderBy('descripcion', 'asc')->get();
        $cliente = Cliente::All();
        return view('citas.create', ['tipo' => $opciontipoconsulta,  'cliente' => $cliente]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //  dd($request->get('hora').':00');
        // $request->validate(['razonsocial' => 'required']);
        $horita =  $request->get('hora') . ':00';
        $datos = new Cita();
        $datos->fecha = Carbon::createFromFormat('d-m-Y', $request->get('fecha'))->format('Y-m-d');
        $datos->hora = Carbon::createFromFormat('H:i:s', $horita)->format('H:i:s');

        $datos->estado_id = 66; //programada
        $datos->tipo_id = $request->get('tipo_id');
        $datos->mascota_id = $request->get('mascota_id');
        $datos->save();
        return redirect()->route('cita.index');

        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cita = Cita::find($id);
        $cita->delete();
        return response()->json(['message' => 'Cita eliminada correctamente'], 200)
            ->header('Content-Type', 'application/json')
            ->header('X-Requested-With', 'XMLHttpRequest');
    }


    public function cambioestado(Request $request, string $id)
    {
        $cita = Cita::findOrFail($id);
        $cita->estado_id = $request->estado;
        $cita->save();
        return response()->json(['message' => 'Entidad actualizada correctamente']);
    }


    public function obtenerdatos($fecha)
    {
        //  $datos = Cita::with('estadocita')->whereDate('fecha', $fecha)->get();
        $datos = Cita::with('estadocita', 'tipoconsulta', 'mascota', 'mascota.propietario')->whereDate('fecha', $fecha)->get();

        return response()->json([
            'datos' => $datos
        ]);
    }

    public function obtenermascotas(int $id)
    {
        $resultado = Mascota::select('id', 'nombre')->where('propietario_id', $id)->get();
        return response()->json($resultado);
    }
}
