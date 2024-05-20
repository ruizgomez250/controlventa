<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Raza;
use Illuminate\Http\Request;
use App\Models\Opcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;




class MascotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        //
        //$id=Auth::id();
        //obtenemos los datos
        $mascota = Mascota::All()->sortBy("nombre");
        //asignar cabecera datatable 'id', 'nombre', 'foto', 'edad', 'sexo_id', 'raza_id', 'propietario_id', 'estado_id'

        $heads = [
            'Código', 'Nombre', 'Raza', 'Sexo', 'Edad', 'Propietario', 'Celular', 'Acción'
        ];


        return view('mascotas.index', ['mascota' =>  $mascota, 'heads' => $heads]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        $sexo = Opcion::where('id_dominio', 6)->orderBy('descripcion')->get();
        $especie = Opcion::where('id_dominio', 8)->orderBy('descripcion')->get();
        $estado = Opcion::where('id_dominio', 9)->orderBy('descripcion')->get();
        $cliente = Cliente::orderBy('razonsocial', 'asc')->get();
        return view('mascotas.create', ['sexo' => $sexo, 'propietario' => $cliente, 'especie' => $especie, 'estadomascota' => $estado]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
        $request->validate(['nombre' => 'required']);

        /*   $request->validate([
            'imagen' => 'required|image|max:2048', // Validación de la imagen (opcional)
        ]);*/
        /*php artisan storage:link link simbolico para habilitar carpeta*/
        $imagen = $request->file('foto');
        $rutaImagen = $imagen->store('images', 'public');
        $datos = $request->all();
        $datos['foto'] = $rutaImagen;
        Mascota::create($datos);
        return redirect()->route('mascota.index');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sexo = Opcion::where('id_dominio', 6)->orderBy('descripcion')->get();
        $especie = Opcion::where('id_dominio', 8)->orderBy('descripcion')->get();
        $estadomascota = Opcion::where('id_dominio', 9)->orderBy('descripcion')->get();
        $propietario = Cliente::orderBy('razonsocial', 'asc')->get();
        $mascota = Mascota::find($id);
        //  dd($mascota);
        return view('mascotas.edit', compact('mascota', 'sexo', 'especie', 'estadomascota', 'propietario'));
    }

    /**
     * Update the specified resource in storage.
     */
    /*
 public function update(Request $request, Mascota $mascota)
    {
        $mascotaData = $request->only('nombre','edad','sexo_id','raza_id','propietario_id','estado_id','foto');     
        // 
        if ($request->hasFile('foto')) {
            // Eliminar la imagen anterior si existe            
            Storage::delete('public/' . $mascota->foto);
            $imagen = $request->file('foto');
            // Subir y almacenar la nueva imagen
            $rutaImagen = $imagen->store('images', 'public');
            $mascotaData['foto'] = $rutaImagen;
            
        }  
       
        $mascota->update($mascotaData);
    
        return redirect()->route('mascota.index');
    }
*/


    public function update(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);
        // Actualizar los campos de la mascota
        $mascota->nombre = $request->input('nombre');
        $mascota->edad = $request->input('edad');
        $mascota->sexo_id = $request->input('sexo_id');
        $mascota->raza_id = $request->input('raza_id');
        $mascota->propietario_id = $request->input('propietario_id');
        $mascota->estado_id = $request->input('estado_id');

        if ($request->hasFile('foto')) {
            if ($mascota->foto) {
                Storage::delete('public/' . $mascota->foto);
            }
            $imagen = $request->file('foto');
            $rutaImagen = $imagen->store('images', 'public');
            $mascota->foto = $rutaImagen;
        }
        $mascota->update();
        return redirect()->route('mascota.index')->with('success', 'La mascota se ha actualizado correctamente.');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $mascota = Mascota::find($id);
        $mascota->delete();
        return redirect()->route('mascota.index');
    }



    public function consulta(int $id)
    {
        $resultados = Raza::where('claseanimal_id', $id)->get(); // Realizar consulta utilizando el ID de la categoría
        return response()->json($resultados);
    }

    public function consultatwo()
    {
        $datos = Mascota::with('sexoanimal', 'razaanimal', 'propietario')->get();
        return response()->json([
            'datos' => $datos
        ]);
    }

    public function consultamascota(Request $request)
    {
        $id = $request->get('id');   
        $datos = Mascota::where('id', $id)->with('sexoanimal', 'razaanimal', 'propietario')->get();
        return response()->json($datos);
    }
}
