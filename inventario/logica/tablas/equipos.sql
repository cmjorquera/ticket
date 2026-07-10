-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:56:28
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
(78, 2492, 11, NULL, 0, NULL, 'Afterschool', 'Dell', 'Inspiron 3442', '2RQ6B12', 'Notebook', 'PC-2RQ6B12', 1, 1),
(80, 8, 15, 12, 8, 8, 'CJorquera', 'Acer', 'Aspire AL15-41P', 'NXJ53AL004448001A09D00', 'Notebook', 'PC-NXJ53AL004448001A09D00', 1, 0),
(81, 2496, 10, NULL, 32, 2496, 'PC-C61CD7YP24', 'Apple', 'Ipad Air A2902', 'C61CD7YP24', 'Tablet', 'PC-C61CD7YP24', 1, 0),
(82, 2496, 10, NULL, 32, 2496, 'PC-M76KN2YKM6', 'Apple', 'Ipad Air A2902', 'M76KN2YKM6', 'Tablet', 'PC-M76KN2YKM6', 1, 0),
(83, 2496, 10, NULL, 32, 2496, 'PC-MK4FPQ33XM', 'Apple', 'Ipad Air A2902', 'MK4FPQ33XM', 'Tablet', 'PC-MK4FPQ33XM', 1, 0),
(84, 2496, 10, NULL, 32, 2496, 'PC-G32TWRYGK2', 'Apple', 'Ipad Air A2902', 'G32TWRYGK2', 'Tablet', 'PC-G32TWRYGK2', 1, 0),
(85, 2496, 10, NULL, 32, 2496, 'PC-K6HW7F7J3Y', 'Apple', 'Ipad Air A2902', 'K6HW7F7J3Y', 'Tablet', 'PC-K6HW7F7J3Y', 1, 0),
(86, 2496, 10, NULL, 32, 2496, 'PC-LXQXF652HV', 'Apple', 'Ipad Air A2902', 'LXQXF652HV', 'Tablet', 'PC-LXQXF652HV', 1, 0),
(87, 2496, 10, NULL, 32, 2496, 'PC-HX92L95YGQ', 'Apple', 'Ipad Air A2902', 'HX92L95YGQ', 'Tablet', 'PC-HX92L95YGQ', 1, 0),
(88, 2496, 10, NULL, 32, 2496, 'PC-HQ66KXCY07', 'Apple', 'Ipad Air A2902', 'HQ66KXCY07', 'Tablet', 'PC-HQ66KXCY07', 1, 0),
(89, 2496, 10, NULL, 32, 2496, 'PC-GCJVJWE5HLF9', 'Apple', 'Ipad A1822', 'GCJVJWE5HLF9', 'Tablet', 'PC-GCJVJWE5HLF9', 1, 0),
(90, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVQHHLF9', 'Apple', 'Ipad A1822', 'GCJVJVQHHLF9', 'Tablet', 'PC-GCJVJVQHHLF9', 1, 0),
(91, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVBDHLF9', 'Apple', 'Ipad A1822', 'GCJVJVBDHLF9', 'Tablet', 'PC-GCJVJVBDHLF9', 1, 0),
(92, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVQQHLF9', 'Apple', 'Ipad A1822', 'GCJVJVQQHLF9', 'Tablet', 'PC-GCJVJVQQHLF9', 1, 0),
(93, 2496, 10, NULL, 32, 2496, 'PC-GCKVJA4CHLF9', 'Apple', 'Ipad A1822', 'GCKVJA4CHLF9', 'Tablet', 'PC-GCKVJA4CHLF9', 1, 0),
(94, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVD8HLF9', 'Apple', 'Ipad A1822', 'GCJVJVD8HLF9', 'Tablet', 'PC-GCJVJVD8HLF9', 1, 0),
(95, 2496, 10, NULL, 32, 2496, 'PC-GCKVJ9SXHLF9', 'Apple', 'Ipad A1822', 'GCKVJ9SXHLF9', 'Tablet', 'PC-GCKVJ9SXHLF9', 1, 0),
(96, 2496, 10, NULL, 32, 2496, 'PC-GCKVJAR9HLF9', 'Apple', 'Ipad A1822', 'GCKVJAR9HLF9', 'Tablet', 'PC-GCKVJAR9HLF9', 1, 0),
(97, 2496, 10, NULL, 32, 2496, 'PC-GCJVJXJ7HLF9', 'Apple', 'Ipad A1822', 'GCJVJXJ7HLF9', 'Tablet', 'PC-GCJVJXJ7HLF9', 1, 0),
(98, 2496, 10, NULL, 32, 2496, 'PC-GCJVJV76HLF9', 'Apple', 'Ipad A1822', 'GCJVJV76HLF9', 'Tablet', 'PC-GCJVJV76HLF9', 1, 0),
(99, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVPXHLF9', 'Apple', 'Ipad A1822', 'GCJVJVPXHLF9', 'Tablet', 'PC-GCJVJVPXHLF9', 1, 0),
(100, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVDLHLF9', 'Apple', 'Ipad A1822', 'GCJVJVDLHLF9', 'Tablet', 'PC-GCJVJVDLHLF9', 1, 0),
(101, 2496, 10, NULL, 32, 2496, 'PC-GCJVJV9VHLF9', 'Apple', 'Ipad A1822', 'GCJVJV9VHLF9', 'Tablet', 'PC-GCJVJV9VHLF9', 1, 0),
(102, 2496, 10, NULL, 32, 2496, 'PC-GCJVJW4XHLF9', 'Apple', 'Ipad A1822', 'GCJVJW4XHLF9', 'Tablet', 'PC-GCJVJW4XHLF9', 1, 0),
(103, 2496, 10, NULL, 32, 2496, 'PC-GCJVJWCQHLF9', 'Apple', 'Ipad A1822', 'GCJVJWCQHLF9', 'Tablet', 'PC-GCJVJWCQHLF9', 1, 0),
(104, 2496, 10, NULL, 32, 2496, 'PC-F9HVJFR6HLF9', 'Apple', 'Ipad A1822', 'F9HVJFR6HLF9', 'Tablet', 'PC-F9HVJFR6HLF9', 1, 0),
(105, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVKYHLF9', 'Apple', 'Ipad A1822', 'GCJVJVKYHLF9', 'Tablet', 'PC-GCJVJVKYHLF9', 1, 0),
(106, 2496, 10, NULL, 32, 2496, 'PC-F9HVJAH1HLF9', 'Apple', 'Ipad A1822', 'F9HVJAH1HLF9', 'Tablet', 'PC-F9HVJAH1HLF9', 1, 0),
(107, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVYWHLF9', 'Apple', 'Ipad A1822', 'GCJVJVYWHLF9', 'Tablet', 'PC-GCJVJVYWHLF9', 1, 0),
(108, 2496, 10, NULL, 32, 2496, 'PC-DMQS68LUG5VJ', 'Apple', 'Ipad Air A1566', 'DMQS68LUG5VJ', 'Tablet', 'PC-DMQS68LUG5VJ', 1, 0),
(109, 2496, 10, NULL, 32, 2496, 'PC-GCJVJW14HLF9', 'Apple', 'Ipad A1822', 'GCJVJW14HLF9', 'Tablet', 'PC-GCJVJW14HLF9', 1, 0),
(110, 2496, 10, NULL, 32, 2496, 'PC-GCJVJXP9HLF9', 'Apple', 'Ipad A1822', 'GCJVJXP9HLF9', 'Tablet', 'PC-GCJVJXP9HLF9', 1, 0),
(111, 2496, 10, NULL, 32, 2496, 'PC-GCKVJ6ZFHLF9', 'Apple', 'Ipad A1822', 'GCKVJ6ZFHLF9', 'Tablet', 'PC-GCKVJ6ZFHLF9', 1, 0),
(112, 2496, 10, NULL, 32, 2496, 'PC-GCKVJAHLHLF9', 'Apple', 'Ipad A1822', 'GCKVJAHLHLF9', 'Tablet', 'PC-GCKVJAHLHLF9', 1, 0),
(113, 2496, 10, NULL, 32, 2496, 'PC-GCJVJVA1HLF9', 'Apple', 'Ipad A1822', 'GCJVJVA1HLF9', 'Tablet', 'PC-GCJVJVA1HLF9', 1, 0),
(114, 2496, 10, NULL, 32, 2496, 'PC-GCJVJW7NHLF9', 'Apple', 'Ipad A1822', 'GCJVJW7NHLF9', 'Tablet', 'PC-GCJVJW7NHLF9', 1, 0),
(115, 2496, 10, NULL, 32, 2496, 'PC-GCKVJAL8HLF9', 'Apple', 'Ipad A1822', 'GCKVJAL8HLF9', 'Tablet', 'PC-GCKVJAL8HLF9', 1, 0),
(116, 2496, 10, NULL, 32, 2496, 'PC-GCKVJB8YHLF9', 'Apple', 'Ipad A1822', 'GCKVJB8YHLF9', 'Tablet', 'PC-GCKVJB8YHLF9', 1, 0),
(117, 2496, 10, NULL, 32, 2496, 'PC-GCKVJ70BHLF9', 'Apple', 'Ipad A1822', 'GCKVJ70BHLF9', 'Tablet', 'PC-GCKVJ70BHLF9', 1, 0),
(118, 2496, 10, NULL, 32, 2496, 'PC-F9HVJPRRHLF9', 'Apple', 'Ipad A1822', 'F9HVJPRRHLF9', 'Tablet', 'PC-F9HVJPRRHLF9', 1, 0),
(119, 2496, 10, NULL, 32, 2496, 'PC-F9HVJNCSHLF9', 'Apple', 'Ipad A1822', 'F9HVJNCSHLF9', 'Tablet', 'PC-F9HVJNCSHLF9', 1, 0),
(120, 2496, 10, NULL, 32, 2496, 'PC-GCKVJAJ3HLF9', 'Apple', 'Ipad A1822', 'GCKVJAJ3HLF9', 'Tablet', 'PC-GCKVJAJ3HLF9', 1, 0),
(121, 2496, 10, NULL, 32, 2496, 'PC-F9JVJ3G7HLF9', 'Apple', 'Ipad A1822', 'F9JVJ3G7HLF9', 'Tablet', 'PC-F9JVJ3G7HLF9', 1, 0),
(122, 2496, 10, NULL, 32, 2496, 'PC-GCKVJA92HLF9', 'Apple', 'Ipad A1822', 'GCKVJA92HLF9', 'Tablet', 'PC-GCKVJA92HLF9', 1, 0),
(123, 2496, 10, NULL, 32, 2496, 'PC-GCKVJ769HLF9', 'Apple', 'Ipad A1822', 'GCKVJ769HLF9', 'Tablet', 'PC-GCKVJ769HLF9', 1, 0),
(124, 2496, 10, NULL, 32, 2496, 'PC-DMPPQPX7FK14', 'Apple', 'Ipad Air A1474', 'DMPPQPX7FK14', 'Tablet', 'PC-DMPPQPX7FK14', 1, 0),
(125, 2496, 10, NULL, 32, 2496, 'PC-DMPPQQ9HFK14', 'Apple', 'Ipad Air A1474', 'DMPPQQ9HFK14', 'Tablet', 'PC-DMPPQQ9HFK14', 1, 0),
(126, 2496, 10, NULL, 32, 2496, 'PC-DMPR3KFRFK10', 'Apple', 'Ipad Air A1474', 'DMPR3KFRFK10', 'Tablet', 'PC-DMPR3KFRFK10', 1, 0),
(127, 2496, 10, NULL, 32, 2496, 'PC-DMPR3J1DFK10', 'Apple', 'Ipad Air A1474', 'DMPR3J1DFK10', 'Tablet', 'PC-DMPR3J1DFK10', 1, 0),
(128, 2496, 10, NULL, 32, 2496, 'PC-DMPS3H72G5VT', 'Apple', 'Ipad Air A1566', 'DMPS3H72G5VT', 'Tablet', 'PC-DMPS3H72G5VT', 1, 0),
(129, 2496, 10, NULL, 32, 2496, 'PC-GCKVJANYHLF9', 'Apple', 'Ipad A1822', 'GCKVJANYHLF9', 'Tablet', 'PC-GCKVJANYHLF9', 1, 0),
(130, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N587350', 'Asus', 'X540L', 'H8N0CX22N587350', 'Notebook', 'PC-H8N0CX22N587350', 1, 0),
(131, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N64435D', 'Asus', 'X540L', 'H8N0CX22N64435D', 'Notebook', 'PC-H8N0CX22N64435D', 1, 0),
(132, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M90835B', 'Asus', 'X540L', 'H8N0CX22M90835B', 'Notebook', 'PC-H8N0CX22M90835B', 1, 0),
(133, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M96135B', 'Asus', 'X540L', 'H8N0CX22M96135B', 'Notebook', 'PC-H8N0CX22M96135B', 4, 0),
(134, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M96035C', 'Asus', 'X540L', 'H8N0CX22M96035C', 'Notebook', 'PC-H8N0CX22M96035C', 1, 0),
(135, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N21835F', 'Asus', 'X540L', 'H8N0CX22N21835F', 'Notebook', 'PC-H8N0CX22N21835F', 1, 0),
(136, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M96835B', 'Asus', 'X540L', 'H8N0CX22M96835B', 'Notebook', 'PC-H8N0CX22M96835B', 1, 0),
(137, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N202357', 'Asus', 'X540L', 'H8N0CX22N202357', 'Notebook', 'PC-H8N0CX22N202357', 1, 0),
(138, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N173350', 'Asus', 'X540L', 'H8N0CX22N173350', 'Notebook', 'PC-H8N0CX22N173350', 1, 0),
(139, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N234357', 'Asus', 'X540L', 'H8N0CX22N234357', 'Notebook', 'PC-H8N0CX22N234357', 1, 0),
(140, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M80235E', 'Asus', 'X540L', 'H8N0CX22M80235E', 'Notebook', 'PC-H8N0CX22M80235E', 4, 0),
(141, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M864351', 'Asus', 'X540L', 'H8N0CX22M864351', 'Notebook', 'PC-H8N0CX22M864351', 1, 0),
(142, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M94935B', 'Asus', 'X540L', 'H8N0CX22M94935B', 'Notebook', 'PC-H8N0CX22M94935B', 1, 0),
(143, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N123353', 'Asus', 'X540L', 'H8N0CX22N123353', 'Notebook', 'PC-H8N0CX22N123353', 1, 0),
(144, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M697355', 'Asus', 'X540L', 'H8N0CX22M697355', 'Notebook', 'PC-H8N0CX22M697355', 1, 0),
(145, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N027352', 'Asus', 'X540L', 'H8N0CX22N027352', 'Notebook', 'PC-H8N0CX22N027352', 1, 0),
(146, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N278353', 'Asus', 'X540L', 'H8N0CX22N278353', 'Notebook', 'PC-H8N0CX22N278353', 1, 0),
(147, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M778354', 'Asus', 'X540L', 'H8N0CX22M778354', 'Notebook', 'PC-H8N0CX22M778354', 1, 0),
(148, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N28235F', 'Asus', 'X540L', 'H8N0CX22N28235F', 'Notebook', 'PC-H8N0CX22N28235F', 1, 0),
(149, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N208356', 'Asus', 'X540L', 'H8N0CX22N208356', 'Notebook', 'PC-H8N0CX22N208356', 1, 0),
(150, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M962351', 'Asus', 'X540L', 'H8N0CX22M962351', 'Notebook', 'PC-H8N0CX22M962351', 1, 0),
(151, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N283355', 'Asus', 'X540L', 'H8N0CX22N283355', 'Notebook', 'PC-H8N0CX22N283355', 1, 0),
(152, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N019357', 'Asus', 'X540L', 'H8N0CX22N019357', 'Notebook', 'PC-H8N0CX22N019357', 1, 0),
(153, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N56635A', 'Asus', 'X540L', 'H8N0CX22N56635A', 'Notebook', 'PC-H8N0CX22N56635A', 1, 0),
(154, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M974358', 'Asus', 'X540L', 'H8N0CX22M974358', 'Notebook', 'PC-H8N0CX22M974358', 1, 0),
(155, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M592354', 'Asus', 'X540L', 'H8N0CX22M592354', 'Notebook', 'PC-H8N0CX22M592354', 1, 0),
(156, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M920355', 'Asus', 'X540L', 'H8N0CX22M920355', 'Notebook', 'PC-H8N0CX22M920355', 4, 0),
(157, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M941359', 'Asus', 'X540L', 'H8N0CX22M941359', 'Notebook', 'PC-H8N0CX22M941359', 1, 0),
(158, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22M85835E', 'Asus', 'X540L', 'H8N0CX22M85835E', 'Notebook', 'PC-H8N0CX22M85835E', 1, 0),
(159, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N02135B', 'Asus', 'X540L', 'H8N0CX22N02135B', 'Notebook', 'PC-H8N0CX22N02135B', 4, 0),
(160, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N54335D', 'Asus', 'X540L', 'H8N0CX22N54335D', 'Notebook', 'PC-H8N0CX22N54335D', 1, 0),
(161, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N23935E', 'Asus', 'X540L', 'H8N0CX22N23935E', 'Notebook', 'PC-H8N0CX22N23935E', 1, 0),
(162, 2496, 10, NULL, 32, 2496, 'PC-H8N0CX22N552358', 'Asus', 'X540L', 'H8N0CX22N552358', 'Notebook', 'PC-H8N0CX22N552358', 1, 0),
(163, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNMS', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNMS', 'Notebook', 'PC-YX0CTNMS', 1, 0),
(164, 2496, 10, NULL, 32, 2496, 'PC-YX0CTLBM', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTLBM', 'Notebook', 'PC-YX0CTLBM', 1, 0),
(165, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNMR', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNMR', 'Notebook', 'PC-YX0CTNMR', 1, 0),
(166, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNLW', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNLW', 'Notebook', 'PC-YX0CTNLW', 1, 0),
(167, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNLD', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNLD', 'Notebook', 'PC-YX0CTNLD', 1, 0),
(168, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNM0', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNM0', 'Notebook', 'PC-YX0CTNM0', 1, 0),
(169, 2496, 10, NULL, 32, 2496, 'PC-YX0CTLBH', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTLBH', 'Notebook', 'PC-YX0CTLBH', 1, 0),
(170, 2496, 10, NULL, 32, 2496, 'PC-YX0CTLBF', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTLBF', 'Notebook', 'PC-YX0CTLBF', 1, 0),
(171, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG6', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG6', 'Notebook', 'PC-YX0DWLG6', 1, 0),
(172, 2496, 10, NULL, 32, 2496, 'PC-YX0CTLBG', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTLBG', 'Notebook', 'PC-YX0CTLBG', 1, 0),
(173, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG2', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG2', 'Notebook', 'PC-YX0DWLG2', 1, 0),
(174, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGF', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGF', 'Notebook', 'PC-YX0DWLGF', 1, 0),
(175, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNMA', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNMA', 'Notebook', 'PC-YX0CTNMA', 1, 0),
(176, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGC', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGC', 'Notebook', 'PC-YX0DWLGC', 1, 0),
(177, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGA', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGA', 'Notebook', 'PC-YX0DWLGA', 1, 0),
(178, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG8', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG8', 'Notebook', 'PC-YX0DWLG8', 1, 0),
(179, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGB', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGB', 'Notebook', 'PC-YX0DWLGB', 1, 0),
(180, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNL6', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNL6', 'Notebook', 'PC-YX0CTNL6', 1, 0),
(181, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNMH', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNMH', 'Notebook', 'PC-YX0CTNMH', 1, 0),
(182, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG1', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG1', 'Notebook', 'PC-YX0DWLG1', 1, 0),
(183, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG7', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG7', 'Notebook', 'PC-YX0DWLG7', 1, 0),
(184, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNLE', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNLE', 'Notebook', 'PC-YX0CTNLE', 1, 0),
(185, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNLM', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNLM', 'Notebook', 'PC-YX0CTNLM', 1, 0),
(186, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG3', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG3', 'Notebook', 'PC-YX0DWLG3', 1, 0),
(187, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGG', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGG', 'Notebook', 'PC-YX0DWLGG', 1, 0),
(188, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNLY', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNLY', 'Notebook', 'PC-YX0CTNLY', 1, 0),
(189, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNM1', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNM1', 'Notebook', 'PC-YX0CTNM1', 1, 0),
(190, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNM9', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNM9', 'Notebook', 'PC-YX0CTNM9', 1, 0),
(191, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNML', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNML', 'Notebook', 'PC-YX0CTNML', 1, 0),
(192, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNMY', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNMY', 'Notebook', 'PC-YX0CTNMY', 1, 0),
(193, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG9', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG9', 'Notebook', 'PC-YX0DWLG9', 1, 0),
(194, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG4', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG4', 'Notebook', 'PC-YX0DWLG4', 1, 0),
(195, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLG5', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLG5', 'Notebook', 'PC-YX0DWLG5', 1, 0),
(196, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGE', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGE', 'Notebook', 'PC-YX0DWLGE', 1, 0),
(197, 2496, 10, NULL, 32, 2496, 'PC-YX0DWLGD', 'Lenovo', '100e Chromebook Gen 4', 'YX0DWLGD', 'Notebook', 'PC-YX0DWLGD', 1, 0),
(198, 2496, 10, NULL, 32, 2496, 'PC-YX0CTNMJ', 'Lenovo', '100e Chromebook Gen 4', 'YX0CTNMJ', 'Notebook', 'PC-YX0CTNMJ', 1, 0),
(199, 2496, 10, NULL, 32, 2496, 'PC-P20A075G', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A075G', 'Notebook', 'PC-P20A075G', 1, 0),
(200, 2496, 10, NULL, 32, 2496, 'PC-PF2Z787H', 'Lenovo', '100e Chromebook Gen 2', 'PF2Z787H', 'Notebook', 'PC-PF2Z787H', 1, 0),
(201, 2496, 10, NULL, 32, 2496, 'PC-MP1WD9WN', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1WD9WN', 'Notebook', 'PC-MP1WD9WN', 1, 0),
(202, 2496, 10, NULL, 32, 2496, 'PC-MP1SM18Q', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SM18Q', 'Notebook', 'PC-MP1SM18Q', 1, 0),
(203, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKB9', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKB9', 'Notebook', 'PC-MP1SLKB9', 1, 0),
(204, 2496, 10, NULL, 32, 2496, 'PC-MP20BCTL', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BCTL', 'Notebook', 'PC-MP20BCTL', 1, 0),
(205, 2496, 10, NULL, 32, 2496, 'PC-MP1SLK9S', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLK9S', 'Notebook', 'PC-MP1SLK9S', 1, 0),
(206, 2496, 10, NULL, 32, 2496, 'PC-MP20ENBA', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20ENBA', 'Notebook', 'PC-MP20ENBA', 1, 0),
(207, 2496, 10, NULL, 32, 2496, 'PC-MP1SLT13', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLT13', 'Notebook', 'PC-MP1SLT13', 1, 0),
(208, 2496, 10, NULL, 32, 2496, 'PC-MP1SLZ0P', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLZ0P', 'Notebook', 'PC-MP1SLZ0P', 1, 0),
(209, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKBJ', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKBJ', 'Notebook', 'PC-MP1SLKBJ', 1, 0),
(210, 2496, 10, NULL, 32, 2496, 'PC-MP20BAJJ', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BAJJ', 'Notebook', 'PC-MP20BAJJ', 1, 0),
(211, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKCW', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKCW', 'Notebook', 'PC-MP1SLKCW', 1, 0),
(212, 2496, 10, NULL, 32, 2496, 'PC-MP20E105', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20E105', 'Notebook', 'PC-MP20E105', 1, 0),
(213, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKAE', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKAE', 'Notebook', 'PC-MP1SLKAE', 1, 0),
(214, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKFG', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKFG', 'Notebook', 'PC-MP1SLKFG', 1, 0),
(215, 2496, 10, NULL, 32, 2496, 'PC-MP20BFR4', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BFR4', 'Notebook', 'PC-MP20BFR4', 1, 0),
(216, 2496, 10, NULL, 32, 2496, 'PC-MP20EX0R', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20EX0R', 'Notebook', 'PC-MP20EX0R', 1, 0),
(217, 2496, 10, NULL, 32, 2496, 'PC-MP20BFNZ', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BFNZ', 'Notebook', 'PC-MP20BFNZ', 1, 0),
(218, 2496, 10, NULL, 32, 2496, 'PC-MP20BD7F', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BD7F', 'Notebook', 'PC-MP20BD7F', 1, 0),
(219, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKC4', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKC4', 'Notebook', 'PC-MP1SLKC4', 1, 0),
(220, 2496, 10, NULL, 32, 2496, 'PC-MP1SLSTF', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLSTF', 'Notebook', 'PC-MP1SLSTF', 1, 0),
(221, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKBA', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKBA', 'Notebook', 'PC-MP1SLKBA', 1, 0),
(222, 2496, 10, NULL, 32, 2496, 'PC-MP20BD22', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BD22', 'Notebook', 'PC-MP20BD22', 1, 0),
(223, 2496, 10, NULL, 32, 2496, 'PC-MP20ENB5', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20ENB5', 'Notebook', 'PC-MP20ENB5', 1, 0),
(224, 2496, 10, NULL, 32, 2496, 'PC-MP1SLQAW', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLQAW', 'Notebook', 'PC-MP1SLQAW', 1, 0),
(225, 2496, 10, NULL, 32, 2496, 'PC-MP1SLRQA', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLRQA', 'Notebook', 'PC-MP1SLRQA', 1, 0),
(226, 2496, 10, NULL, 32, 2496, 'PC-MP20BDCB', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BDCB', 'Notebook', 'PC-MP20BDCB', 1, 0),
(227, 2496, 10, NULL, 32, 2496, 'PC-MP20BHRL', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20BHRL', 'Notebook', 'PC-MP20BHRL', 1, 0),
(228, 2496, 10, NULL, 32, 2496, 'PC-MP1SLK98', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLK98', 'Notebook', 'PC-MP1SLK98', 1, 0),
(229, 2496, 10, NULL, 32, 2496, 'PC-MP20GLX8', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20GLX8', 'Notebook', 'PC-MP20GLX8', 1, 0),
(230, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKDZ', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKDZ', 'Notebook', 'PC-MP1SLKDZ', 1, 0),
(231, 2496, 10, NULL, 32, 2496, 'PC-MP1SLPQ2', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLPQ2', 'Notebook', 'PC-MP1SLPQ2', 1, 0),
(232, 2496, 10, NULL, 32, 2496, 'PC-MP1SLMA1', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLMA1', 'Notebook', 'PC-MP1SLMA1', 1, 0),
(233, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKAV', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKAV', 'Notebook', 'PC-MP1SLKAV', 1, 0),
(234, 2496, 10, NULL, 32, 2496, 'PC-MP1SLKAT', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP1SLKAT', 'Notebook', 'PC-MP1SLKAT', 1, 0),
(235, 2496, 10, NULL, 32, 2496, 'PC-MP20F6VR', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20F6VR', 'Notebook', 'PC-MP20F6VR', 1, 0),
(236, 2496, 10, NULL, 32, 2496, 'PC-P20A1LG7', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1LG7', 'Notebook', 'PC-P20A1LG7', 1, 0),
(237, 2496, 10, NULL, 32, 2496, 'PC-P209ZKSK', 'Lenovo', '100e Chromebook Gen 2 AST', 'P209ZKSK', 'Notebook', 'PC-P209ZKSK', 1, 0),
(238, 2496, 10, NULL, 32, 2496, 'PC-P20A06FY', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A06FY', 'Notebook', 'PC-P20A06FY', 1, 0),
(239, 2496, 10, NULL, 32, 2496, 'PC-P20A06SS', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A06SS', 'Notebook', 'PC-P20A06SS', 1, 0),
(240, 2496, 10, NULL, 32, 2496, 'PC-P20A07GK', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A07GK', 'Notebook', 'PC-P20A07GK', 1, 0),
(241, 2496, 10, NULL, 32, 2496, 'PC-P20A1LDS', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1LDS', 'Notebook', 'PC-P20A1LDS', 1, 0),
(242, 2496, 10, NULL, 32, 2496, 'PC-P20A1MJK', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1MJK', 'Notebook', 'PC-P20A1MJK', 1, 0),
(243, 2496, 10, NULL, 32, 2496, 'PC-P20A07LG', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A07LG', 'Notebook', 'PC-P20A07LG', 1, 0),
(244, 2496, 10, NULL, 32, 2496, 'PC-P20A03PN', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A03PN', 'Notebook', 'PC-P20A03PN', 1, 0),
(245, 2496, 10, NULL, 32, 2496, 'PC-P20A1M0D', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M0D', 'Notebook', 'PC-P20A1M0D', 1, 0),
(246, 2496, 10, NULL, 32, 2496, 'PC-P20A1M34', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M34', 'Notebook', 'PC-P20A1M34', 1, 0),
(247, 2496, 10, NULL, 32, 2496, 'PC-P20A07J7', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A07J7', 'Notebook', 'PC-P20A07J7', 1, 0),
(248, 2496, 10, NULL, 32, 2496, 'PC-P20A073J', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A073J', 'Notebook', 'PC-P20A073J', 1, 0),
(249, 2496, 10, NULL, 32, 2496, 'PC-P20A1M4Z', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M4Z', 'Notebook', 'PC-P20A1M4Z', 1, 0),
(250, 2496, 10, NULL, 32, 2496, 'PC-P20A1M9R', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M9R', 'Notebook', 'PC-P20A1M9R', 1, 0),
(251, 2496, 10, NULL, 32, 2496, 'PC-PF2XVH8B', 'Lenovo', '100e Chromebook Gen 2', 'PF2XVH8B', 'Notebook', 'PC-PF2XVH8B', 1, 0),
(252, 2496, 10, NULL, 32, 2496, 'PC-P20A03RK', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A03RK', 'Notebook', 'PC-P20A03RK', 1, 0),
(253, 2496, 10, NULL, 32, 2496, 'PC-P20A1MGT', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1MGT', 'Notebook', 'PC-P20A1MGT', 1, 0),
(254, 2496, 10, NULL, 32, 2496, 'PC-P20A06JF', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A06JF', 'Notebook', 'PC-P20A06JF', 1, 0),
(255, 2496, 10, NULL, 32, 2496, 'PC-P20A1LCL', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1LCL', 'Notebook', 'PC-P20A1LCL', 1, 0),
(256, 2496, 10, NULL, 32, 2496, 'PC-P20A1M88', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M88', 'Notebook', 'PC-P20A1M88', 1, 0),
(257, 2496, 10, NULL, 32, 2496, 'PC-P20A1LD1', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1LD1', 'Notebook', 'PC-P20A1LD1', 1, 0),
(258, 2496, 10, NULL, 32, 2496, 'PC-P20A03NY', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A03NY', 'Notebook', 'PC-P20A03NY', 1, 0),
(259, 2496, 10, NULL, 32, 2496, 'PC-P20A06HV', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A06HV', 'Notebook', 'PC-P20A06HV', 1, 0),
(260, 2496, 10, NULL, 32, 2496, 'PC-P20A06XP', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A06XP', 'Notebook', 'PC-P20A06XP', 1, 0),
(261, 2496, 10, NULL, 32, 2496, 'PC-P20A1M4B', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M4B', 'Notebook', 'PC-P20A1M4B', 1, 0),
(262, 2496, 10, NULL, 32, 2496, 'PC-P20A06KD', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A06KD', 'Notebook', 'PC-P20A06KD', 1, 0),
(263, 2496, 10, NULL, 32, 2496, 'PC-P20A072Z', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A072Z', 'Notebook', 'PC-P20A072Z', 1, 0),
(264, 2496, 10, NULL, 32, 2496, 'PC-P20A1P5Z', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1P5Z', 'Notebook', 'PC-P20A1P5Z', 1, 0),
(265, 2496, 10, NULL, 32, 2496, 'PC-P20A1M8A', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M8A', 'Notebook', 'PC-P20A1M8A', 1, 0),
(266, 2496, 10, NULL, 32, 2496, 'PC-P20A1M0K', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M0K', 'Notebook', 'PC-P20A1M0K', 1, 0),
(267, 2496, 10, NULL, 32, 2496, 'PC-P20A1LEP', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1LEP', 'Notebook', 'PC-P20A1LEP', 1, 0),
(268, 2496, 10, NULL, 32, 2496, 'PC-P20A1M4D', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A1M4D', 'Notebook', 'PC-P20A1M4D', 1, 0),
(269, 2496, 10, NULL, 32, 2496, 'PC-P20A0460', 'Lenovo', '100e Chromebook Gen 2 AST', 'P20A0460', 'Notebook', 'PC-P20A0460', 1, 0),
(270, 2496, 10, NULL, 32, 2496, 'PC-MP20E7V0', 'Lenovo', '100e Chromebook Gen 2 AST', 'MP20E7V0', 'Notebook', 'PC-MP20E7V0', 1, 0),
(271, 2496, 10, NULL, 32, 2496, 'PC-5CD1472TMZ', 'HP', '14-dq2028la', '5CD1472TMZ', 'Notebook', 'PC-5CD1472TMZ', 1, 0),
(272, 2492, 11, NULL, 0, 2492, 'PC-INSPIRON3442', 'Afterschool', 'Dell', 'Inspiron 3442', '2RQ6B12', 'PC-INSPIRON3442', 1, 1),
(273, 2492, 11, NULL, 0, 2492, 'PC-1000-1426L', 'LAPTOP_PKA', 'HP', '1000-1426L', '5CG334155K', 'PC-1000-1426L', 1, 1),
(274, 2492, 11, NULL, 0, 2492, 'PC-240G5', 'LAPTOP_PKB', 'HP', '240 G5', '5CG6266JJG', 'PC-240G5', 1, 1),
(275, 2492, 11, NULL, 0, 2492, 'PC-240G9', 'LAPTOP_1A', 'HP', '240 G9', '5CG332153Q', 'PC-240G9', 1, 1),
(276, 2492, 11, NULL, 0, 2492, 'PC-MS-7C09', 'Biblioteca01-W10', 'Micro-Star', 'MS-7C09', 'BE291A71-7515-49E8-ABA5-2D979580E9B3', 'PC-MS-7C09', 1, 1),
(277, 2492, 11, NULL, 0, 2492, 'PC-MS-7788', 'MarkQual', 'MSI', 'MS-7788', '73D5B865-4E91-4A3E-B5B3-3D2DA3240E5F', 'PC-MS-7788', 1, 1),
(278, 2492, 11, NULL, 0, 2492, 'PC-240G4', 'Notebook-Biblioteca', 'HP', '240 G4', '5CG61433FC', 'PC-240G4', 1, 1),
(279, 2492, 11, NULL, 0, 2492, 'PC-240G8', 'MZUNIGA', 'HP', '240 G8', '5CG2376DQ9', 'PC-240G8', 1, 1),
(280, 2492, 11, NULL, 0, 2492, 'PC-IDEAPADFLEX514ITL05', 'Alumna01', 'Lenovo', 'IdeaPad Flex 5 14ITL05', 'PW003277', 'PC-IDEAPADFLEX514ITL05', 1, 1);

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
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=281;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
