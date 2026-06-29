<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SifenConfiguracion extends Model
{
    protected $table = 'sifen_configuraciones';

    protected $fillable = [
        'ruc_emisor',
        'dv',
        'razon_social',
        'direccion',
        'calle_principal',
        'numero_casa',
        'calle_secundaria',
        'complemento_direccion',
        'telefono',
        'email',
        'establecimiento',
        'punto_expedicion',
        'ambiente',
        'departamento_codigo',
        'distrito_codigo',
        'ciudad_codigo',
        'nombre_sucursal',
        'tipo_contribuyente',
        'tipo_regimen',
        'actividad_economica_codigo',
        'actividad_economica_descripcion',
        'certificado_p12',
        'certificado_password',
        'csc_id',
        'csc_codigo',
        'habilitado',
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

    public function isHabilitado(): bool
    {
        return $this->habilitado && $this->ruc_emisor && $this->dv && $this->certificado_p12;
    }

    public function getAmbienteUrl(): string
    {
        return $this->ambiente === 2
            ? 'https://sifen.set.gov.py'
            : 'https://sifen-test.set.gov.py';
    }

    public function isProduccion(): bool
    {
        return $this->ambiente === 2;
    }
}
