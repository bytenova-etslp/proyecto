// La validacion definitiva y el guardado del envio se realizan en PHP.
const formularioCheckout = document.getElementById("form-checkout");
const mensajeCheckout = document.getElementById("mensaje-checkout");
let enviandoCheckout = false;

// Al salir del campo se presenta como 099 999 999, sin cambiar los digitos.
const telefonoCheckout = document.getElementById("envio-telefono");
telefonoCheckout.addEventListener("blur", () => {
    const digitos = telefonoCheckout.value.replace(/\s/g, "");
    if (/^[0-9]{9}$/.test(digitos)) {
        telefonoCheckout.value = digitos.slice(0, 3) + " " + digitos.slice(3, 6) + " " + digitos.slice(6);
    }
});

formularioCheckout.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    if (enviandoCheckout) return;
    const boton = formularioCheckout.querySelector('button[type="submit"]');
    enviandoCheckout = true;
    boton.disabled = true;
    mensajeCheckout.textContent = "Guardando pedido...";
    let navegar = false;
    try {
        const respuesta = await fetch(formularioCheckout.action, {
            method: "POST", body: new FormData(formularioCheckout)
        });
        if (respuesta.redirected || respuesta.status === 401) {
            navegar = true;
            window.location.href = respuesta.redirected ? respuesta.url : "index.php?pagina=login";
            return;
        }
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.correcto) {
            mensajeCheckout.textContent = resultado.mensaje || "No se pudo guardar el pedido. Revisá los datos.";
            return;
        }
        if (!Number.isInteger(resultado.id_pedido) || resultado.id_pedido < 1) {
            throw new Error("Respuesta no valida");
        }
        guardarCarrito([]);
        navegar = true;
        window.location.href = "index.php?pagina=pago&id=" + resultado.id_pedido;
    } catch (error) {
        mensajeCheckout.textContent = "No se pudo confirmar la respuesta del servidor. El carrito se conserva. Revisá Mis pedidos antes de volver a intentar.";
    } finally {
        if (!navegar) {
            enviandoCheckout = false;
            boton.disabled = false;
        }
    }
});
