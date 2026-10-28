// El navegador permite imprimir la factura o guardarla como PDF.
document.getElementById("imprimir-factura")?.addEventListener("click", () => {
    window.print();
});
