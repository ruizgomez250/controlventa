<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Evento;

class EventoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():View
    {
        //obtenemos los datos
        $evento = Evento::All();
        //asignar cabecera datatable
        $heads =[
            'ID','Nombre','Descripción','Tipo','Acción'
        ];
        return view('evento.index',['evento'=>$evento,'heads'=>$heads]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create():View
    {
        //
        return view('evento.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request):RedirectResponse
    {
        //$request->validate(['nombre'=>'required','descripcion'=>'required','tipo'=>'required']);//validaciòn de campos
        //for request
      //  dd($request->all());
        Evento::create($request->all());                             
        return redirect()->route('evento.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evento $evento):View
    {
        //
        return view('evento.edit',['evento'=>$evento]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Evento $evento):RedirectResponse
    {
        //
        $evento->update($request->all());
        return redirect()->route('evento.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evento $evento)
    {
        //
        $evento->delete();
        return redirect()->route('evento.index');
    }
}
