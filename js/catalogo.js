/*
	Archivo: catalogo.js
	Proyecto: PuntoModa - ByteNova
	Ultima actualizacion: 04/09/2026

	Adaptado del ejemplo de carrito entregado en clase.
	Contiene los datos del catalogo y la logica para mostrar productos
	y agregarlos al carrito.
*/

// Por ahora el catalogo contiene el producto que ya estaba en el sitio.
const productos = [
	{
		id: 1,
		nombre: "Canguro Oversize",
		precio: 1890,
		categoria: "Abrigos",
		imagen: "imagenes/canguro.jpg"
	}
];

// Dibuja los productos dentro de #catalogo-container usando DOM dinamico.
function mostrarProductos() {
	const contenedor = document.getElementById("catalogo-container");

	// Esta funcion tambien se carga en otras paginas, por eso verificamos el contenedor.
	if (!contenedor) return;

	contenedor.innerHTML = "";

	productos.forEach(producto => {
		// 1) Crear el elemento de la tarjeta.
		const card = document.createElement("div");
		card.classList.add("tarjeta-producto");

		// 2) Agregar el contenido de la tarjeta.
		card.innerHTML = `
			<a href="detalle-producto.html" class="enlace-imagen-producto">
				<div class="imagen-producto">
					<img src="${producto.imagen}" alt="${producto.nombre}">
				</div>
			</a>
			<div class="info-producto">
				<div class="categoria-producto">${producto.categoria}</div>
				<h3 class="titulo-producto">${producto.nombre}</h3>
				<div class="precio-producto">$ ${producto.precio.toLocaleString("es-UY")}</div>
				<div class="botones-producto">
					<a href="detalle-producto.html" class="btn btn-secundario">Ver detalle</a>
					<button type="button" class="btn btn-agregar-producto" data-id="${producto.id}">Agregar al Carrito</button>
				</div>
			</div>
		`;

		// 3) Agregar la tarjeta a la pagina.
		contenedor.appendChild(card);
	});
}

// Agrega un producto o suma uno a la cantidad si ya estaba guardado.
function agregarAlCarrito(producto) {
	const carrito = cargarCarrito();
	const existente = carrito.find(item => item.id === producto.id);

	if (existente) {
		existente.cantidad++;
	} else {
		carrito.push({
			id: producto.id,
			nombre: producto.nombre,
			precio: producto.precio,
			cantidad: 1
		});
	}

	guardarCarrito(carrito);
	actualizarContadorCarrito();
	mostrarMensaje(`${producto.nombre} agregado al carrito`);
}

// Conecta los botones de agregar con el producto correspondiente.
function prepararBotonesAgregar() {
	const botones = document.querySelectorAll(".btn-agregar-producto");

	botones.forEach(boton => {
		boton.addEventListener("click", () => {
			const id = Number(boton.dataset.id);
			const producto = productos.find(item => item.id === id);

			if (producto) {
				agregarAlCarrito(producto);
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

// Todo empieza cuando el HTML termino de cargar.
document.addEventListener("DOMContentLoaded", () => {
	mostrarProductos();
	prepararBotonesAgregar();
	actualizarContadorCarrito();
});
