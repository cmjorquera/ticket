-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-06-2026 a las 09:06:56
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
-- Estructura de tabla para la tabla `equipo_fotos`
--

CREATE TABLE `equipo_fotos` (
  `id_foto` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `tipo_foto` enum('normal','panoramica') NOT NULL DEFAULT 'normal',
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `orden_foto` int(11) NOT NULL DEFAULT 1,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipo_fotos`
--

INSERT INTO `equipo_fotos` (`id_foto`, `id_equipo`, `ruta_foto`, `tipo_foto`, `principal`, `orden_foto`, `fecha_subida`) VALUES
(5, 54, '/uploads/equipos/54/foto_69b58cbd80fa64.74663127.png', 'normal', 1, 1, '2026-03-14 13:28:45'),
(6, 54, '/uploads/equipos/54/foto_69b58cd6ae3ba7.53442777.png', 'normal', 1, 1, '2026-03-14 13:29:10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_fotos`
--
ALTER TABLE `equipo_fotos`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `idx_equipo_fotos_equipo` (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_fotos`
--
ALTER TABLE `equipo_fotos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
