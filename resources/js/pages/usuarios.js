const formulario = document.getElementById('usuario-form');

if (formulario) {
    const rol = document.getElementById('rol_id');
    const area = document.getElementById('area_id');
    const ventanilla = document.getElementById('ventanilla_id');

    const esEmpleado = () => (rol.selectedOptions[0]?.textContent ?? '').toLowerCase().includes('empleado');

    const actualizar = () => {
        const empleado = esEmpleado();
        const areaId = area.value;

        area.disabled = !empleado;
        ventanilla.disabled = !empleado;
        area.required = empleado;
        ventanilla.required = empleado;

        [...ventanilla.options].forEach((opcion) => {
            if (!opcion.value) {
                return;
            }

            const coincide = !areaId || opcion.dataset.area === areaId;
            opcion.hidden = !coincide;
            opcion.disabled = !coincide;
        });

        if (ventanilla.selectedOptions[0]?.disabled) {
            ventanilla.value = '';
        }
    };

    rol.addEventListener('change', actualizar);
    area.addEventListener('change', actualizar);
    actualizar();
}
