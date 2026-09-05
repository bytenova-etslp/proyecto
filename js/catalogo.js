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
		imagen: "imagenes/canguro.jpg",
		talles: ["S", "M", "L", "XL"]
	}
];

// Dibuja los productos dentro de #catalogo-container usando DOM dinamico.
function mostrarProductos() {
	const contenedor = document.getElementById("catalogo-container");

	// Esta funcion tambien se carga en otras paginas, por eso verificamos el contenedor.
	if (!contenedor) return;

	contenedor.innerHTML = "";

	productos.forEach(producto => {
		// Preparamos las opciones de talle para el selector.
		let opcionesTalle = "";
		producto.talles.forEach(talle => {
			opcionesTalle += `<option value="${talle}">${talle}</option>`;
		});

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
				<div class="seleccion-talle">
					<label for="talle-${producto.id}">Talle</label>
					<select id="talle-${producto.id}" class="selector-talle" data-producto-id="${producto.id}">
						${opcionesTalle}
					</select>
				</div>
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

// Agrega un producto o suma uno a la cantidad si ya estaba guardado con el mismo talle.
function agregarAlCarrito(producto, talle) {
	const carrito = cargarCarrito();
	const existente = carrito.find(item => item.id === producto.id && item.talle === talle);

	if (existente) {
		existente.cantidad++;
	} else {
		carrito.push({
			id: producto.id,
			nombre: producto.nombre,
			precio: producto.precio,
			talle: talle,
			cantidad: 1
		});
	}

	guardarCarrito(carrito);
	actualizarContadorCarrito();
	mostrarMensaje(`${producto.nombre} talle ${talle} agregado al carrito`);
}

// Conecta los botones de agregar con el producto y el talle seleccionados.
function prepararBotonesAgregar() {
	const botones = document.querySelectorAll(".btn-agregar-producto");

	botones.forEach(boton => {
		boton.addEventListener("click", () => {
			const id = Number(boton.dataset.id);
			const producto = productos.find(item => item.id === id);
			const selectorTalle = document.querySelector(`.selector-talle[data-producto-id="${id}"]`);

			if (producto && selectorTalle) {
				agregarAlCarrito(producto, selectorTalle.value);
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
