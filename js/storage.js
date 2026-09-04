/*
	Archivo: storage.js
	Proyecto: PuntoModa - ByteNova
	Ultima actualizacion: 04/09/2026

	Este archivo se basa en el ejemplo de carrito entregado en clase.
	Su responsabilidad es leer y guardar el carrito en LocalStorage.
*/

const CLAVE_CARRITO = "carrito";

// Recupera el carrito guardado. Si no existe, devuelve un arreglo vacio.
function cargarCarrito() {
	const carritoGuardado = localStorage.getItem(CLAVE_CARRITO);

	if (carritoGuardado) {
		return JSON.parse(carritoGuardado);
	} else {
		return [];
	}
}

// Guarda el arreglo del carrito convertido a texto con JSON.stringify().
function guardarCarrito(carrito) {
	localStorage.setItem(CLAVE_CARRITO, JSON.stringify(carrito));
}

// Suma las cantidades de todos los productos guardados en el carrito.
function contarItemsCarrito(carrito) {
	return carrito.reduce((total, item) => total + item.cantidad, 0);
}

// Actualiza el contador que se muestra junto al enlace "Carrito".
function actualizarContadorCarrito() {
	const carrito = cargarCarrito();
	const contador = document.getElementById("contador-carrito");

	if (contador) {
		contador.textContent = contarItemsCarrito(carrito);
	}
}

// Al terminar de cargar cada pagina, se actualiza el contador del header.
document.addEventListener("DOMContentLoaded", actualizarContadorCarrito);
