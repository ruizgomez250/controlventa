<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Configuracion;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\SifenConfiguracion;
use App\Helpers\ActividadesEconomicas;
use App\Services\SifenPkuatiaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $configuraciones = Configuracion::all();

        // Obtener la configuración existente o crear una nueva con valores NULL
        $sifenConfig = SifenConfiguracion::first();

        if (!$sifenConfig) {
            $sifenConfig = SifenConfiguracion::create([
                'ambiente' => 1,
                'establecimiento' => '001',
                'punto_expedicion' => '001',
                'habilitado' => false,
            ]);
        }

        $departamentos = Departamento::orderBy('codigo')->pluck('nombre', 'codigo');

        // Solo cargar distritos si hay departamento seleccionado
        $distritosIniciales = collect();
        if ($sifenConfig->departamento_codigo) {
            $distritosIniciales = Distrito::where('departamento_codigo', $sifenConfig->departamento_codigo)
                ->orderBy('codigo')
                ->pluck('nombre', 'codigo');
        }

        // Solo cargar ciudades si hay distrito seleccionado
        $ciudadesIniciales = collect();
        if ($sifenConfig->distrito_codigo) {
            $distrito = Distrito::where('codigo', $sifenConfig->distrito_codigo)->first();
            if ($distrito) {
                $ciudadesIniciales = Ciudad::where('distrito_id', $distrito->id)
                    ->orderBy('codigo')
                    ->pluck('nombre', 'codigo');
            }
        }

        $actividadesEconomicas = ActividadesEconomicas::paraSelect();

        return view('configuracion.index', compact(
            'configuraciones',
            'sifenConfig',
            'departamentos',
            'distritosIniciales',
            'ciudadesIniciales',
            'actividadesEconomicas'
        ));
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
     * ============================================================
     *  GUARDAR CONFIGURACIÓN GENERAL + SIFEN
     * ============================================================
     * Persiste en una sola transacción:
     *  - Configuraciones generales (condición de venta, pagos, idioma, moneda)
     *  - Configuración SIFEN (RUC, certificado, CSC, datos geográficos, etc.)
     *
     * El certificado digital P12 se recibe como archivo y se guarda
     * en base64 en la BD. Si se envía sifen_limpiar_certificado, se borra.
     */
    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {

                // ==========================================
                // CONFIGURACIONES GENERALES DEL SISTEMA
                // ==========================================

                // Condición de venta: "cada vez" o fija
                $condicionv = Configuracion::firstOrCreate(
                    ['descripcion' => 'condicionv'],
                    ['estado' => 1]
                );
                $condicionv->estado = ($request->input('condicion') == 'cadavez') ? 1 : 0;
                $condicionv->save();

                // Ventas / pagos: habilitar/deshabilitar módulo de cobros
                $ventas = Configuracion::firstOrCreate(
                    ['descripcion' => 'ventas'],
                    ['estado' => 0]
                );
                $ventas->estado = $request->has('pagos') ? 1 : 0;
                $ventas->save();

                // Idioma de la interfaz (es/en)
                if ($request->has('idioma')) {
                    $idioma = Configuracion::firstOrCreate(
                        ['descripcion' => 'idioma'],
                        ['observacion' => 'es']
                    );
                    $idioma->observacion = $request->input('idioma');
                    $idioma->save();
                    session(['app_locale' => $request->input('idioma')]);
                }

                // Moneda (Gs., USD, etc.)
                if ($request->has('moneda')) {
                    $moneda = Configuracion::firstOrCreate(
                        ['descripcion' => 'moneda'],
                        ['observacion' => 'Gs.']
                    );
                    $moneda->observacion = $request->input('moneda');
                    $moneda->save();
                }

                // ==========================================
                // CONFIGURACIÓN SIFEN (Facturación Electrónica)
                // ==========================================
                $sifen = SifenConfiguracion::firstOrCreate([], [
                    'ambiente' => 1,
                    'establecimiento' => '001',
                    'punto_expedicion' => '001',
                    'habilitado' => false,
                ]);

                // Datos del emisor
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

                // Establecimiento y punto de expedición (códigos SET)
                $sifen->establecimiento = $request->input('sifen_establecimiento', $sifen->establecimiento);
                $sifen->punto_expedicion = $request->input('sifen_punto_expedicion', $sifen->punto_expedicion);

                // Ambiente: 1 = Testing (homologación), 2 = Producción
                $sifen->ambiente = $request->input('sifen_ambiente', $sifen->ambiente);

                // Datos geográficos según catálogo SET
                $sifen->departamento_codigo = $request->input('sifen_departamento_codigo') ?: $sifen->departamento_codigo ?: 1;
                $sifen->distrito_codigo = $request->input('sifen_distrito_codigo') ?: $sifen->distrito_codigo ?: 1;
                $sifen->ciudad_codigo = $request->input('sifen_ciudad_codigo') ?: $sifen->ciudad_codigo ?: 1;
                $sifen->nombre_sucursal = $request->input('sifen_nombre_sucursal', $sifen->nombre_sucursal);

                // Tipo de contribuyente y régimen
                $sifen->tipo_contribuyente = $request->input('sifen_tipo_contribuyente', $sifen->tipo_contribuyente);
                $sifen->tipo_regimen = $request->input('sifen_tipo_regimen', $sifen->tipo_regimen);

                // Actividad económica principal (código + descripción)
                $sifen->actividad_economica_codigo = $request->input('sifen_actividad_economica_codigo', $sifen->actividad_economica_codigo);
                $sifen->actividad_economica_descripcion = $request->input('sifen_actividad_economica_descripcion', $sifen->actividad_economica_descripcion);

                // CSC = Código de Seguridad del Contribuyente (para firmar DEs)
                $sifen->csc_id = $request->input('sifen_csc_id', $sifen->csc_id);
                $sifen->csc_codigo = $request->input('sifen_csc_codigo', $sifen->csc_codigo);

                // Master switch: habilitar/deshabilitar todo SIFEN
                // Usa boolean() para capturar correctamente el value=0 del hidden input
                $sifen->habilitado = $request->boolean('sifen_habilitado');

                // Contraseña del certificado P12
                $sifen->certificado_password = $request->input('sifen_certificado_password', $sifen->certificado_password);

                // Certificado digital: se recibe como archivo .p12,
                // se codifica en base64 y se guarda en la BD.
                // Si se marca "limpiar", se elimina.
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
