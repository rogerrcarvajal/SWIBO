truncate table usuarios restart identity
truncate table productos restart identity
truncate table categorias_producto restart identity
truncate table movimientos_inventario restart identity

truncate table usuarios cascade
truncate table productos cascade
truncate table categorias_producto cascade
truncate table movimientos_inventario cascade


select * from usuarios
select * from productos
select * from categorias_producto
select * from movimientos_inventario

-- La contraseña para este usuario es 'admin'
INSERT INTO usuarios (nombre, email, username, password, rol) VALUES 
('Administrador del Sistema', 'admin@bixoil.com', 'admin', '$2y$10$wT33m.5z3t9kX.y5s4P95.N/EOC2s1sjuoXJ.fG6CgEaJjH6.C.Gu', 'Admin');