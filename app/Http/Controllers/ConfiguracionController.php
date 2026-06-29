<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\SifenConfiguracion;
use App\Services\SifenPkuatiaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configuraciones = Configuracion::all();
        $sifenConfig = SifenConfiguracion::firstOrCreate([], [
            'ambiente' => 1,
            'establecimiento' => '001',
            'punto_expedicion' => '001',
            'habilitado' => false,
        ]);
        return view('configuracion.index', compact('configuraciones', 'sifenConfig'));
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
        try {
            DB::transaction(function () use ($request) {

                // Condicionv
                $condicionv = Configuracion::firstOrCreate(
                    ['descripcion' => 'condicionv'],
                    ['estado' => 1]
                );

                $condicionv->estado = ($request->input('condicion') == 'cadavez') ? 1 : 0;
                $condicionv->save();

                // Ventas / pagos
                $ventas = Configuracion::firstOrCreate(
                    ['descripcion' => 'ventas'],
                    ['estado' => 0]
                );

                $ventas->estado = $request->has('pagos') ? 1 : 0;
                $ventas->save();

                // Idioma
                if ($request->has('idioma')) {
                    $idioma = Configuracion::firstOrCreate(
                        ['descripcion' => 'idioma'],
                        ['observacion' => 'es']
                    );
                    $idioma->observacion = $request->input('idioma');
                    $idioma->save();
                    session(['app_locale' => $request->input('idioma')]);
                }

                // Moneda
                if ($request->has('moneda')) {
                    $moneda = Configuracion::firstOrCreate(
                        ['descripcion' => 'moneda'],
                        ['observacion' => 'Gs.']
                    );
                    $moneda->observacion = $request->input('moneda');
                    $moneda->save();
                }

                // SIFEN - Facturación Electrónica Paraguay
                $sifen = SifenConfiguracion::firstOrCreate([], [
                    'ambiente' => 1,
                    'establecimiento' => '001',
                    'punto_expedicion' => '001',
                    'habilitado' => false,
                ]);

                $sifen->ruc_emisor = $request->input('sifen_ruc_emisor', $sifen->ruc_emisor);
                $sifen->dv = $request->input('sifen_dv', $sifen->dv);
                $sifen->razon_social = $request->input('sifen_razon_social', $sifen->razon_social);
                $sifen->direccion = $request->input('sifen_direccion', $sifen->direccion);
                $sifen->calle_principal = $request->input('sifen_calle_principal', $sifen->calle_principal);
                $sifen->numero_casa = $request->input('sifen_numero_casa', $sifen->numero_casa);
                $sifen->calle_secundaria = $request->input('sifen_calle_secundaria', $sifen->calle_secundaria);
                $sifen->complemento_direccion = $request->input('sifen_complemento_direccion', $sifen->complemento_direccion);
                $sifen->telefono = $request->input('sifen_telefono', $sifen->telefono);
                $sifen->email = $request->input('sifen_email', $sifen->email);
                $sifen->establecimiento = $request->input('sifen_establecimiento', $sifen->establecimiento);
                $sifen->punto_expedicion = $request->input('sifen_punto_expedicion', $sifen->punto_expedicion);
                $sifen->ambiente = $request->input('sifen_ambiente', $sifen->ambiente);
                $sifen->departamento_codigo = $request->input('sifen_departamento_codigo', $sifen->departamento_codigo);
                $sifen->distrito_codigo = $request->input('sifen_distrito_codigo', $sifen->distrito_codigo);
                $sifen->ciudad_codigo = $request->input('sifen_ciudad_codigo', $sifen->ciudad_codigo);
                $sifen->nombre_sucursal = $request->input('sifen_nombre_sucursal', $sifen->nombre_sucursal);
                $sifen->tipo_contribuyente = $request->input('sifen_tipo_contribuyente', $sifen->tipo_contribuyente);
                $sifen->actividad_economica_codigo = $request->input('sifen_actividad_economica_codigo', $sifen->actividad_economica_codigo);
                $sifen->actividad_economica_descripcion = $request->input('sifen_actividad_economica_descripcion', $sifen->actividad_economica_descripcion);
                $sifen->csc_id = $request->input('sifen_csc_id', $sifen->csc_id);
                $sifen->csc_codigo = $request->input('sifen_csc_codigo', $sifen->csc_codigo);
                $sifen->habilitado = $request->has('sifen_habilitado');
                $sifen->certificado_password = $request->input('sifen_certificado_password', $sifen->certificado_password);

                if ($request->has('sifen_limpiar_certificado')) {
                    $sifen->certificado_p12 = null;
                } elseif ($request->hasFile('sifen_certificado_p12')) {
                    $file = $request->file('sifen_certificado_p12');
                    $sifen->certificado_p12 = base64_encode(file_get_contents($file->getRealPath()));
                }

                $sifen->save();
            });

            return redirect()->route('configuracion.index')
                ->with('success', 'Configuración actualizada con éxito!!');
        } catch (\Exception $e) {
            return redirect()->route('configuracion.index')
                ->with('error', 'Ocurrió un error al actualizar la configuración: ' . $e->getMessage());
        }
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
