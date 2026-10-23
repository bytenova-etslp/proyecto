/*
	Archivo: catalogo.js
	Proyecto: Punto Moda - ByteNova

	Adaptado del ejemplo de carrito entregado en clase.
	Los productos ahora los genera PHP desde la base de datos.
	Este archivo mantiene solamente la logica para agregarlos al carrito.
*/

// Agrega un producto o suma uno a la cantidad si ya estaba guardado con el mismo talle.
function agregarAlCarrito(id, nombre, precio, talle, stock) {
	// La disponibilidad corresponde al talle elegido al cargar la pagina.
	if (!Number.isInteger(stock) || stock <= 0) {
		mostrarMensaje("Este talle no tiene unidades disponibles.");
		return;
	}
	const carrito = cargarCarrito();
	const existente = carrito.find(item => item.id === id && item.talle === talle);

	if (existente) {
		// Actualiza tambien los articulos guardados antes de este cambio.
		existente.stock = stock;
		if (existente.cantidad >= stock) {
			guardarCarrito(carrito);
			mostrarMensaje(`Solo hay ${stock} unidades disponibles de este talle.`);
			return;
		}
		existente.cantidad++;
	} else {
		carrito.push({
			id: id,
			nombre: nombre,
			precio: precio,
			talle: talle,
			cantidad: 1,
			stock: stock
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

			if (!selectorTalle) return;
			const opcion = selectorTalle.selectedOptions[0];
			if (!opcion || opcion.disabled) {
				mostrarMensaje("Selecciona un talle disponible.");
				return;
			}

			const stock = Number(opcion.dataset.stock);
			agregarAlCarrito(id, nombre, precio, selectorTalle.value, stock);
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
