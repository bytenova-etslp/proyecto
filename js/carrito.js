/*
	Archivo: carrito.js
	Proyecto: Punto Moda - ByteNova
	Ultima actualizacion: 04/09/2026

	Adaptado del ejemplo de carrito entregado en clase.
	Se encarga de mostrar, modificar, eliminar y vaciar productos del carrito.
*/

// Dibuja el contenido actual del carrito dentro de #carrito-container.
function mostrarCarrito() {
	const carrito = cargarCarrito();
	const contenedor = document.getElementById("carrito-container");
	const totalEl = document.getElementById("total-carrito");
	const vacioEl = document.getElementById("carrito-vacio");
	const resumenEl = document.querySelector(".carrito-resumen");

	contenedor.innerHTML = "";

	// Si no hay productos, mostramos el mensaje y ocultamos el resumen.
	if (carrito.length === 0) {
		vacioEl.style.display = "block";
		resumenEl.style.display = "none";
		actualizarContadorCarrito();
		return;
	}

	vacioEl.style.display = "none";
	resumenEl.style.display = "flex";

	let total = 0;

	carrito.forEach(item => {
		const subtotal = item.precio * item.cantidad;
		total += subtotal;

		// 1) Crear el elemento del producto.
		const div = document.createElement("div");
		div.classList.add("item-carrito");

		// 2) Agregar su informacion, talle y botones.
		div.innerHTML = `
			<div class="item-info">
				<span class="item-nombre">${item.nombre}</span>
				<span class="item-talle">Talle: ${item.talle || "Sin especificar"}</span>
				<span class="item-precio-unitario">$${item.precio.toLocaleString("es-UY")} c/u</span>
			</div>

			<div class="item-cantidad">
				<button class="btn-cantidad btn-restar" aria-label="Restar uno">−</button>
				<span class="item-cantidad-numero">${item.cantidad}</span>
				<button class="btn-cantidad btn-sumar" aria-label="Sumar uno">+</button>
			</div>

			<span class="item-subtotal">$${subtotal.toLocaleString("es-UY")}</span>
			<button class="btn-eliminar" aria-label="Eliminar producto">✕</button>
		`;

		// Cada boton modifica solamente este producto y talle.
		div.querySelector(".btn-sumar").addEventListener("click", () => cambiarCantidad(item.id, item.talle, 1));
		div.querySelector(".btn-restar").addEventListener("click", () => cambiarCantidad(item.id, item.talle, -1));
		div.querySelector(".btn-eliminar").addEventListener("click", () => eliminarDelCarrito(item.id, item.talle));

		// 3) Agregar el producto a la pagina.
		contenedor.appendChild(div);
	});

	totalEl.textContent = `Total: $${total.toLocaleString("es-UY")}`;
	actualizarContadorCarrito();
}

// Suma o resta una unidad a la cantidad de un producto y talle.
function cambiarCantidad(id, talle, cambio) {
	let carrito = cargarCarrito();
	const item = carrito.find(producto => producto.id === id && producto.talle === talle);

	if (!item) return;

	item.cantidad += cambio;

	// Si la cantidad llega a cero, el producto se elimina.
	if (item.cantidad <= 0) {
		carrito = carrito.filter(producto => !(producto.id === id && producto.talle === talle));
	}

	guardarCarrito(carrito);
	mostrarCarrito();
}

// Elimina por completo un producto de un talle determinado.
function eliminarDelCarrito(id, talle) {
	const carrito = cargarCarrito().filter(producto => !(producto.id === id && producto.talle === talle));
	guardarCarrito(carrito);
	mostrarCarrito();
}

// Vacia todos los productos guardados.
function vaciarCarrito() {
	guardarCarrito([]);
	mostrarCarrito();
}

// Por ahora la compra es simulada y el pedido no se guarda en la base de datos.
function finalizarCompra() {
	const carrito = cargarCarrito();

	if (carrito.length === 0) return;

	alert("Compra simulada. El pedido todavía no se guarda en la base de datos.");
	vaciarCarrito();
}

// Conecta los botones una vez que termino de cargar el HTML.
document.addEventListener("DOMContentLoaded", () => {
	mostrarCarrito();
	document.getElementById("btn-vaciar").addEventListener("click", vaciarCarrito);
	document.getElementById("btn-finalizar").addEventListener("click", finalizarCompra);
});
