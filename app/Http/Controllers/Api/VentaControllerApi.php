<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;

class VentaControllerApi extends Controller
{
    public function index(): JsonResponse
    {
        $ventas = Venta::query()
            ->with([
                'usuario:id,name,email',
                'cliente:id,razonsocial,ruc,telefono,correo,direccion',
                'detalles.producto:id,descripcion,codigo,imagen',
                'pagares:id,id_venta,monto,estado,fecha_vencimiento',
            ])
            ->where('estado', 1)
            ->where('tipo_comprobante', 'CREDITO')
            ->latest('fecha_emision')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $ventas,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $venta = Venta::query()
            ->with([
                'usuario:id,name,email',
                'cliente:id,razonsocial,ruc,telefono,correo,direccion',
                'detalles.producto:id,descripcion,codigo,imagen',
                'pagares:id,id_venta,monto,estado,fecha_vencimiento',
            ])
            ->where('estado', 1)
            ->find($id);

        if (! $venta) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada o inactiva.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $venta,
        ]);
    }
}
