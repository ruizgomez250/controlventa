<?php

namespace Tests\Feature;

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\VentaController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ListadosPaginadosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.listados_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('listados_test');
        foreach (['clientes', 'proveedores'] as $tabla) {
            Schema::create($tabla, function (Blueprint $t) {
                $t->increments('id');
                foreach (['razonsocial', 'ruc', 'correo', 'telefono', 'celular', 'estado'] as $columna) $t->string($columna)->nullable();
            });
            for ($i = 1; $i <= 61; $i++) {
                DB::table($tabla)->insert(['id' => $i, 'razonsocial' => $i === 1 ? 'Persona buscada' : 'Persona ' . $i, 'ruc' => '123456']);
            }
        }
        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Operador']);
        foreach (['compras_cab', 'ventas'] as $tabla) {
            Schema::create($tabla, function (Blueprint $t) {
                $t->increments('id');
                $t->integer('id_usuario');
                $t->integer('id_cliente')->nullable();
                $t->integer('id_proveedor')->nullable();
                foreach (['fecha_emision', 'nro_factura', 'numero_factura', 'timbrado', 'timbrado_factura', 'condicion_de_compra', 'tipo_comprobante', 'total', 'total_compra', 'sifen_estado'] as $columna) $t->string($columna)->nullable();
            });
            for ($i = 1; $i <= 61; $i++) {
                DB::table($tabla)->insert(['id' => $i, 'id_cliente' => $i, 'id_proveedor' => $i, 'id_usuario' => 1]);
            }
        }
        $this->actingAs(new class extends User {
            public function can($abilities, $arguments = []) { return true; }
        });
    }

    public function test_las_cuatro_listas_paginan_buscan_y_cargan_relaciones(): void
    {
        foreach ([ClienteController::class => 'clientes', ProveedorController::class => 'proveedores', CompraController::class => 'cabecera', VentaController::class => 'cabecera'] as $controller => $variable) {
            $request = Request::create('/listado', 'GET', ['page' => 2]);
            $this->app->instance('request', $request);
            $pagina = (new $controller())->index($request)->getData()[$variable];
            $this->assertInstanceOf(LengthAwarePaginator::class, $pagina);
            $this->assertSame(61, $pagina->total());
            $this->assertCount(25, $pagina);
            $this->assertSame(36, $pagina->first()->id);
            $this->assertSame(12, $pagina->last()->id);

            $request = Request::create('/listado', 'GET', ['buscar' => 'Persona buscada', 'por_pagina' => 50, 'orden' => 'id', 'direccion' => 'asc']);
            $this->app->instance('request', $request);
            $resultado = (new $controller())->index($request)->getData()[$variable];
            $this->assertSame(1, $resultado->total());
            $this->assertSame(1, $resultado->first()->id);
            $this->assertSame(50, $resultado->perPage());
            $this->assertStringContainsString('buscar=Persona', $resultado->url(2));
            if ($controller === CompraController::class || $controller === VentaController::class) {
                $this->assertTrue($resultado->first()->relationLoaded('usuario'));
                $this->assertTrue($resultado->first()->relationLoaded($controller === CompraController::class ? 'proveedor' : 'cliente'));
            }

            $request = Request::create('/listado', 'GET', ['buscar' => 'sin-coincidencias']);
            $this->app->instance('request', $request);
            $vacio = (new $controller())->index($request)->getData()[$variable];
            $this->assertSame(0, $vacio->total());
            $this->assertCount(0, $vacio);

            $request = Request::create('/listado', 'GET', ['por_pagina' => 100, 'direccion' => 'asc']);
            $this->app->instance('request', $request);
            $ascendente = (new $controller())->index($request)->getData()[$variable];
            $this->assertSame(1, $ascendente->first()->id);
            $this->assertCount(61, $ascendente);
        }
    }

    public function test_rechaza_tamanos_sin_limite_y_columnas_no_permitidas(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new ClienteController())->index(Request::create('/cliente', 'GET', ['por_pagina' => 100000, 'orden' => 'columna_invalida']));
    }
}
