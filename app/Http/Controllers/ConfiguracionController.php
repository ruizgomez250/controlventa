<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configuraciones = Configuracion::all();
        return view('configuracion.index', compact('configuraciones'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('configuraciones.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $configuracion = Configuracion::find($idqr = $request->input('idqr'));
        $qrtrue = $request->has('qr') ? 1 : 0;
        $configuracion->estado=$qrtrue;
        $configuracion->save();
        return redirect()->route('configuracion.index')
            ->with('success', 'Configuracion actualizado con exito!!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function show(Configuracion $configuracion)
    {
        return view('configuraciones.show', compact('configuracion'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function edit(Configuracion $configuracion)
    {
        return view('configuraciones.edit', compact('configuracion'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Configuracion $configuracion)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'estado' => 'required|boolean',
            'observacion' => 'nullable|string|max:255',
        ]);

        $configuracion->update($request->all());

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuracion updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Configuracion  $configuracion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Configuracion $configuracion)
    {
        $configuracion->delete();

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuracion deleted successfully');
    }
}
