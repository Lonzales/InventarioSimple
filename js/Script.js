// Inicialización de variables
let tableBody = $('#tableBody');
let elementos = [];
let selected = null;

// Función para escapar texto
const escaparHTML = (texto) => 
    texto.replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");

// Función para automatizar la creación de etiquetas
const crearTag = (data) => Array.isArray(data) ? data.map(item => `<span class='tag' style="background-color: ${escaparHTML(item.color)};">${escaparHTML(item.tag)}</span>`).join('') : '';

// Función para generar notificaciones
const generarNotificacion = (mensaje) => {
    $('#notificationContainer').prepend(`<div class="notification">
            <div style="display: flex;">
                <i class="${mensaje.success ? 'bx bx-check' : 'bx bx-x'}" style="color: ${mensaje.success ? 'green' : 'red'};"></i> <p>${mensaje.success ? 'Success' : 'Error'}</p>
            </div>
            <p id="notificationMessage">${escaparHTML(mensaje.message)}</p>
            <span class="timer"></span>
    </div>`);

    setTimeout(() => {
        $('#notificationContainer').find('.notification').last().remove();
    }, 5000);
}

// Función para llenar la tabla
const llenarTabla = (data) => {
    if (!Array.isArray(data)) {
        // Se muestra un mensaje en caso de no tener información
        $(tableBody).html(`<tr>
            <td colspan='4'>${escaparHTML(data)}</td>
        </tr>`);
        return;
    }
    
    $(tableBody).html(data.map(item => `<tr data-id='${item.id}'>
            <td>${escaparHTML(item.itemName)}</td>
            <td>${escaparHTML(item.description)}</td>
            <td>${item.price}</td>
            <td>
                <div style='display: flex; gap: 2px'>${crearTag(item.tags)}</div>
            </td>
        </tr>`).join(''));
}

// Función para llenar el menú de selección de tags
const llenarSelectTags = (data) => {
    if (!Array.isArray(data)) {
        $('#tagsDropMenu').html(`<p>${escaparHTML(data)}</p>`);
        return;
    }

    $('#tagsDropMenu').html(data.map(item => `<span>
        <input id="tag${item.id}" name="tags[]" type="checkbox" value="${item.id}"/>
        <label for="tag${item.id}">${escaparHTML(item.tag)}</label>
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
    $('#mainForm').find('input[type=text], textarea, input[type=number]').val('');
    $('#mainForm').find('input[type=checkbox]').prop('checked', false);
    $('#sideContainer').find('h1').text('Add Item');
});

// Función para llenar el formulario con los datos del item seleccionado
$('#editRowBtn').on('click', () => {
    if (!selected) {
        generarNotificacion({ success: false, message: 'No item selected for editing' });
        return;
    }

    generarNotificacion({ success: true, message: 'Item selected for editing: ' + selected.itemName });
    $('#sideContainer').find('h1').text('Edit Item');
    $('#itemId').val(selected.id);
    $('#itemName').val(selected.itemName);
    $('#itemDesc').val(selected.description);
    $('#itemPrice').val(selected.price);
    if (selected.tags) { // Selección de las tags según el item seleccionado en caso de tener
        selected.tags.forEach(tag => {
            $('#mainForm').find(`#tag${tag.id}`).prop('checked', true);
        });
    }
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
                } else {
                    llenarTabla(response.message);
                    generarNotificacion(response);
                }
            },
            error: (xhr, status, error) => {
                $response = xhr.responseJSON;
                llenarTabla($response?.message ?? 'Unexpected server error.')
                generarNotificacion($response ?? { success: false, message: 'Unexpected server error' });
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
                else {
                    llenarSelectTags('No tags found.');
                    generarNotificacion(response);
                }
            },
            error: (xhr, status, error) => {
                llenarSelectTags('Unexpected server error');
                generarNotificacion(xhr.responseJSON ?? { success: false, message: 'Unexpected server error' });
            }
        });
    }

    consultaItems();
    consultaTags();

    // Función para agregar un nuevo Item
    $('#addRowBtn').on('click', function(e) {
        e.preventDefault();
        const formData = new FormData($('#mainForm')[0]);
        formData.append('accion', $('#itemId').val() ? 'actualizarItem' : 'agregarItem');

        $.ajax({ url: 'php/Controller.php', method: 'POST', dataType: 'json', data: formData, processData: false, contentType: false,
            success: (response) => {
                generarNotificacion(response);
                if (response.success) {
                    consultaItems();
                    $('#cancelRowBtn').trigger('click'); // Se limpia el formulario
                }
            },
            error: (xhr, status, error) => 
                generarNotificacion(xhr.responseJSON ?? { success: false, message: 'Unexpected server error' })
        })
    })

    // Función para eliminar Items
    $('#removeRowBtn').on('click', () => {
        if (!selected) {
            generarNotificacion({ success: false, message: 'No item selected for deletion' });
            return;
        }

        $.ajax({ url: 'php/Controller.php', method: 'POST', dataType: 'json', data: { accion: 'eliminarItem', itemId: selected.id },
            success: (response) => {
                generarNotificacion(response);
                if (response.success) {
                    consultaItems(); // Se recarga la tabla
                }
            },
            error: (xhr, status, error) =>
                generarNotificacion(xhr.responseJSON ?? { success: false, message: 'Unexpected server error' })
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