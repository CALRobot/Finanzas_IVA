-- Adminer 4.7.8 MySQL dump

-- base.  'finanzas_app_iva_v2'

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `categoria_gasto`;
CREATE TABLE `categoria_gasto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categoria_gasto` (`id`, `nombre`) VALUES
(1,	'Alquiler'),
(2,	'Comida'),
(6,	'Compras'),
(8,	'Educación'),
(9,	'Gastos Vs'),
(4,	'Ocio'),
(7,	'Salud'),
(10,	'Seguro'),
(5,	'Servicios'),
(3,	'Transporte');

DROP TABLE IF EXISTS `categoria_ingreso`;
CREATE TABLE `categoria_ingreso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categoria_ingreso` (`id`, `nombre`) VALUES
(5,	'Facturas'),
(2,	'Freelance'),
(7,	'Ingresos Vs'),
(4,	'Inversiones'),
(6,	'Reembolso'),
(3,	'Regalo'),
(1,	'Salario');

DROP TABLE IF EXISTS `gastos`;
CREATE TABLE `gastos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `fecha` date NOT NULL,
  `concepto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_imponible` decimal(10,2) NOT NULL,
  `total_bruto` decimal(10,2) NOT NULL,
  `importe_iva` decimal(10,2) NOT NULL DEFAULT '0.00',
  `id_tipo_iva` int DEFAULT NULL,
  `categoria_id` int DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foto_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `fk_gastos_tipo_iva` (`id_tipo_iva`),
  CONSTRAINT `fk_gastos_tipo_iva` FOREIGN KEY (`id_tipo_iva`) REFERENCES `tipos_iva` (`id`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria_gasto` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gastos` (`id`, `usuario_id`, `fecha`, `concepto`, `base_imponible`, `total_bruto`, `importe_iva`, `id_tipo_iva`, `categoria_id`, `descripcion`, `foto_link`) VALUES
(1,	1,	'2025-06-20',	'Pago alquiler nave Silla',	650.00,	650.00,	0.00,	4,	1,	'',	NULL),
(4,	1,	'2025-06-20',	'comida',	40.50,	45.00,	4.50,	2,	2,	'',	''),
(5,	1,	'2025-06-20',	'comida',	40.50,	44.55,	4.05,	2,	2,	'',	'2025-06-20_gasto_comida_685b959556244.jpg'),
(6,	1,	'2025-06-20',	'comida',	40.50,	44.55,	4.05,	2,	2,	'',	'2025-06-20_gasto_comida_685b95b684385.jpg'),
(7,	1,	'2025-06-20',	'comida',	45.00,	45.00,	0.00,	4,	2,	'',	''),
(8,	1,	'2025-06-21',	'Paella para dos',	69.12,	76.03,	6.91,	2,	2,	'',	NULL),
(9,	1,	'2025-06-21',	'Paella para dos',	69.12,	76.03,	6.91,	2,	2,	'',	'2025-06-21_gasto_Paella_para_dos_685b956f57bd8.jpg'),
(10,	1,	'2025-06-25',	'Farmacia',	45.00,	54.45,	9.45,	1,	7,	'',	'2025-06-25_gasto_Farmacia_685b930967029.jpg');

DROP TABLE IF EXISTS `ingresos`;
CREATE TABLE `ingresos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `fecha` date NOT NULL,
  `concepto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_imponible` decimal(10,2) NOT NULL,
  `total_bruto` decimal(10,2) NOT NULL,
  `importe_iva` decimal(10,2) NOT NULL DEFAULT '0.00',
  `id_tipo_iva` int DEFAULT NULL,
  `categoria_id` int DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foto_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `fk_ingresos_tipo_iva` (`id_tipo_iva`),
  CONSTRAINT `fk_ingresos_tipo_iva` FOREIGN KEY (`id_tipo_iva`) REFERENCES `tipos_iva` (`id`),
  CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ingresos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria_ingreso` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ingresos` (`id`, `usuario_id`, `fecha`, `concepto`, `base_imponible`, `total_bruto`, `importe_iva`, `id_tipo_iva`, `categoria_id`, `descripcion`, `foto_link`) VALUES
(1,	1,	'2025-06-20',	'Cobro reparacion sisterna',	99.17,	120.00,	20.83,	1,	7,	'vecino del 2do piso - peres galdos 7',	'2025-06-20_ingreso_Cobro_reparacion_sisterna_685b962a59d64.jpg'),
(3,	1,	'2025-06-20',	'Cobro Factura Nº 2 del 2025',	1400.00,	1400.00,	0.00,	4,	5,	'',	'2025-06-20_ingreso_Cobro_Factura_N_2_del_2025_685b965dd2609.jpg'),
(4,	1,	'2025-06-21',	'Develop ofline',	148.76,	180.00,	31.24,	1,	2,	'',	'2025-06-21_ingreso_Develop_ofline_685b95e61e14a.jpg'),
(5,	1,	'2025-06-21',	'Develop ofline',	148.76,	180.00,	31.24,	1,	2,	'',	'2025-06-21_ingreso_Develop_ofline_685b95ff9395a.jpg'),
(6,	1,	'2025-06-25',	'Devolucion monitor',	120.00,	145.20,	25.20,	1,	6,	'',	'2025-06-25_ingreso_Devolucion_monitor_685b92db5ac59.jpg'),
(7,	1,	'2025-06-25',	'Devolucion monitor',	120.00,	145.20,	25.20,	1,	6,	'',	NULL),
(8,	1,	'2025-06-25',	'Reparación',	30.00,	36.30,	6.30,	1,	7,	'',	NULL),
(9,	1,	'2025-06-25',	'Reparación',	30.00,	36.30,	6.30,	1,	7,	'',	NULL);

DROP TABLE IF EXISTS `tipos_iva`;
CREATE TABLE `tipos_iva` (
  `id` int NOT NULL AUTO_INCREMENT,
  `porcentaje` decimal(4,2) NOT NULL,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `porcentaje` (`porcentaje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipos_iva` (`id`, `porcentaje`, `descripcion`, `created_at`) VALUES
(1,	21.00,	'IVA General',	'2025-06-23 07:24:11'),
(2,	10.00,	'IVA Reducido',	'2025-06-23 07:24:11'),
(3,	4.00,	'IVA Superreducido',	'2025-06-23 07:24:11'),
(4,	0.00,	'IVA Exento/No Sujeto',	'2025-06-23 07:24:11');

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'assets/img/user-profile.png',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `password`, `email`, `foto_link`, `fecha_registro`) VALUES
(1,	'admin',	'$2y$10$/4VeD/hFFZ6vcBiIQy9wnu3cc00xOCviHctiN8OHpBfGN94oHHScu',	'calingrobot@gmail.com',	'assets/img/user-profile.png',	'2025-06-20 09:01:58');

-- 2025-06-25 07:09:34
