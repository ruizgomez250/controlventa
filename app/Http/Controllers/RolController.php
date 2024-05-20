<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class RolController extends Controller
{

    public function index(): View
    {
        $tienePermiso = $this->verificarPermiso('Rol', 'crear');
        if ($tienePermiso) {
            $usuarios = User::all();
            return view('roles.index', compact('usuarios'));
        } else {
            return view('sinpermiso.index');
        }
    }
    function verificaModelo(string $nombremodelo)
    {

        $idusuario = auth()->id();
        $permisos = Rol::where('id_usuario', $idusuario)
            ->where('nombre_modelo', $nombremodelo)
            ->first();
        if ($permisos) {
            return true;
        } else {
            return false;
        }
        // $ventas = [
        //     'text'       => 'Ventas',
        //     'icon'       => 'fas fa-money-check-alt',
        //     'icon_color' => 'success',
        //     'classes'    => 'custom-icon-box-black',
        //     'content'    => '<i class="fas fa-box"></i>',
        //     'submenu'    => [
        //         [
        //             'text' => 'Lista de Ventas',
        //             'url'  => '/venta',
        //         ],
        //         [
        //             'text' => 'Registrar Ventas',
        //             'url'  => '/venta/create',
        //         ],              
        //     ],
        // ];
    }
    function verificarPermiso(string $nombremodelo, string $accion)
    {
        //accion= leer,crear,borrar,editar
        $idusuario = auth()->id();
        $permisos = Rol::where('id_usuario', $idusuario)
            ->where('nombre_modelo', $nombremodelo)
            ->first();
        switch ($accion) {
            case 'leer':
                if (!is_null($permisos) && !is_null($permisos->leer)) {
                    $resultado = $permisos->leer == 1 ? true : false;
                    return $resultado;
                } else {
                    return false;
                }
                break;
            case 'crear':
                if (!is_null($permisos) && !is_null($permisos->leer)) {
                    $resultado = $permisos->crear == 1 ? true : false;
                    return $resultado;
                } else {
                    return false;
                }
                break;
            case 'editar':
                if (!is_null($permisos) && !is_null($permisos->leer)) {
                    $resultado = $permisos->editar == 1 ? true : false;
                    return $resultado;
                } else {
                    return false;
                }
                break;
            case 'borrar':
                if (!is_null($permisos) && !is_null($permisos->leer)) {
                    $resultado = $permisos->borrar == 1 ? true : false;
                    return $resultado;
                } else {
                    return false;
                }
                break;
            default:
                return false;
                break;
        }
    }
    public function getRoles($id)

    {
        $tienePermiso = $this->verificarPermiso('Rol', 'leer');

        if ($tienePermiso) {
            $permisos = Rol::where('id_usuario', $id)->get();
            return response()->json($permisos);
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    public function store(Request $request)
    {



        $tienePermiso = $this->verificarPermiso('Rol', 'crear');
        if ($tienePermiso) {
            try {
                DB::beginTransaction();
                $idUsuario = $request->input('id_usuario');
                $roles = Rol::where('id_usuario', $idUsuario)->get();

                // Verificar si se encontraron roles
                if ($roles->isNotEmpty()) {
                    foreach ($roles as $rol) {
                        // Establecer todos los permisos en 0
                        $rol->leer = 0;
                        $rol->borrar = 0;
                        $rol->crear = 0;
                        $rol->editar = 0;
                        $rol->save();
                    }
                }
                $permisos = $request->input('permisos');
                $bandera = 'sindato';
                foreach ($permisos as $nombreVariable => $valor) {
                    // Analizar si el valor es "on"

                    $posicion = strrpos($nombreVariable, "_"); // Encuentra la última aparición del guion bajo
                    $nombreClase = substr($nombreVariable, 0, $posicion);
                    $accion = substr($nombreVariable, $posicion + 1);

                    if ($bandera !=  $nombreClase) {
                        $permisosAux = Rol::where('id_usuario', $idUsuario)
                            ->where(DB::raw('LOWER(nombre_modelo)'), strtolower($nombreClase))
                            ->first();
                            
                        if (is_null($permisosAux)) {
                            $permisosAux = new Rol();
                            $permisosAux->nombre_modelo =$nombreClase;
                            $permisosAux->id_usuario=$idUsuario;
                            $permisosAux->leer =0;
                            $permisosAux->crear=0;
                            $permisosAux->editar =0;
                            $permisosAux->borrar=0;
                            
                        }
                        $bandera = $nombreClase;
                    }

                    switch ($accion) {
                        case 'leer':
                            $permisosAux->leer = $valor === "on" ? 1 : 0;
                            
                            break;
                        case 'crear':
                            $permisosAux->crear = $valor === "on" ? 1 : 0;
                            break;
                        case 'editar':
                            $permisosAux->editar = $valor === "on" ? 1 : 0;
                            break;
                        case 'borrar':
                            $permisosAux->borrar = $valor === "on" ? 1 : 0;
                            break;
                    }
                    
                    $permisosAux->save();
                }
                DB::commit();

                // Redirigir con mensaje de éxito
                return redirect()->route('rol.index')->with('success', 'Producto creado exitosamente');
            } catch (Exception $e) {
                // Rollback de la transacción en caso de excepción
                DB::rollBack();

                // Capturar excepciones y redirigir con mensaje de error
                return redirect()->back()->with('error', 'Error al agregar los roles: ' . $e->getMessage());
            }
        } else {
            return view('sinpermiso.index');
        }
    }
}
