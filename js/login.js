/*
    ByteNova - Validacion del login antes de enviarlo al servidor.
    Ultima actualizacion: 29/10/2026.
    PHP vuelve a validar los datos y comprueba las credenciales.
*/

const formularioLogin = document.querySelector("#form-login");
const correoLogin = document.querySelector("#login-email");
const contrasenaLogin = document.querySelector("#login-password");
const mensajeLogin = document.querySelector("#mensaje-login");

if (formularioLogin && correoLogin && contrasenaLogin && mensajeLogin) {
    formularioLogin.addEventListener("submit", (evento) => {
        mensajeLogin.replaceChildren();
        correoLogin.value = correoLogin.value.trim();

        let error = "";
        let campoConError = null;

        if (correoLogin.value === "") {
            error = "Ingresá tu correo electrónico.";
            campoConError = correoLogin;
        } else if (correoLogin.value.length > 100 || !correoLogin.validity.valid) {
            error = "Ingresá un correo válido de hasta 100 caracteres.";
            campoConError = correoLogin;
        } else if (contrasenaLogin.value === "") {
            error = "Ingresá tu contraseña.";
            campoConError = contrasenaLogin;
        }

        if (error !== "") {
            evento.preventDefault();
            const elemento = document.createElement("li");
            elemento.textContent = error;
            mensajeLogin.appendChild(elemento);
            campoConError.focus();
        }
        // Sin errores, el formulario continua con su POST normal a PHP.
    });

    // Con JavaScript se usan nuestros mensajes. Sin el script, valida HTML.
    formularioLogin.noValidate = true;
}
