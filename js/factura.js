// La descarga directa usa PHP; esta accion abre la impresion del navegador.
document.getElementById("imprimir-factura")?.addEventListener("click", () => {
    window.print();
});
