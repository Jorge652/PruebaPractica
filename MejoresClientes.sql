SELECT 
    c.nombreCliente,
    SUM(fd.subtotal) AS TotalGastado,
    p.nombreProducto,
    SUM(fd.cantidad) AS TotalVendido
FROM factura_detalle fd
INNER JOIN cliente c ON fd.idCliente = c.idCliente
INNER JOIN producto p ON fd.idProducto = p.idProducto
INNER JOIN factura_cabecera fc ON fd.idFacturaDetalle = fc.idFactura
GROUP BY c.nombreCliente, p.nombreProducto
ORDER BY TotalGastado DESC, TotalVendido DESC;
