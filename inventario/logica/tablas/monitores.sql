-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:58:17
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
-- Estructura de tabla para la tabla `monitores`
--

CREATE TABLE `monitores` (
  `id_monitor` int(11) NOT NULL,
  `id_monitor_legacy` int(11) DEFAULT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `id_usuario_registra` int(11) DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `nombre_monitor` varchar(150) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(150) DEFAULT NULL,
  `numero_serie` varchar(150) DEFAULT NULL,
  `codigo_interno` varchar(100) DEFAULT NULL,
  `tamano_monitor` varchar(50) DEFAULT NULL,
  `resolucion_monitor` varchar(100) DEFAULT NULL,
  `tipo_panel` varchar(100) DEFAULT NULL,
  `tipo_conexion` varchar(150) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `monitores`
--

INSERT INTO `monitores` (`id_monitor`, `id_monitor_legacy`, `id_colegio`, `id_ubicacion`, `id_usuario_asignado`, `id_usuario_registra`, `id_estado`, `nombre_monitor`, `marca`, `modelo`, `numero_serie`, `codigo_interno`, `tamano_monitor`, `resolucion_monitor`, `tipo_panel`, `tipo_conexion`, `observacion`, `fecha_registro`) VALUES
(1, 269, 15, NULL, NULL, NULL, 1, 'Monitor BOE0A23', NULL, '()', NULL, 'BOE0A23', '15', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 55. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(2, 270, 15, NULL, NULL, NULL, 1, 'Monitor SAM0634', NULL, 'SMB2030N (Samsung)', 'H9LB201570', 'SAM0634', '20', '1600 x 900', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 55. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(3, 271, 15, NULL, NULL, NULL, 1, 'Monitor BOE07FF', NULL, '()', NULL, 'BOE07FF', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 56. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(4, 272, 15, NULL, NULL, NULL, 1, 'Monitor SAC952D', NULL, 'LED MONITOR ()', NULL, 'SAC952D', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 56. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(5, 273, 15, NULL, NULL, NULL, 1, 'Monitor AOC1831', NULL, '831W (AOC International)', NULL, 'AOC1831', '19', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 57. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(6, 274, 15, NULL, NULL, NULL, 1, 'Monitor BOE097D', NULL, '()', NULL, 'BOE097D', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 57. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(7, 275, 15, NULL, NULL, NULL, 1, 'Monitor BOE0920', NULL, '()', NULL, 'BOE0920', '15', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 58. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(8, 276, 15, NULL, NULL, NULL, 1, 'Monitor GSM4ED7', NULL, 'E2042 (LG Electronics (GoldStar))', '206NDSK6U16', 'GSM4ED7', '20', '1600 x 900', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 58. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(9, 279, 15, NULL, NULL, NULL, 1, 'Monitor BOE0A23', NULL, '()', NULL, 'BOE0A23', '15', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 60. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(10, 280, 15, NULL, NULL, NULL, 1, 'Monitor 280', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 60. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(11, 281, 15, NULL, NULL, NULL, 1, 'Monitor AOC1831', NULL, '831W (AOC International)', NULL, 'AOC1831', '19', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 61. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(12, 282, 15, NULL, NULL, NULL, 1, 'Monitor BOE0A23', NULL, '()', NULL, 'BOE0A23', '15', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 61. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(13, 283, 15, NULL, NULL, NULL, 4, 'Monitor BOE08CD', NULL, '()   ****', '3423424  **', 'BOE08CD', '15', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 62. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(14, 284, 15, NULL, NULL, NULL, 4, 'Monitor GSM586D', NULL, 'E2251 (LG Electronics (GoldStar))', '111LTSS3U07', 'GSM586D', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 62. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(15, 285, 15, NULL, NULL, NULL, 1, 'Monitor AUO2992', NULL, 'HP', NULL, 'AUO2992', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 63. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(16, 286, 15, NULL, NULL, NULL, 1, 'Monitor GSM5C66', NULL, 'LG FHD (LG Electronics (GoldStar))', '404TFGC0Y11', 'GSM5C66', '24', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 63. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(17, 287, 15, NULL, NULL, NULL, 1, 'Monitor CMN161A', NULL, '()', NULL, 'CMN161A', '16', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 64. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(18, 288, 15, NULL, NULL, NULL, 1, 'Monitor SAM0D20', NULL, 'S24F350 (Samsung)', 'H4ZH800057', 'SAM0D20', '23', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 64. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(19, 289, 15, NULL, NULL, NULL, 1, 'Monitor AUO4799', NULL, 'SA', 'C40H7K', 'AUO4799', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 65. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(20, 290, 15, NULL, NULL, NULL, 1, 'Monitor HPN342E', NULL, 'HP 22w', 'CNC7440H7K', 'HPN342E', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 65. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(21, 291, 15, NULL, NULL, NULL, 1, 'Monitor CMN15DC', NULL, '()', NULL, 'CMN15DC', '15', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 66. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(22, 292, 15, NULL, NULL, NULL, 1, 'Monitor 292', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 66. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(23, 293, 15, NULL, NULL, NULL, 1, 'Monitor SAM7122', NULL, 'S22A33x (Samsung)', 'H4TTC00365', 'SAM7122', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 67. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(24, 294, 15, NULL, NULL, NULL, 1, 'Monitor AUO499F', NULL, '()', NULL, 'AUO499F', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 67. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(25, 295, 15, NULL, NULL, NULL, 1, 'Monitor BOE0B14', NULL, '()', NULL, 'BOE0B14', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 68. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(26, 296, 15, NULL, NULL, NULL, 1, 'Monitor SAM08A8', NULL, 'S20B300 (Samsung)', 'HTLC401303', 'SAM08A8', '20', '1600 x 900', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 68. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(27, 297, 15, NULL, NULL, NULL, 1, 'Monitor BOE08E2', NULL, '()', NULL, 'BOE08E2', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 69. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(28, 298, 15, NULL, NULL, NULL, 1, 'Monitor 298', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 69. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(29, 299, 15, NULL, NULL, NULL, 1, 'Monitor LEN9156', NULL, '()', NULL, 'LEN9156', '16', '1920 x 1200', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 70. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(30, 300, 15, NULL, NULL, NULL, 1, 'Monitor SAM7122', NULL, 'S22A33x (Samsung)', 'H4TTC00357', 'SAM7122', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 70. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(31, 301, 15, NULL, NULL, NULL, 1, 'Monitor BOE09AE', NULL, '()', NULL, 'BOE09AE', '14', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 71. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(32, 302, 15, NULL, NULL, NULL, 1, 'Monitor 302', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 71. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(33, 303, 15, NULL, NULL, NULL, 1, 'Monitor LEN889A', NULL, '()', NULL, 'LEN889A', '14', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 72. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(34, 304, 15, NULL, NULL, NULL, 1, 'Monitor 304', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 72. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(35, 305, 15, NULL, NULL, NULL, 1, 'Monitor BOE07FF', NULL, '()', NULL, 'BOE07FF', '15', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 73. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(36, 306, 15, NULL, NULL, NULL, 1, 'Monitor AOC2202', NULL, '22B2WG5 (AOC International)', 'QNHM9HA0844', 'AOC2202', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 73. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(37, 307, 11, NULL, NULL, NULL, 1, 'Monitor BOE09AE', NULL, '()', NULL, 'BOE09AE', '14', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 74. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(38, 308, 11, NULL, NULL, NULL, 1, 'Monitor 308', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 74. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(39, 313, 15, NULL, 0, NULL, 1, 'Monitor CMN1413', NULL, '()', NULL, 'CMN1413', '14', '1366 x 768', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 59. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(40, 314, 15, NULL, 0, NULL, 1, 'Monitor 314', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 59. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(41, 333, 15, NULL, 0, NULL, 1, 'Monitor VSC0E28', NULL, 'VA2248 SERIES (ViewSonic)', 'SDD11492332', 'VSC0E28', '22', '1920 x 1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 54. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(42, 334, 15, NULL, 0, NULL, 1, 'Monitor 334', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 54. Orden monitor anterior: 2', '2026-06-19 12:20:35'),
(43, 339, 11, NULL, 2492, NULL, 1, 'Monitor 339', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 75. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(44, 341, 11, NULL, 0, NULL, 1, 'Monitor 341', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 76. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(45, 342, 15, NULL, 0, NULL, 1, 'Monitor MON-001', NULL, 'Dell P2219H', 'SN-MON-001', 'MON-001', '22', '1920x1080', NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 77. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(46, 343, 11, NULL, 0, NULL, 1, 'Monitor 343', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Migrado desde equipo_monitor. ID equipo anterior: 78. Orden monitor anterior: 1', '2026-06-19 12:20:35'),
(64, NULL, 15, 12, 2485, 42, 1, 'Monitor Externo Alejandro Rojas', 'LG', '24M8500', 'MX50947', '', '', '', '', '', '', '2026-06-19 14:40:37');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `monitores`
--
ALTER TABLE `monitores`
  ADD PRIMARY KEY (`id_monitor`),
  ADD UNIQUE KEY `uq_monitores_legacy` (`id_monitor_legacy`),
  ADD KEY `idx_monitor_colegio` (`id_colegio`),
  ADD KEY `idx_monitor_ubicacion` (`id_ubicacion`),
  ADD KEY `idx_monitor_usuario` (`id_usuario_asignado`),
  ADD KEY `idx_monitor_estado` (`id_estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `monitores`
--
ALTER TABLE `monitores`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
