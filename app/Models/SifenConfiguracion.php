<?php

namespace App\Models;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que almacena la configuración de Facturación Electrónica SIFEN.
 * 
 * Tabla: sifen_configuraciones (una sola fila, firstOrCreate).
 * 
 * Datos clave que guarda:
 *  - RUC/DV del emisor
 *  - Dirección completa (calle, número, complemento, departamento/distrito/ciudad)
 *  - Certificado digital P12 (en base64) y su contraseña
 *  - CSC (Código de Seguridad del Contribuyente): ID + código de 32 caracteres
 *  - Actividad económica principal
 *  - Ambiente: 1 = Testing, 2 = Producción
 */
class SifenConfiguracion extends Model
{
    protected $table = 'sifen_configuraciones';

    protected $fillable = [
        'ruc_emisor',               // RUC del emisor (2-8 dígitos)
        'dv',                       // Dígito verificador del RUC
        'razon_social',             // Razón social del emisor
        'direccion',                // Dirección completa (respaldo)
        'calle_principal',          // Calle principal (requerido por SET)
        'numero_casa',              // Número de casa
        'calle_secundaria',         // Calle secundaria
        'complemento_direccion',    // Complemento (piso, oficina, etc.)
        'telefono',
        'email',
        'establecimiento',          // Código de establecimiento (001 por defecto)
        'punto_expedicion',         // Punto de expedición (001 por defecto)
        'ambiente',                 // 1 = Testing (homologación), 2 = Producción
        'departamento_codigo',      // Código SET de departamento (1-20)
        'distrito_codigo',          // Código SET de distrito
        'ciudad_codigo',            // Código SET de ciudad
        'nombre_sucursal',          // Nombre comercial de la sucursal
        'tipo_contribuyente',       // 1=Física, 2=Jurídica
        'tipo_regimen',             // 1=General, 2=Pequeño, 3=Integrado, 4=Agropecuario
        'actividad_economica_codigo', // Código SET de actividad económica
        'actividad_economica_descripcion', // Descripción textual
        'certificado_p12',          // Certificado digital en base64
        'certificado_password',     // Contraseña del P12
        'csc_id',                   // ID del CSC (Código de Seguridad del Contribuyente)
        'csc_codigo',               // Código CSC (32 caracteres, firmas DE)
        'habilitado',               // Master switch: true/false
    ];

    protected $casts = [
        'habilitado' => 'boolean',
        'ambiente' => 'integer',
        'departamento_codigo' => 'integer',
        'distrito_codigo' => 'integer',
        'ciudad_codigo' => 'integer',
        'tipo_contribuyente' => 'integer',
        'tipo_regimen' => 'integer',
        'actividad_economica_codigo' => 'integer',
        'establecimiento' => 'integer',
        'punto_expedicion' => 'integer',
    ];

    /**
     * Verifica si SIFEN está listo para emitir:
     * requiere habilitado=true + RUC + DV + certificado P12 cargado.
     */
    public function isHabilitado(): bool
    {
        return $this->habilitado && $this->ruc_emisor && $this->dv && $this->certificado_p12;
    }

    /** Retorna la URL base del ambiente SIFEN (producción o testing) */
    public function getAmbienteUrl(): string
    {
        return $this->ambiente === 2
            ? 'https://sifen.set.gov.py'
            : 'https://sifen-test.set.gov.py';
    }

    /** ¿Está configurado para producción? (ambiente === 2) */
    public function isProduccion(): bool
    {
        return $this->ambiente === 2;
    }

    /** Relación con el modelo Departamento local */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_codigo', 'codigo');
    }

    /** Obtiene el nombre del departamento (local o catálogo SET) */
    public function getDepartamentoNombre(): ?string
    {
        $dept = $this->departamento;
        return $dept ? $dept->nombre : \App\Helpers\SifenCatalogo::getDepartamento((int)$this->departamento_codigo);
    }

    /** Obtiene el nombre del distrito (local o catálogo SET) */
    public function getDistritoNombre(): ?string
    {
        $distrito = Distrito::where('codigo', (int)$this->distrito_codigo)
            ->where('departamento_codigo', (int)$this->departamento_codigo)
            ->first();
        return $distrito ? $distrito->nombre : \App\Helpers\SifenCatalogo::getDistrito(
            (int)$this->departamento_codigo,
            (int)$this->distrito_codigo
        );
    }

    /** Obtiene el nombre de la ciudad (local o catálogo SET) */
    public function getCiudadNombre(): ?string
    {
        $distrito = Distrito::where('codigo', (int)$this->distrito_codigo)
            ->where('departamento_codigo', (int)$this->departamento_codigo)
            ->first();
        if (!$distrito) {
            return \App\Helpers\SifenCatalogo::getCiudad(
                (int)$this->departamento_codigo,
                (int)$this->distrito_codigo,
                (int)$this->ciudad_codigo
            );
        }
        $ciudad = Ciudad::where('codigo', (int)$this->ciudad_codigo)
            ->where('distrito_id', $distrito->id)
            ->first();
        return $ciudad ? $ciudad->nombre : null;
    }
}
