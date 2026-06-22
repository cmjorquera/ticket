-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-06-2026 a las 15:14:21
-- Versión del servidor: 10.6.19-MariaDB
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `acceso_sistema_panel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `id_usuario_registra` int(11) DEFAULT NULL,
  `nombre_equipo` varchar(255) DEFAULT NULL,
  `fabricante` varchar(255) DEFAULT NULL,
  `producto` varchar(255) DEFAULT NULL,
  `numero_serie` varchar(255) DEFAULT NULL,
  `tipo_pc` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `eliminado` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id_equipo`, `id_usuario`, `id_colegio`, `id_ubicacion`, `id_usuario_asignado`, `id_usuario_registra`, `nombre_equipo`, `fabricante`, `producto`, `numero_serie`, `tipo_pc`, `qr_code`, `id_estado`, `eliminado`) VALUES
(54, 1, 15, NULL, 0, NULL, 'AESPINOSA-PC.txt', 'MSI', 'MS-7A15', 'Default string', 'Desktop', '../../codigosQR/computadores/54Sin_asignar.png', 1, 0),
(55, 3, 15, NULL, NULL, NULL, 'AFUENTES', 'Dell Inc.', 'Inspiron 3501', 'F7JLPH3', 'Notebook', '../../codigosQR/computadores/55Sin_asignar.png', 1, 0),
(56, 2, 15, NULL, NULL, NULL, 'FCHAVEZ', 'HP', 'HP Pavilion Laptop 15-cs1xxx', '5CD9215BTT', 'Notebook', '../../codigosQR/computadores/56_2.png', 1, 0),
(57, 39, 15, NULL, NULL, NULL, 'FGOMEZ', 'Dell Inc.', 'Inspiron 15 3511', '231M7K3', 'Notebook', '../../codigosQR/computadores/57_2462.png', 1, 0),
(58, 12, 15, NULL, NULL, NULL, 'FVALENZUELA-NTB.txt', 'HP', 'HP Laptop 15-ef1xxx', '5CD119FKWG', 'Notebook', '../../codigosQR/computadores/58Sin_asignar.png', 1, 0),
(59, 9, 15, NULL, 0, NULL, 'GMUNOZ', 'HP', 'HP Laptop 14-cf2xxx', '5CG1113SW0', 'Notebook', '../../codigosQR/computadores/59Sin_asignar.png', 1, 0),
(60, 4, 15, NULL, NULL, NULL, 'RLEON', 'Dell Inc.', 'Inspiron 3501', 'J6NLPH3', 'Notebook', '../../codigosQR/computadores/60_4.png', 1, 0),
(61, 5, 15, NULL, NULL, NULL, 'VPEREZ', 'Dell Inc.', 'Inspiron 3501', '19VMPH3', 'Notebook', '../../codigosQR/computadores/61_5.png', 1, 0),
(62, 8, 15, NULL, NULL, NULL, 'CJORQUERA', 'Dell Inc.', 'Inspiron 3505', 'FQQ6J93', 'Notebook', '../../codigosQR/computadores/62_8.png', 4, 0),
(63, 36, 15, NULL, NULL, NULL, 'JFERNANDEZ', 'HP', 'Victus by HP Gaming Laptop 15-fa1', '5CD41039D2', 'Notebook', '../../codigosQR/computadores/63_36.png', 1, 0),
(64, 6, 15, 12, 6, NULL, 'RAMON.txt', 'HP', 'Victus by HP Laptop 16-d0xxx', '5CD251BFJD', 'Notebook', '../../codigosQR/computadores/64_6.png', 1, 0),
(65, 7, 15, NULL, NULL, NULL, 'MGUTIERREZ', 'HP', 'HP Pavilion Laptop 15-eh0xxx', '5CD21114W0', 'Notebook', '../../codigosQR/computadores/65Sin_asignar.png', 1, 0),
(66, 16, 15, NULL, NULL, NULL, 'GFRIEDL', 'HP', 'HP Laptop 15-da1xxx', 'CND02658LV', 'Notebook', '../../codigosQR/computadores/66_16.png', 1, 0),
(67, 26, 15, 14, 26, NULL, 'JMACKENNA', 'HP', 'HP Laptop 15-dy5xxx', '5CD248DRVZ', 'Notebook', '../../codigosQR/computadores/67_26.png', 1, 0),
(68, 40, 15, NULL, NULL, NULL, 'JPRIETO.txt', 'HP', 'HP Laptop 15-dy5xxx', '5CD338F0B5', 'Notebook', '../../codigosQR/computadores/68_2463.png', 1, 0),
(69, 17, 15, 13, 17, NULL, 'CNIETO', 'LENOVO', '82FG', 'PF3DBNE4', 'Notebook', '../../codigosQR/computadores/69_17.png', 1, 0),
(70, 25, 15, NULL, NULL, NULL, 'NOTEBOOK.txt', 'LENOVO', '82XF', 'MP2MD192', 'Notebook', '../../codigosQR/computadores/70_25.png', 1, 0),
(71, 23, 15, NULL, NULL, NULL, 'COSTORNOL', 'LENOVO', '82H7', 'PF3QKAJ6', 'Notebook', '../../codigosQR/computadores/71_23.png', 1, 0),
(72, 21, 15, NULL, NULL, NULL, 'CBARROS', 'LENOVO', '82A3', 'LT10AN81', 'Notebook', '../../codigosQR/computadores/72_21.png', 1, 0),
(73, 20, 15, NULL, NULL, NULL, 'MIBANEZ', 'HP', 'HP Pavilion Laptop 15-cs1xxx', '5CD9215BM1', 'Notebook', '../../codigosQR/computadores/73_20.png', 1, 0),
(74, 41, 11, NULL, NULL, NULL, 'TERRAZURIZ', 'LENOVO', '82H7', 'PF3CY56W', 'Notebook', '../../codigosQR/computadores/74Sin_asignar.png', 1, 0),
(75, 2492, 11, NULL, 2492, NULL, 'LAPTOP-92BTC0P8', 'Asus', 'Vivobook Go E1504FA', 'T9N0CV15T954396', 'Notebook', 'PC-T9N0CV15T954396', 1, 0),
(76, 2492, 11, NULL, 0, NULL, 'CARLOS-GUAJARDO', 'Asus', 'Vivobook Go E1504FA', 'T9N0CV15T93639E', 'Notebook', 'PC-T9N0CV15T93639E', 1, 0),
(77, 8, 15, 12, 8, NULL, 'PC-AULA-01', 'Dell', 'OptiPlex 3080', 'SN-12345ABC', 'Desktop', 'PC-SN-12345ABC', 1, 0),
(78, 2492, 11, NULL, 0, NULL, 'Afterschool', 'Dell', 'Inspiron 3442', '2RQ6B12', 'Notebook', 'PC-2RQ6B12', 1, 0),
(80, 8, 15, 12, 8, 8, 'CJorquera', 'Acer', 'Aspire AL15-41P', 'NXJ53AL004448001A09D00', 'Notebook', 'PC-NXJ53AL004448001A09D00', 1, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id_equipo`),
  ADD UNIQUE KEY `ux_equipos_numero_serie` (`numero_serie`),
  ADD KEY `idx_equipos_asignado` (`id_usuario_asignado`),
  ADD KEY `idx_equipos_colegio` (`id_colegio`),
  ADD KEY `idx_equipos_creado_por` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
