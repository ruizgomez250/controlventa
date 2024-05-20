// Obtén una referencia a la tabla DataTable

var dataTable;

$(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#table1')) {
        // DataTable no se ha inicializado en #table1, así que lo inicializamos
        dataTable = $('#table1').DataTable({
            // Configuración de DataTables
        });
    } else {
        // DataTable ya se ha inicializado en #table1, por lo que simplemente obtenemos la instancia existente
        dataTable = $('#table1').DataTable();
    }
});

function guardarCategoria(guardarUrl,nombreFormulario,addSelect2,nombreselect2,borrar) {
    $.ajax({
        type: "POST",
        url: guardarUrl,
        data: $("#"+ nombreFormulario).serialize(),
        dataType: 'json',
        success: function(response) {
            var id = response.id;
            var descripcion= response.descripcion;

            var data=response.data;
            // for (var i = 0; i < response.data.length; i++) {
            //     if(i == 0)
            //         data=response.data[i];
            //     else
            //         data=data+','+response.data[i];
            // }
            
            console.log(data);
            if(addSelect2){
                agregarSelect2(id,descripcion,nombreselect2);
            }
            // Mostrar mensaje de éxito o hacer algo con la respuesta
            Swal.fire(
                'guardado exitoso!',
                'Presione el boton!',
                'success'
            )
            url="{{ url('/') }}/"+borrar+"/" + id;
            // Crear el botón de eliminación como una cadena HTML
            var nuevaFila ='<button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button" data-url="'+url+'" onclick="borrar(this,\'' + nombreselect2 + '\',true)">'+
                '<ion-icon name="trash-outline"><i class="fa fa-sm fa-fw fa-trash"></i></ion-icon> </button>';
            // 'filaDatos' con los datos de la fila que deseas agregar
            var filaDatos = [data,nuevaFila];
           

            // Agrega la fila a la tabla
            //dataTable.rows.add([filaDatos]).draw(false);
            var addedRow = dataTable.row.add(filaDatos).draw(false).node();
            $(addedRow).attr("data-id", id);
            $(addedRow).addClass("table-row");
            //dataTable.destroy(); // Destruye el DataTable existente
        },
        error: function(error) {
            // Manejar errores aquí
            console.log(error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Hubo problemas al intentar guardar!'
            })
        }
    });
}

function borrar(borrarcategoria,select2,utilizaselect2) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo'
    }).then((result) => {
        if (result.isConfirmed) {
            var borrarCategoriaUrl =borrarcategoria.getAttribute('data-url');//"{{ url('/borrar-categoria') }}/" + id; // Generar la URL con el id
            
            $.ajax({
                type: "DELETE", // Cambiar de POST a DELETE
                url: borrarCategoriaUrl,
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(response) {
                    if (response.hasOwnProperty('error')) {
                        // Manejar el mensaje de error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error
                        });
                    } else {
                        id=response.id;
                        $(".table-row[data-id='" + id + "']").remove();
                        dataTable.row('.table-row[data-id=' + id + ']').remove().draw(
                        false);
                        if(utilizaselect2)
                            borrarSelect2(id,select2);

                        // Manejar el mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Categoría eliminada con éxito.'
                        });
                    }
                },
                error: function(error) {
                    // Manejar errores aquí
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Hubo problemas al intentar eliminar.'
                    })
                }
            });
        }
    });
}

function borrarSelect2(valueToDelete,nombreselect) {
    
    // Establece el nuevo valor en el select2
    $('#'+nombreselect+' option[value="' + valueToDelete + '"]').remove();
    $('#' + nombreselect).trigger('change.select2');
}

function agregarSelect2(id,descripcion,nombreselect2) {

    // Crea un nuevo elemento <option>
    var nuevaOpcion = new Option(descripcion,id, true, true);
    // Agrega la nueva opción al elemento select
    $('#' + nombreselect2).append(nuevaOpcion);

    // Luego, puedes actualizar el select para que se reflejen los cambios
    $('#' + nombreselect2).trigger('change.select2');
}