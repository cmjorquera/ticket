-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 07-04-2026 a las 11:46:35
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
-- Base de datos: `qaseduc_panel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software_catalogo`
--

CREATE TABLE `software_catalogo` (
  `id_software` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_usuario_responsable` int(11) NOT NULL DEFAULT 0,
  `id_tipo_usuario` int(11) NOT NULL DEFAULT 0,
  `nombre_software` varchar(180) NOT NULL,
  `version_software` varchar(80) DEFAULT NULL,
  `cantidad_licencias` int(11) NOT NULL DEFAULT 1,
  `tipo_licenciamiento` enum('Suscripcion','Licencia perpetua','Gratuita') NOT NULL DEFAULT 'Suscripcion',
  `fecha_inicio_licencia` date DEFAULT NULL,
  `fecha_fin_licencia` date DEFAULT NULL,
  `pagado_por` enum('Colegio','Persona') NOT NULL DEFAULT 'Colegio',
  `costo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `moneda` varchar(10) NOT NULL DEFAULT 'USD',
  `proveedor` varchar(180) DEFAULT NULL,
  `url_referencia` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `id_usuario` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `software_catalogo`
--

INSERT INTO `software_catalogo` (`id_software`, `id_colegio`, `id_usuario_responsable`, `id_tipo_usuario`, `nombre_software`, `version_software`, `cantidad_licencias`, `tipo_licenciamiento`, `fecha_inicio_licencia`, `fecha_fin_licencia`, `pagado_por`, `costo`, `moneda`, `proveedor`, `url_referencia`, `observaciones`, `id_usuario`, `activo`, `created_at`, `updated_at`) VALUES
(5, 15, 42, 1, 'Zipgrade', '', 6, 'Suscripcion', '2026-03-01', '2027-03-01', '', 49.00, 'USD', 'Zipgrade', 'www.zipgrade.com', 'Evaluaciones Ensayos PAES', 42, 1, '2026-04-06 16:01:07', '2026-04-06 16:32:31'),
(6, 15, 42, 1, 'Follett Destiny', '', 5, 'Suscripcion', '0000-00-00', '0000-00-00', 'Colegio', 6600.00, 'USD', 'Follett Software', 'seduc.follettdestiny.com', 'Sistema Administrador de bibliotecas', 42, 1, '2026-04-06 16:20:28', '2026-04-06 16:24:51'),
(7, 15, 42, 1, 'Moodle', '4.2', 1, 'Gratuita', '0000-00-00', '0000-00-00', '', 500.00, 'USD', 'Moodle', 'www.seducformacion.cl', 'Licenciamiento gratuito. Solo se paga hosting', 42, 1, '2026-04-06 16:22:43', '2026-04-06 16:22:43');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `software_catalogo`
--
ALTER TABLE `software_catalogo`
  ADD PRIMARY KEY (`id_software`),
  ADD KEY `idx_software_colegio` (`id_colegio`),
  ADD KEY `idx_software_responsable` (`id_usuario_responsable`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `software_catalogo`
--
ALTER TABLE `software_catalogo`
  MODIFY `id_software` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
