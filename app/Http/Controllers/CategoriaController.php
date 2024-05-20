<?php

namespace App\Http\Controllers;

use App\Models\Opcion;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function storeCat(Request $request)
    {
       // dd('hola mundo');
        //Validar la solicitud
         $request->validate([
             'descripcion' => 'required|string|max:255',
         ]);

        // Crear una nueva descripción en la base de datos
        $opcion=Opcion::create($request->all());
        $responseData = [
            'id' => $opcion->id,
            'descripcion'=>$opcion->descripcion,
            'data'=>[$opcion->descripcion],
          ];
          
        return response()->json($responseData);
     //return response()->json($request);
    }
    public function destroy($id)
    {
        try {
            $opcion = Opcion::findOrFail($id);
            $opcion->delete();

            return response()->json(['message' => 'Categoría eliminada con éxito.','id' =>$id]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Maneja la excepción cuando no se encuentra el objeto
            return response()->json(['error' => 'La categoría no se pudo eliminar porque no existe.'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            // Maneja una excepción que indica restricciones de clave foránea
            return response()->json(['error' => 'No se puede eliminar la categoría debido a restricciones de clave foránea.'], 422);
        }
    }
    
    
}
