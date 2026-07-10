-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:58:11
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
-- Estructura de tabla para la tabla `equipo_ubicacion`
--

CREATE TABLE `equipo_ubicacion` (
  `id_ubicacion` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `nombre_ubicacion` varchar(150) NOT NULL,
  `tipo_ubicacion` varchar(80) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `equipo_ubicacion`
--

INSERT INTO `equipo_ubicacion` (`id_ubicacion`, `id_colegio`, `nombre_ubicacion`, `tipo_ubicacion`, `descripcion`, `estado`) VALUES
(1, 1, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(2, 8, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(3, 9, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(4, 10, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(5, 11, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(6, 12, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(7, 13, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(8, 14, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(9, 15, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(10, 22, 'Sala Computación 1', 'Sala Computación', 'Sala de computadores del colegio', 1),
(11, 15, 'Contabilidad', 'Departamento', 'Equipos ubicados en contabilidad.', 1),
(12, 15, 'Informática', 'Departamento', 'Equipos ubicados en informática.', 1),
(13, 15, 'Recursos Humanos', 'Departamento', 'Equipos ubicados en recursos humanos.', 1),
(14, 15, 'Administración', 'Departamento', 'Equipos ubicados en administración.', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_ubicacion`
--
ALTER TABLE `equipo_ubicacion`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD UNIQUE KEY `uq_ubicacion_colegio_nombre` (`id_colegio`,`nombre_ubicacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_ubicacion`
--
ALTER TABLE `equipo_ubicacion`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
