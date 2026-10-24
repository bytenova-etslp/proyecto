// Las filas nuevas solo preparan el formulario; PHP valida y guarda los datos.
document.addEventListener("DOMContentLoaded", () => {
    const filas = document.getElementById("filas-talles");
    const agregar = document.getElementById("btn-agregar-talle");

    function conectarQuitar(boton) {
        boton.addEventListener("click", () => boton.closest("tr").remove());
    }
    filas.querySelectorAll(".btn-quitar-talle").forEach(conectarQuitar);

    agregar.addEventListener("click", () => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td><input type="text" name="talles[]" maxlength="10" aria-label="Talle"></td>
            <td><input type="number" name="stocks[]" min="0" max="2147483647" step="1" value="0" aria-label="Unidades del talle"></td>
            <td><button type="button" class="btn btn-secundario btn-quitar-talle">Quitar fila</button></td>
        `;
        conectarQuitar(fila.querySelector(".btn-quitar-talle"));
        filas.appendChild(fila);
        fila.querySelector('input[name="talles[]"]').focus();
    });
});
