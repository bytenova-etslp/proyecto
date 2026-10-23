/*
    Validacion del formulario de registro.
    Envia los datos a PHP para crear una cuenta de cliente.
*/

const formulario = document.querySelector("#form-registro");
const mensaje = document.querySelector("#mensaje-registro");

formulario.addEventListener("submit", async (event) => {
    event.preventDefault();

    const nombre = document.querySelector("#registro-nombre").value.trim();
    const email = document.querySelector("#registro-email").value.trim();
    const password = document.querySelector("#registro-password").value;
    const confirmar = document.querySelector("#registro-confirmar").value;

    if (nombre === "" || email === "" || password === "" || confirmar === "") {
        mensaje.textContent = "Debe completar todos los campos.";
        mensaje.style.color = "red";
        return;
    }

    if (password.length < 6) {
        mensaje.textContent = "La contraseña debe tener al menos 6 caracteres.";
        mensaje.style.color = "red";
        return;
    }

    if (password !== confirmar) {
        mensaje.textContent = "Las contraseñas no coinciden.";
        mensaje.style.color = "red";
        return;
    }

    // Evita envios repetidos mientras PHP procesa el registro.
    const boton = formulario.querySelector('button[type="submit"]');
    boton.disabled = true;
    mensaje.textContent = "Creando cuenta...";
    mensaje.style.color = "black";

    try {
        const respuesta = await fetch(formulario.action, {
            method: "POST",
            body: new FormData(formulario)
        });
        const resultado = await respuesta.json();

        mensaje.textContent = resultado.mensaje;
        mensaje.style.color = resultado.correcto ? "green" : "red";
        if (resultado.correcto) formulario.reset();
    } catch (error) {
        mensaje.textContent = "No se pudo completar el registro. Intenta nuevamente.";
        mensaje.style.color = "red";
    } finally {
        boton.disabled = false;
    }
});
