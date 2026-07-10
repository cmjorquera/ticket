-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:58:40
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
-- Estructura de tabla para la tabla `monitor_movimiento`
--

CREATE TABLE `monitor_movimiento` (
  `id_movimiento` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL,
  `id_ubicacion_origen` int(11) DEFAULT NULL,
  `id_ubicacion_destino` int(11) DEFAULT NULL,
  `id_usuario_movimiento` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `monitor_movimiento`
--

INSERT INTO `monitor_movimiento` (`id_movimiento`, `id_monitor`, `id_ubicacion_origen`, `id_ubicacion_destino`, `id_usuario_movimiento`, `fecha_movimiento`, `motivo`, `observacion`) VALUES
(1, 64, NULL, 12, 42, '2026-06-19 14:40:37', 'Alta inicial de inventario', 'Monitor registrado inicialmente en esta ubicación.');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `monitor_movimiento`
--
ALTER TABLE `monitor_movimiento`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_monitor_mov_monitor` (`id_monitor`),
  ADD KEY `idx_monitor_mov_origen` (`id_ubicacion_origen`),
  ADD KEY `idx_monitor_mov_destino` (`id_ubicacion_destino`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `monitor_movimiento`
--
ALTER TABLE `monitor_movimiento`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
