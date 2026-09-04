/*
    Validacion del formulario de inicio de sesion.
    Por ahora solo valida los datos del lado del cliente.
*/

const formulario = document.querySelector("#form-login");
const mensaje = document.querySelector("#mensaje-login");

formulario.addEventListener("submit", (event) => {
    event.preventDefault();

    const email = document.querySelector("#login-email").value.trim();
    const password = document.querySelector("#login-password").value.trim();

    if (email === "" || password === "") {
        mensaje.textContent = "Debe completar todos los campos.";
        mensaje.style.color = "red";
        return;
    }

    if (password.length < 6) {
        mensaje.textContent = "La contraseña debe tener al menos 6 caracteres.";
        mensaje.style.color = "red";
        return;
    }

    mensaje.textContent = "Login válido.";
    mensaje.style.color = "green";
});
