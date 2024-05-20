<div>
    <div class="modal fade" id="addCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class=" custom-heading" id="exampleModalLabel">Categoria del Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <form>
                            @csrf
                            <div class="form-group">
                                <x-adminlte-input wire:model="descripcion" name="descripcion" label="Descripcion"
                                    placeholder="Descripcion" fgroup-class="col-md-12"
                                    label-class="form-control text-danger">
                                    {{-- <input wire:model="descripcion" type="text" class="form-control" placeholder="Descripción"> --}}

                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-danger">
                                            <i class="fas fa-tag "></i>
                                        </div>
                                    </x-slot>
                                </x-adminlte-input>
                                <input wire:model="id_dominio" type="hidden" class="form-control" id="id_dominio"
                                    name="id_dominio" value="3">
                            </div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button  wire:click.prevent="storeCat()" class="btn btn-primary">Guardar</button>
                        </form>
                        {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" onclick="guardarCategoria()">Guardar</button> --}}

                    </div>
                </div>
                <div class="modal-footer">
                    <x-adminlte-datatable id="table1" :heads="$headcat" head-theme="dark" theme="light" striped
                        hoverable with-buttons>
                        @foreach ($categoria as $row)
                            <tr>
                                <td>{{ $row->descripcion }}</td>
                                <td style="float:right;">
                                    <form id="delete-form" action="{{ route('producto.destroy', [$row->id]) }}"
                                        method="post" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="delete-button">
                                            <ion-icon name="trash-outline" onclick="borrar()"><i
                                                    class="fa fa-sm fa-fw fa-trash"></i></ion-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>

                </div>


            </div>
        </div>
    </div>
</div>
