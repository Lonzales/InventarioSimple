// Inicialización de variables
let tableBody = $('#tableBody');
let elementos = [];
let selected = null;

// Función para automatizar la creación de etiquetas
const crearTag = (data) => Array.isArray(data) ? data.map(item => `<span class='tag' style="background-color: ${item.color};">${item.tag}</span>`).join('') : '';

// Función para llenar la tabla
const llenarTabla = (data) => {
    if (!Array.isArray(data)) {
        // Se muestra un mensaje en caso de no tener información
        $(tableBody).html(`<tr>
            <td colspan='4'>${data}</td>
        </tr>`);
        return;
    }
    
    $(tableBody).html(data.map(item => `<tr data-id='${item.id}'>
            <td>${item.itemName}</td>
            <td>${item.description}</td>
            <td>${item.price}</td>
            <td>
                <div style='display: flex; gap: 2px'>${crearTag(item.tags)}</div>
            </td>
        </tr>`).join(''));
}

// Función para llenar el menú de selección de tags
const llenarSelectTags = (data) => {
    if (!Array.isArray(data)) {
        $('#tagsDropMenu').html(`<p>${data}</p>`); // <-- Mensaje de error en caso de no tener información, se cambia por notificaciones en el futuro
        return;
    }

    $('#tagsDropMenu').html(data.map(item => `<span>
        <input id="tag${item.id}" name="tags[]" type="checkbox" value="${item.id}"/>
        <label for="tag${item.id}">${item.tag}</label>
    </span>`).join(''));
}

// Función en proceso
const evaluarRotacion = () => {
    $('#sideMenuBtn i').toggleClass('rotateArrow')
}

// Función para la animación del deslizamiento
$('#sideMenuBtn').on('click', () => {
    $('#container').toggleClass('moveLeft');
    $('#sideContainer').toggleClass('moveRight');

    evaluarRotacion();
});

// Función para mostrar un error en los inputs al superar el límite de caracteres
$('.input').on('keyup', function () {
    if ($(this).val().length >= $(this).prop('maxlength') && !$(this).hasClass('error')) {
        $(this).addClass('error');
        
        setTimeout(() => {
            $(this).removeClass('error');
        }, 500);
    }
});

// Función para mostrar el menú de selección de tags
$('.selectInput').on('click', function (e) {
    e.stopPropagation(); // Evita cerrar el mismo menú al hacer click en el
    $(this).find('div.selectContent').show();
});

$(document).on('click', function () {
    $('div.selectContent').hide();
});

// Se quita toda la selección de los elementos del formulario al presionar el botón de cancelar
$('#cancelRowBtn').on('click', () => {
    $('#mainForm').find('input, textarea').val('');
    $('#mainForm').find('input[type=checkbox]').prop('checked', false);
});

$(() => {
    // Función para hacer la consulta a la BD
    const consultaItems = () => {
        elementos = []; // Se limpian los elementos obtenidos
        selected = null;
        llenarTabla('Loading...'); // Mensaje de carga

        $.ajax({ url: 'php/Controller.php', method: 'GET', dataType: 'json', data: { accion: 'consultarItems'},
            success: (response) => {
                if (response.success) {
                    elementos = response.data; // Se guarda en caché
                    llenarTabla(response.data);
                } else
                    llenarTabla(response.message);
            },
            error: (xhr, status, error) => {
                $response = xhr.responseJSON;
                llenarTabla($response?.message ?? 'Unexpected server error.')
            }
        })
    }

    // Función para obtener todos los Tags
    const consultaTags = () => {
        llenarSelectTags('Loading...');

        $.ajax({ url: 'php/Controller.php', method: 'GET', dataType: 'json', data: { accion: 'consultarTags' },
            success: (response) => {
                if (response.success)
                    llenarSelectTags(response.data);
                else
                    llenarSelectTags('No tags found.');
            },
            error: () => {
                llenarSelectTags('Unexpected server error');
            }
        });
    }

    consultaItems();
    consultaTags();

    // Función para agregar un nuevo Item
    $('#addRowBtn').on('click', function(e) {
        e.preventDefault();
        const formData = new FormData($('#mainForm')[0]);
        formData.append('accion', 'agregarItem');

        $.ajax({ url: 'php/Controller.php', method: 'POST', dataType: 'json', data: formData, processData: false, contentType: false,
            success: (response) => {
                if (response.success) {
                    consultaItems();
                    $('#cancelRowBtn').trigger('click'); // Se limpia el formulario
                }
            },
            error: (xhr, status, error) => {
                console.log(xhr.responseJSON);
            }
        })
    })

    // Función para eliminar Items
    $('#removeRowBtn').on('click', () => {
        if (!selected) {
            console.log('No item selected for deletion.'); // <-- Se va a cambiar por notificaciones
            return;
        }

        $.ajax({ url: 'php/Controller.php', method: 'POST', dataType: 'json', data: { accion: 'eliminarItem', itemId: selected.id },
            success: (response) => {
                if (response.success) {
                    consultaItems(); // Se recarga la tabla
                }
            },
            error: (xhr, status, error) => {
                console.log(xhr.responseJSON);
            }
        });
    });

    // Función para seleccionar elementos de la tabla
    tableBody.on('click', 'tr', function () {
        let auxId = Number($(this).data('id')); // Auxiliar para obtener id
        tableBody.find('tr').removeClass('selected');

        if (selected && selected.id === auxId) { // Si se repite la selección se desmarca
            selected = null;
            return;
        }
        
        selected = elementos.find(item => item.id === auxId)
        $(this).addClass('selected');
    });
});