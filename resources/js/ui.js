document.addEventListener('click', (event) => {
    const boton = event.target.closest('[data-confirm]');

    if (!boton) {
        return;
    }

    if (!window.confirm(boton.dataset.confirm)) {
        event.preventDefault();
        event.stopPropagation();
    }
});

document.querySelectorAll('.alert-dismissible').forEach((alerta) => {
    window.setTimeout(() => {
        const instancia = window.bootstrap?.Alert?.getInstance(alerta);
        instancia?.close();
    }, 7000);
});
