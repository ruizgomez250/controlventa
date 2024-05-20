<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Opcion;
use Livewire\WithPagination;

class CrearCategoriaComponent extends Component
{
    use WithPagination;

	protected $paginationTheme = 'bootstrap';
    public $descripcion,$id_dominio;

    public function render()
    {
        return view('livewire.crear-categoria-component');
    }
    public function storeCat()
    {
         $this->validate([
		 'descripcion' => 'required',
		'id_dominio' => 'required',
        ]);

        Opcion::create([ 
			'descripcion' => $this-> descripcion,
			'id_dominio' => $this-> id_dominio
        ]);
        
        $this->resetInput();
		// $this->dispatchBrowserEvent('closeModal');
		// session()->flash('message', 'Categoria Successfully created.');
    }
    public function guardar()
    {
        dd('se ejecuta');
        // $this->validate([
        // 'descripcion' => 'required',
        // ]);

        // Opcion::create([ 
        // 	'descripcion' => $this-> descripcion,
        // 	'id_dominio' => $this-> id_dominio,
        // ]);

        // $this->resetInput();
        // $this->dispatchBrowserEvent('closeModal');
        // session()->flash('message', 'Categoria Successfully created.');
    }
}
