SELECT 
    p.nombreProducto,
    YEAR(fc.fechaEmision) AS Año,
    MONTH(fc.fechaEmision) AS Mes,
    SUM(fd.cantidad) AS TotalVendido
FROM factura_detalle fd
INNER JOIN producto p ON fd.idProducto = p.idProducto
INNER JOIN factura_cabecera fc ON fd.idFacturaCabecera = fc.idFactura
GROUP BY p.nombreProducto, YEAR(fc.fechaEmision), MONTH(fc.fechaEmision)
ORDER BY Año, Mes, TotalVendido DESC;
