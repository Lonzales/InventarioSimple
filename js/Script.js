const evaluarRotacion = () => {
    $('#sideMenuBtn i').toggleClass('rotateArrow')
}

$('#sideMenuBtn').on('click', () => {
    $('#container').toggleClass('moveLeft');
    $('#sideContainer').toggleClass('moveRight');

    evaluarRotacion();
});