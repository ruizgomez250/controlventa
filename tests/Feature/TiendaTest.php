<?php

namespace Tests\Feature;

use App\Models\TiendaConfiguracion;
use App\Models\TiendaPedido;
use App\Models\TiendaProducto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class TiendaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost', 'cache.default' => 'array']);
        URL::forceRootUrl('http://localhost');
        $this->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);
        $this->crearSistema('tienda_a');
        TiendaConfiguracion::actual()->update(['whatsapp' => '595981123456']);
    }

    private function crearSistema(string $nombre): void
    {
        config(['database.connections.' . $nombre => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection($nombre);
        foreach (['opciones', 'clothing_sizes', 'garment_types'] as $tabla) {
            Schema::create($tabla, function (Blueprint $t) { $t->id(); $t->string('name')->nullable(); $t->string('descripcion')->nullable(); });
        }
        DB::table('opciones')->insert(['id' => 1, 'descripcion' => 'Ropa infantil']);
        Schema::create('productos', function (Blueprint $t) {
            $t->id();
            foreach (['codigo', 'descripcion', 'detalle', 'brand', 'color', 'imagen'] as $c) $t->string($c)->nullable();
            foreach (['pventa', 'pcosto', 'stock'] as $c) $t->decimal($c, 14, 2)->default(0);
            foreach (['id_categoria', 'clothing_size_id', 'garment_type_id'] as $c) $t->integer($c)->nullable();
            $t->integer('estado')->default(1);
        });
        (require database_path('migrations/2026_11_12_000001_create_tienda_module.php'))->up();
        DB::table('productos')->insert(['id' => 1, 'codigo' => 'MINI-1', 'descripcion' => 'Remera Mini', 'pventa' => 50000, 'pcosto' => 15000, 'stock' => 5, 'id_categoria' => 1]);
    }

    private function pedido(array $extra = []): array
    {
        return array_replace([
            'referencia' => (string) Str::uuid(), 'token' => Str::random(64),
            'nombre' => 'Cliente Mini', 'telefono' => '0981123456', 'entrega' => 'retiro',
            'items' => [['id' => 1, 'cantidad' => 2, 'precio' => 50000]],
        ], $extra);
    }

    public function test_catalogo_es_publico_paginado_y_no_expone_costos(): void
    {
        for ($i = 2; $i <= 16; $i++) DB::table('productos')->insert(['id' => $i, 'codigo' => 'M-' . $i, 'descripcion' => 'Ropa ' . $i, 'pventa' => 70000, 'stock' => 3]);
        TiendaProducto::create(['producto_id' => 2, 'visible' => false]);
        DB::table('productos')->where('id', 3)->update(['estado' => 0]);
        $this->getJson('/api/tienda/v1/inicio')->assertOk()->assertJsonPath('tienda.nombre', 'MiniFashion')->assertJsonPath('categorias.0.nombre', 'Ropa infantil');
        $response = $this->getJson('/api/tienda/v1/productos')->assertOk()->assertJsonCount(12, 'data')->assertJsonPath('meta.total', 14);
        foreach ($response->json('data') as $p) { $this->assertArrayNotHasKey('pcosto', $p); $this->assertArrayNotHasKey('id_proveedor', $p); }
        $this->getJson('/api/tienda/v1/productos?page=2')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/tienda/v1/productos/2')->assertNotFound();
        $this->getJson('/api/tienda/v1/productos?buscar=Remera')->assertOk()->assertJsonPath('data.0.id', 1)->assertJsonPath('meta.total', 1);
    }

    public function test_ofertas_premium_vencimiento_y_orden_por_precio_real(): void
    {
        TiendaProducto::create(['producto_id' => 1, 'premium' => true, 'precio_oferta' => 30000, 'oferta_hasta' => now()->addDay()]);
        DB::table('productos')->insert(['id' => 2, 'descripcion' => 'Otra remera', 'codigo' => 'M-2', 'pventa' => 40000]);
        $this->getJson('/api/tienda/v1/productos?seccion=ofertas&orden=precio_asc')->assertOk()->assertJsonPath('data.0.precio', 30000)->assertJsonPath('data.0.descuento', 40);
        $this->getJson('/api/tienda/v1/productos?orden=precio_asc')->assertOk()->assertJsonPath('data.0.id', 1);
        $this->getJson('/api/tienda/v1/productos?seccion=premium')->assertOk()->assertJsonCount(1, 'data');
        $this->travel(2)->days();
        $this->getJson('/api/tienda/v1/productos?seccion=ofertas')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/tienda/v1/productos/1')->assertOk()->assertJsonPath('data.precio', 50000)->assertJsonPath('data.precio_anterior', null);
    }

    public function test_pedido_valida_precios_stock_y_no_descuenta_inventario(): void
    {
        $v = $this->pedido();
        $result = $this->postJson('/api/tienda/v1/pedidos', $v)->assertCreated()->assertJsonPath('pedido.total', '100000.00');
        $this->assertStringStartsWith('https://wa.me/595981123456?text=', $result->json('whatsapp_url'));
        $this->assertSame(5.0, (float) DB::table('productos')->value('stock'));
        $this->assertSame(1, TiendaPedido::count());
        $this->postJson('/api/tienda/v1/pedidos', $v)->assertCreated();
        $this->assertSame(1, TiendaPedido::count());
        $this->postJson('/api/tienda/v1/pedidos', array_replace($v, ['nombre' => 'Otra persona']))->assertStatus(409);
        $uuid = $result->json('pedido.uuid');
        $this->getJson('/api/tienda/v1/pedidos/' . $uuid)->assertNotFound();
        $this->withHeader('X-Pedido-Token', $v['token'])->getJson('/api/tienda/v1/pedidos/' . $uuid)->assertOk()->assertJsonPath('pedido.estado', 'pendiente')->assertJsonMissingPath('pedido.telefono')->assertJsonMissingPath('pedido.token_hash');
        $this->postJson('/api/tienda/v1/pedidos', $this->pedido(['items' => [['id' => 1, 'cantidad' => 1, 'precio' => 1]]]))->assertStatus(409);
        $this->postJson('/api/tienda/v1/pedidos', $this->pedido(['items' => [['id' => 1, 'cantidad' => 6, 'precio' => 50000]]]))->assertStatus(422);
    }

    public function test_pedido_necesita_direccion_y_tienda_configurada(): void
    {
        $this->postJson('/api/tienda/v1/pedidos', $this->pedido(['entrega' => 'envio']))->assertUnprocessable()->assertJsonValidationErrors('direccion');
        TiendaConfiguracion::actual()->update(['whatsapp' => null]);
        $this->postJson('/api/tienda/v1/pedidos', $this->pedido())->assertStatus(422);
        TiendaConfiguracion::actual()->update(['habilitada' => false]);
        $this->getJson('/api/tienda/v1/inicio')->assertStatus(503);
        $this->getJson('/api/tienda/v1/productos')->assertStatus(503);
    }

    public function test_sistemas_con_mismos_ids_no_comparten_catalogo_configuracion_ni_pedidos(): void
    {
        $v = $this->pedido();
        $uuid = $this->postJson('/api/tienda/v1/pedidos', $v)->assertCreated()->json('pedido.uuid');
        $idA = TiendaConfiguracion::actual()->identificador;
        $this->crearSistema('tienda_b');
        TiendaConfiguracion::actual()->update(['nombre' => 'Otra tienda', 'whatsapp' => '595982987654']);
        DB::table('productos')->where('id', 1)->update(['descripcion' => 'Solo de tienda B']);
        $this->assertNotSame($idA, TiendaConfiguracion::actual()->identificador);
        $this->getJson('/api/tienda/v1/productos/1')->assertOk()->assertJsonPath('data.nombre', 'Solo de tienda B');
        $this->withHeader('X-Pedido-Token', $v['token'])->getJson('/api/tienda/v1/pedidos/' . $uuid)->assertNotFound();
        $this->assertSame(0, TiendaPedido::count());
        DB::setDefaultConnection('tienda_a');
        $this->getJson('/api/tienda/v1/productos/1')->assertOk()->assertJsonPath('data.nombre', 'Remera Mini');
        $this->assertSame(1, TiendaPedido::count());
    }

    public function test_imagen_publica_solo_de_producto_visible_y_administracion_requiere_login(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('productos/mini.png', 'imagen-de-prueba');
        DB::table('productos')->where('id', 1)->update(['imagen' => 'productos/mini.png']);
        $this->get('/api/tienda/v1/imagenes/1')->assertOk();
        TiendaProducto::create(['producto_id' => 1, 'visible' => false]);
        $this->getJson('/api/tienda/v1/imagenes/1')->assertNotFound();
        $this->get('/tienda-configuracion')->assertRedirect(route('login'));
        $this->get('/tienda-pedidos')->assertRedirect(route('login'));
        $this->get('/tienda')->assertOk()->assertSee('tienda-api')->assertSee('Mi carrito');
    }

    public function test_administrador_configura_tienda_ofertas_y_sigue_pedidos(): void
    {
        (require database_path('migrations/2026_06_17_100001_create_permission_tables.php'))->up();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs(new \App\Models\User(['name' => 'Administrador']));
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->get('/tienda-configuracion')->assertOk()->assertSee('Identidad y pedidos por WhatsApp');
        $this->put('/tienda-configuracion', [
            'nombre' => 'MiniFashion', 'descripcion' => 'Moda para los más pequeños',
            'whatsapp' => '595981111111', 'color' => '#176b4a', 'aviso' => 'Tu próximo favorito', 'habilitada' => 1,
        ])->assertRedirect();
        $this->assertSame('595981111111', TiendaConfiguracion::actual()->whatsapp);
        $this->put('/tienda-productos/1', ['visible' => 1, 'premium' => 1, 'destacado' => 1, 'precio_oferta' => 30000])->assertRedirect();
        $this->assertSame('30000.00', TiendaProducto::find(1)->precio_oferta);
        $this->putJson('/tienda-productos/1', ['visible' => 1, 'premium' => 1, 'destacado' => 1, 'precio_oferta' => 90000])->assertUnprocessable();
        $result = $this->postJson('/api/tienda/v1/pedidos', $this->pedido(['items' => [['id' => 1, 'cantidad' => 1, 'precio' => 30000]]]))->assertCreated();
        $id = $result->json('pedido.id');
        $this->get('/tienda-pedidos')->assertOk()->assertSee('Cliente Mini');
        $this->put('/tienda-pedidos/' . $id, ['estado' => 'confirmado'])->assertRedirect();
        $this->assertSame('confirmado', TiendaPedido::find($id)->estado);
    }
}
