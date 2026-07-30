<?php

namespace App\Http\Controllers;

use App\Models\Dominio;
use App\Models\Opcion;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function storeCat(Request $request)
    {
        abort_unless(auth()->user()->can('producto crear'), 403);

        //Validar la solicitud
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'id_dominio' => 'required|integer|in:3,5',
        ]);
        $descAux = '';
        if ($request->input('id_dominio') == 3) {
            $descAux = 'CATEGORIA';
        } elseif ($request->input('id_dominio') == 5) {
            $descAux = 'UNIDAD MEDIDA';
        }

        // Verificar si el dominio existe, y si no, crearlo
        $dominio = Dominio::where('id', $request->input('id_dominio'))->first();
        if (! $dominio) {
            $dominio = Dominio::create([
                'id' => $request->input('id_dominio'),
                'descripcion' => $descAux,
                'estado' => 'activado',
            ]);
        }

        // Crear una nueva descripción en la base de datos
        $opcion = Opcion::create($validated);
        $responseData = [
            'id' => $opcion->id,
            'descripcion' => $opcion->descripcion,
            'data' => [$opcion->descripcion],
        ];

        return response()->json($responseData);

        return response()->json($request->input());
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('producto borrar'), 403);

        try {
            $opcion = Opcion::findOrFail($id);
            $opcion->delete();

            return response()->json(['message' => 'Categoría eliminada con éxito.', 'id' => $id]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Maneja la excepción cuando no se encuentra el objeto
            return response()->json(['error' => 'La categoría no se pudo eliminar porque no existe.'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            // Maneja una excepción que indica restricciones de clave foránea
            return response()->json(['error' => 'No se puede eliminar la categoría debido a restricciones de clave foránea.'], 422);
        }
    }
}
