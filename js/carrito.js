/*
	Archivo: carrito.js
	Proyecto: Punto Moda - ByteNova
	Ultima actualizacion: 04/09/2026

	Adaptado del ejemplo de carrito entregado en clase.
	Se encarga de mostrar, modificar, eliminar y vaciar productos del carrito.
*/

let enviandoPedido = false;

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
				<span class="item-nombre"></span>
				<span class="item-talle"></span>
				<span class="item-precio-unitario"></span>
			</div>

			<div class="item-cantidad">
				<button class="btn-cantidad btn-restar" aria-label="Restar uno">−</button>
				<span class="item-cantidad-numero"></span>
				<button class="btn-cantidad btn-sumar" aria-label="Sumar uno">+</button>
			</div>

			<span class="item-subtotal"></span>
			<button class="btn-eliminar" aria-label="Eliminar producto">✕</button>
		`;

        // Los datos del carrito se muestran como texto, sin interpretarlos como HTML.
        div.querySelector(".item-nombre").textContent = item.nombre;
        div.querySelector(".item-talle").textContent = `Talle: ${item.talle || "Sin especificar"}`;
        div.querySelector(".item-precio-unitario").textContent = `$${Number(item.precio).toLocaleString("es-UY")} c/u`;
        div.querySelector(".item-cantidad-numero").textContent = item.cantidad;
        div.querySelector(".item-subtotal").textContent = `$${subtotal.toLocaleString("es-UY")}`;

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
    if (enviandoPedido) return;
	let carrito = cargarCarrito();
	const item = carrito.find(producto => producto.id === id && producto.talle === talle);

	if (!item) return;

	// Permite reducir o eliminar articulos aunque sean de un carrito anterior.
	if (cambio > 0) {
		if (!Number.isInteger(item.stock) || item.stock < 0) {
			alert("Vuelve al catalogo y agrega este talle para actualizar su disponibilidad.");
			return;
		}
		if (item.cantidad + cambio > item.stock) {
			alert(`Solo hay ${item.stock} unidades disponibles de este talle.`);
			return;
		}
	}

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
    if (enviandoPedido) return;
	const carrito = cargarCarrito().filter(producto => !(producto.id === id && producto.talle === talle));
	guardarCarrito(carrito);
	mostrarCarrito();
}

// Vacia todos los productos guardados.
function vaciarCarrito() {
    if (enviandoPedido) return;
	guardarCarrito([]);
	mostrarCarrito();
}

// El checkout recibe solo producto, talle y cantidad. Todavia no crea un pedido.
function finalizarCompra() {
	if (enviandoPedido) return;
	const carrito = cargarCarrito();
	if (carrito.length === 0) return;

    enviandoPedido = true;
    const formulario = document.createElement("form");
    formulario.method = "POST";
    formulario.action = "index.php?pagina=checkout";
    const campo = document.createElement("input");
    campo.type = "hidden";
    campo.name = "carrito";
    campo.value = JSON.stringify(carrito.map(item => ({
        id: item.id, talle: item.talle, cantidad: item.cantidad
    })));
    formulario.appendChild(campo);
    document.body.appendChild(formulario);
    formulario.submit();
}

// Permite volver a continuar si el navegador recupera el carrito desde su cache.
window.addEventListener("pageshow", () => { enviandoPedido = false; });

// Conecta los botones una vez que termino de cargar el HTML.
document.addEventListener("DOMContentLoaded", () => {
	mostrarCarrito();
	document.getElementById("btn-vaciar").addEventListener("click", vaciarCarrito);
	document.getElementById("btn-finalizar").addEventListener("click", finalizarCompra);
});
