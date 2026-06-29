// Inicialización de variables
let tableBody = $('#tableBody');
let elementos = [];
let selected = null;

// Función para automatizar la creación de etiquetas
const crearTag = (data) => Array.isArray(data) ? data.map(item => `<span class='tag' style="background-color: ${item.color};">${item.tag}</span>`).join('') : '';

// Función para llenar la tabla
const llenarTabla = (data) => {
    if (Array.isArray(data)) {
        $(tableBody).html(data.map(item => `<tr data-id='${item.id}'>
            <td>${item.itemName}</td>
            <td>${item.description}</td>
            <td>${item.price}</td>
            <td>
                <div style='display: flex; gap: 2px'>${crearTag(item.tags)}</div>
            </td>
            </tr>`
        ).join(''));
        return;
    }
    // Se muestra un mensaje en caso de no tener información
    $(tableBody).html(`<tr>
        <td colspan='4'>${data}</td>
    </tr>`);
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

$(() => {
    // Función para hacer la consulta a la BD
    const consultaBD = () => {
        elementos = []; // Se limpian los elementos obtenidos
        llenarTabla('Loading'); // Mensaje de carga

        $.ajax({ url: 'php/Controller.php', method: 'GET', dataType: 'json', data: { accion: 'consultar'},
            success: (response) => {
                if (response.success) {
                    elementos = response.data; // Se guarda en caché
                    llenarTabla(response.data);
                } else
                    llenarTabla(response.message);
            },
            error: (error) => {
                llenarTabla(error?.message ?? 'Unexpected server error.');
            }
        })
    }

    consultaBD();

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
