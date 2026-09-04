/*
    Validacion del formulario de registro.
    El registro real se conectara con PHP mas adelante.
*/

const formulario = document.querySelector("#form-registro");
const mensaje = document.querySelector("#mensaje-registro");

formulario.addEventListener("submit", (event) => {
    event.preventDefault();

    const nombre = document.querySelector("#registro-nombre").value.trim();
    const email = document.querySelector("#registro-email").value.trim();
    const password = document.querySelector("#registro-password").value.trim();
    const confirmar = document.querySelector("#registro-confirmar").value.trim();

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

    mensaje.textContent = "Registro válido.";
    mensaje.style.color = "green";
});
