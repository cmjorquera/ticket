-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-06-2026 a las 09:34:52
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
-- Estructura de tabla para la tabla `equipo_procesador`
--

CREATE TABLE `equipo_procesador` (
  `id_equipo` int(11) NOT NULL,
  `equipo_fabricante` varchar(100) DEFAULT NULL,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_velocidad` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_procesador`
--

INSERT INTO `equipo_procesador` (`id_equipo`, `equipo_fabricante`, `equipo_modelo`, `equipo_velocidad`) VALUES
(54, 'Intel(R) Corporation', 'Intel(R) Core(TM) i5-6400 CPU @ 2.70GHz', '2700.0'),
(55, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '3800.0'),
(56, 'Intel(R) Corporation', 'Intel(R) Core(TM) i5-8265U CPU @ 1.60GHz', '1485.0'),
(57, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '2400.0'),
(58, 'Advanced Micro Devices Inc.', 'AMD Ryzen 5 4500U with Radeon Graphics', '2375.0'),
(59, 'Intel(R) Corporation', 'Intel(R) Core(TM) i3-10110U CPU @ 2.10GHz', '1980.0'),
(60, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '3800.0'),
(61, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '3800.0'),
(62, 'Advanced Micro Devices Inc.  ****', 'AMD Ryzen 5 3450U with Radeon Vega Mobile Gfx (Procesador)', '2100.0'),
(63, 'Intel(R) Corporation', '12th Gen Intel(R) Core(TM) i5-12500H', '4158.0'),
(64, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i7-11800H @ 2.30GHz', '2178.0'),
(65, 'Advanced Micro Devices Inc.', 'AMD Ryzen 7 4700U with Radeon Graphics', '2000.0'),
(66, 'Intel(R) Corporation', 'Intel(R) Pentium(R) CPU 5405U @ 2.30GHz', '2178.0'),
(67, 'Intel(R) Corporation', '12th Gen Intel(R) Core(TM) i5-1235U', '3960.0'),
(68, 'Intel(R) Corporation', '12th Gen Intel(R) Core(TM) i5-1235U', '3960.0'),
(69, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '2400.0'),
(70, 'Intel(R) Corporation', '13th Gen Intel(R) Core(TM) i7-13620H', '4653.0'),
(71, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i3-1115G4 @ 3.00GHz', '3000.0'),
(72, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '2400.0'),
(73, 'Intel(R) Corporation', 'Intel(R) Core(TM) i5-8265U CPU @ 1.60GHz', '1485.0'),
(74, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i3-1115G4 @ 3.00GHz', '3000.0'),
(75, 'AMD', 'Ryzen 5 7520U', ''),
(76, 'AMD', 'Ryzen 5 7520U', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_procesador`
--
ALTER TABLE `equipo_procesador`
  ADD PRIMARY KEY (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_procesador`
--
ALTER TABLE `equipo_procesador`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
