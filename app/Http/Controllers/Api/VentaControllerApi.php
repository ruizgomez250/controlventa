<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Pagare;
use Illuminate\Http\Request;

class VentaControllerApi extends Controller
{
     
    public function index()
{
    $ventas = Venta::with([
        'usuario:id,name,email',
        'cliente:id,razonsocial,ruc,telefono,correo,direccion',
        'detalles.producto:id,descripcion,codigo,pventa,imagen',
        'pagare:id,id_venta,monto'
    ])
    ->where('estado', 1)
    ->where('tipo_comprobante', 'CREDITO') // 🔹 Filtra solo las ventas con comprobante CREDITO
    ->get()
    ->map(function ($venta) {

        // Calcular total de la venta
        $venta->total = (int) round(
            $venta->detalles->sum(function ($detalle) {
                $precio = $detalle->producto->pventa ?? 0;
                return $detalle->cantidad * $precio;
            })
        );

        // Limpiar decimales en precios y cantidades
        $venta->detalles->transform(function ($detalle) {
            $detalle->producto->pventa = (int) round($detalle->producto->pventa ?? 0);
            $detalle->cantidad = (int) round($detalle->cantidad ?? 0);
            return $detalle;
        });

        // Agregar monto del pagaré si existe
        $venta->monto_pagare = $venta->pagare ? (int) round($venta->pagare->monto) : 0;

        return $venta;
    });

    return response()->json([
        'success' => true,
        'data' => $ventas
    ]);
}
    
    public function show($id)
    {
        $venta = Venta::with([
            'usuario:id,name,email',
            'cliente:id,razonsocial,ruc,telefono,correo,direccion',
            'detalles.producto:id,descripcion,codigo,pventa,imagen'
        ])
        ->where('estado', 1)
        ->find($id);

        if (!$venta) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada o no está activa'
            ], 404);
        }

        // Calculamos total y limpiamos decimales
        $venta->total = (int) round(
            $venta->detalles->sum(function ($detalle) {
                return $detalle->cantidad * $detalle->producto->pventa;
            })
        );

        $venta->detalles->transform(function ($detalle) {
            $detalle->producto->pventa = (int) $detalle->producto->pventa;
            $detalle->cantidad = (int) $detalle->cantidad;
            return $detalle;
        });

        return response()->json([
            'success' => true,
            'data' => $venta
        ]);
    }
}
