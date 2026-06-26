let testPhpUrl = 'php/test.php';

const generarAlerta = (texto) => {
    alert(texto);
}

const generarEtiqueta = (texto) => {
    $('body').append(`${texto}`);
}

$('button').on('click', function () {
    $.ajax({
        url: testPhpUrl,
        method: 'POST',
        dataType: 'json',
        data: { accion: $(this).val() },
        success: (resp) => {
            console.log(resp.resultado);

            if (resp.accion === 'alerta')
                generarAlerta(resp.resultado);
            else if (resp.accion === 'etiqueta')
                generarEtiqueta(resp.resultado);
        }
    })
});