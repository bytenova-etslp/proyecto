/*
	Archivo: catalogo.js
	Proyecto: Punto Moda - ByteNova

	Adaptado del ejemplo de carrito entregado en clase.
	Los productos ahora los genera PHP desde la base de datos.
	Este archivo mantiene solamente la logica para agregarlos al carrito.
*/

// Agrega un producto o suma uno a la cantidad si ya estaba guardado con el mismo talle.
function agregarAlCarrito(id, nombre, precio, talle) {
	const carrito = cargarCarrito();
	const existente = carrito.find(item => item.id === id && item.talle === talle);

	if (existente) {
		existente.cantidad++;
	} else {
		carrito.push({
			id: id,
			nombre: nombre,
			precio: precio,
			talle: talle,
			cantidad: 1
		});
	}

	guardarCarrito(carrito);
	actualizarContadorCarrito();
	mostrarMensaje(`${nombre} talle ${talle} agregado al carrito`);
}

// Lee desde el boton los datos que PHP dejo en los atributos data-*.
function prepararBotonesAgregar() {
	const botones = document.querySelectorAll(".btn-agregar-producto");

	botones.forEach(boton => {
		boton.addEventListener("click", () => {
			const id = Number(boton.dataset.id);
			const nombre = boton.dataset.nombre;
			const precio = Number(boton.dataset.precio);
			const selectorTalle = document.querySelector(`.selector-talle[data-producto-id="${id}"]`);

			if (selectorTalle) {
				agregarAlCarrito(id, nombre, precio, selectorTalle.value);
			}
		});
	});
}

// Muestra un aviso breve cuando se agrega un producto.
function mostrarMensaje(texto) {
	const mensaje = document.getElementById("mensaje-flotante");

	if (!mensaje) return;

	mensaje.textContent = texto;
	mensaje.classList.add("mensaje-flotante-visible");

	setTimeout(() => {
		mensaje.classList.remove("mensaje-flotante-visible");
	}, 1500);
}

// Conecta los botones cuando termina de cargar la pagina.
document.addEventListener("DOMContentLoaded", () => {
	prepararBotonesAgregar();
	actualizarContadorCarrito();
});
