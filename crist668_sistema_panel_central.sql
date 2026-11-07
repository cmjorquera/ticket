-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 07-11-2026 a las 11:19:10
-- Versión del servidor: 5.7.44-48
-- Versión de PHP: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `crist668_sistema_panel_central`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_cierre`
--

CREATE TABLE `almuerzo_cierre` (
  `id_cierre` int(10) UNSIGNED NOT NULL,
  `id_dia_menu` int(10) UNSIGNED NOT NULL,
  `cerrado_en` datetime NOT NULL,
  `total_menu` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `total_hipocalorico` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `total_picnic` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `total_no_almuerza` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `total_sin_respuesta` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `archivo_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enviado_a` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enviado_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_configuracion`
--

CREATE TABLE `almuerzo_configuracion` (
  `clave` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `almuerzo_configuracion`
--

INSERT INTO `almuerzo_configuracion` (`clave`, `valor`, `descripcion`, `actualizado_en`) VALUES
('dias_anticipacion', '31', 'Hasta cuántos días hacia adelante se puede dejar elegido', '2026-11-07 11:17:07'),
('hora_cierre', '10:00', 'A esta hora se cierra la elección y se genera el PDF', '2026-11-07 11:17:07'),
('hora_inicio', '08:00', 'Desde qué hora se puede elegir el almuerzo del día', '2026-11-07 11:17:07'),
('id_colegio_pdf', '15', 'El PDF incluye solo usuarios de este colegio (15 = Seduc SpA)', '2026-11-07 11:17:07'),
('id_usuario_encargado', '8', 'Usuario que publica los menús y recibe el PDF (usuarios.id)', '2026-11-07 11:17:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_dia`
--

CREATE TABLE `almuerzo_dia` (
  `id_dia_menu` int(10) UNSIGNED NOT NULL,
  `id_mes` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `id_dia` tinyint(3) UNSIGNED NOT NULL,
  `hay_servicio` tinyint(1) NOT NULL DEFAULT '1',
  `observacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_dia_semana`
--

CREATE TABLE `almuerzo_dia_semana` (
  `id_dia` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abreviatura` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `almuerzo_dia_semana`
--

INSERT INTO `almuerzo_dia_semana` (`id_dia`, `nombre`, `abreviatura`, `activo`) VALUES
(1, 'Lunes', 'Lun', 1),
(2, 'Martes', 'Mar', 1),
(3, 'Miércoles', 'Mié', 1),
(4, 'Jueves', 'Jue', 1),
(5, 'Viernes', 'Vie', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_eleccion`
--

CREATE TABLE `almuerzo_eleccion` (
  `id_eleccion` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_dia_menu` int(10) UNSIGNED NOT NULL,
  `id_menu` int(10) UNSIGNED DEFAULT NULL,
  `origen` enum('diaria','anticipada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diaria',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_menu`
--

CREATE TABLE `almuerzo_menu` (
  `id_menu` int(10) UNSIGNED NOT NULL,
  `id_dia_menu` int(10) UNSIGNED NOT NULL,
  `id_tipo` tinyint(3) UNSIGNED NOT NULL,
  `entrada` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plato_fondo` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postre` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_menu_dia`
--

CREATE TABLE `almuerzo_menu_dia` (
  `fecha` date NOT NULL,
  `id_menu_mes` int(11) NOT NULL,
  `menu_dia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hipocalorico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picnic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_menu_mes`
--

CREATE TABLE `almuerzo_menu_mes` (
  `id_menu_mes` int(11) NOT NULL,
  `anio` smallint(6) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `publicado_por` int(11) NOT NULL,
  `publicado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_mes`
--

CREATE TABLE `almuerzo_mes` (
  `id_mes` int(10) UNSIGNED NOT NULL,
  `anio` smallint(5) UNSIGNED NOT NULL,
  `mes` tinyint(3) UNSIGNED NOT NULL,
  `estado` enum('borrador','publicado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `id_usuario` int(11) NOT NULL,
  `publicado_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_pedido`
--

CREATE TABLE `almuerzo_pedido` (
  `id_usuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `opcion` enum('menu','hipo','picnic','no') COLLATE utf8mb4_unicode_ci NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almuerzo_tipo`
--

CREATE TABLE `almuerzo_tipo` (
  `id_tipo` tinyint(3) UNSIGNED NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icono` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` char(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `almuerzo_tipo`
--

INSERT INTO `almuerzo_tipo` (`id_tipo`, `codigo`, `nombre`, `descripcion`, `icono`, `color`, `orden`, `activo`) VALUES
(1, 'menu', 'Menú del día', 'Almuerzo tradicional: entrada, plato de fondo y postre.', 'bi-egg-fried', '#005B96', 1, 1),
(2, 'hipocalorico', 'Hipocalórico', 'Opción liviana, baja en calorías.', 'bi-heart-pulse', '#3C8D2F', 2, 1),
(3, 'picnic', 'Picnic', 'Para llevar: sándwich o similar, bebida y fruta.', 'bi-basket', '#C98A00', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_adjuntos_ticket`
--

CREATE TABLE `archivos_adjuntos_ticket` (
  `id` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `adjunto` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivos_adjuntos_ticket`
--

INSERT INTO `archivos_adjuntos_ticket` (`id`, `id_ticket`, `adjunto`) VALUES
(171, 515, 'archivos/69c19a8d723fd-WhatsApp Image 2026-03-02 at 11.50.00 AM.jpeg'),
(133, 385, 'archivos/686d74866c8d8-5270924550.pdf'),
(134, 385, 'archivos/adjuntosConversacionTicket/1752005369_5237774012.pdf'),
(167, 471, 'archivos/69611abd87d13-reporte_cursos.xlsx'),
(166, 470, 'archivos/696112ecf0757-imagen.jpg'),
(165, 460, 'archivos/adjuntosConversacionTicket/1761670897_reglas_tabancura.png'),
(164, 453, 'archivos/68ed555dcd937-Listado (9).xls'),
(163, 452, 'archivos/68ed0a1f7d7ac-Resumen colegios para Carga Masiva.xlsx'),
(162, 451, 'archivos/68e79dbc09d19-pATRONhUIN.xlsx'),
(160, 423, 'archivos/68a78d6c40db5-7A.pdf'),
(147, 391, 'archivos/adjuntosConversacionTicket/1752250594_rack_A.png'),
(155, 410, 'archivos/6888f3355aa55-JUAN DE DIOS FUENZALIDA IZQUIERDO IVA.pdf'),
(174, 520, 'archivos/adjuntosConversacionTicket/1774440383_modulo_eliminar_ticket_00.png'),
(175, 520, 'archivos/adjuntosConversacionTicket/1774440409_modulo_eliminar_ticket_01.png'),
(176, 520, 'archivos/adjuntosConversacionTicket/1774440419_modulo_eliminar_ticket_02.png'),
(177, 520, 'archivos/adjuntosConversacionTicket/1774440429_modulo_eliminar_ticket_03.png'),
(178, 520, 'archivos/adjuntosConversacionTicket/1774440440_modulo_eliminar_ticket_04.png'),
(179, 529, 'archivos/69d5223781325-Inicio SIAE.png'),
(183, 538, 'archivos/69d80c4a89046-22329C34-D290-40A3-B1B0-560479FB1E31.jpeg'),
(182, 538, 'archivos/69d80c4a88a8e-software_5.pdf'),
(184, 539, 'archivos/69d80ddc05881-_$DistribucionHoras_ColegioLosAlerces_2025_Vigentes_10-03-2026.xlsx'),
(185, 539, 'archivos/69d80ddc05e31-inventario_software_20260407_135732.pdf'),
(186, 540, 'archivos/69d80e4e579ac-inventario_software_20260407_135732.pdf'),
(187, 540, 'archivos/69d80e4e57edd-ejemplo_caraga_masiva_bloqueado.xlsx'),
(188, 541, 'archivos/69d8111ab0c86-software_5.pdf'),
(189, 583, 'archivos/6a398fed31086-Encuesta capacitación PC 2026(1).xlsx'),
(190, 588, 'archivos/6aa00d8c1b560-diagrama _presupuesto.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area_trabajo`
--

CREATE TABLE `area_trabajo` (
  `id_area` int(10) NOT NULL,
  `nombre_area` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `encargado_area` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `sigla_area` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `correo_encargado` varchar(100) COLLATE utf8_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `area_trabajo`
--

INSERT INTO `area_trabajo` (`id_area`, `nombre_area`, `encargado_area`, `sigla_area`, `correo_encargado`) VALUES
(1, 'INFORMATICA', 'Manuel Gonzalez', 'info', 'manuel@seduc.cl'),
(2, 'CONTABILIDAD', 'Chavez Marcos', 'cont', 'marcos@seduc.cl'),
(3, 'RECURSOS HUMANOS', 'Constanza Alamos', 'RRHH', 'guadalupe@seduc.cl'),
(4, 'PROFESORAS', ' Felipe Contreras', 'O.Y.H', 'felipe@seduc.cl'),
(5, 'PREVENCION ', 'Gonzalo ', 'pr', 'gonzalo@seduc.cl'),
(6, 'VISITAS', 'xxxx', 'Vis', 'xxx@seduc.cl'),
(7, 'GERENCIA', 'ramon', 'Dios', 'dios@seduc.cl'),
(8, 'FINANZAS', 'Yanalis Reyes', 'FIN', 'yreyes@seduc.cl'),
(9, 'PERIODISTAS', 'Antonip Valdes', 'PER', 'avaldes@seduc.cl'),
(10, 'ADMINISTADOR COLEGIO', 'CCCC', 'RWE', ''),
(11, 'INFORMATICA-COLEGIO', '', 'info-colegio', 'manuel@seduc.cl');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseo_categoria_catalogo`
--

CREATE TABLE `aseo_categoria_catalogo` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aseo_categoria_catalogo`
--

INSERT INTO `aseo_categoria_catalogo` (`id_categoria`, `nombre_categoria`, `activo`) VALUES
(1, 'Desinfectantes', 1),
(2, 'Limpieza general', 1),
(3, 'Pisos', 1),
(4, 'Baños', 1),
(5, 'Vidrios', 1),
(6, 'Lavandería', 1),
(7, 'Consumibles', 1),
(8, 'Otro', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseo_historial`
--

CREATE TABLE `aseo_historial` (
  `id_historial` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `accion` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseo_movimientos`
--

CREATE TABLE `aseo_movimientos` (
  `id_movimiento` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` enum('ingreso','salida') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_movimiento` datetime NOT NULL,
  `responsable` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `id_usuario` int(11) NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseo_productos`
--

CREATE TABLE `aseo_productos` (
  `id_producto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `nombre_producto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unidad_medida` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `stock_minimo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aseo_unidad_catalogo`
--

CREATE TABLE `aseo_unidad_catalogo` (
  `id_unidad` int(11) NOT NULL,
  `nombre_unidad` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aseo_unidad_catalogo`
--

INSERT INTO `aseo_unidad_catalogo` (`id_unidad`, `nombre_unidad`, `activo`) VALUES
(1, 'Litro', 1),
(2, 'Botella', 1),
(3, 'Bidón', 1),
(4, 'Unidad', 1),
(5, 'Caja', 1),
(6, 'Bolsa', 1),
(7, 'Paquete', 1),
(8, 'Galón', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificacion_tickett`
--

CREATE TABLE `calificacion_tickett` (
  `id` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_tecnico` int(11) NOT NULL,
  `id_calificacion` int(11) NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `ip_usuario` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calificacion_tickett`
--

INSERT INTO `calificacion_tickett` (`id`, `id_ticket`, `id_usuario`, `id_tecnico`, `id_calificacion`, `comentario`, `fecha`, `hora`, `ip_usuario`) VALUES
(80, 504, 42, 7, 4, 'USUARIOS OK', '2026-03-16', '11:00:14', '186.67.42.46'),
(81, 495, 42, 42, 4, 'Perfecto', '2026-03-16', '11:00:52', '186.67.42.46'),
(82, 496, 42, 42, 4, 'OK', '2026-03-16', '11:01:17', '186.67.42.46'),
(83, 472, 42, 8, 4, 'Está OK', '2026-03-16', '15:07:29', '186.67.42.46'),
(84, 423, 45, 6, 4, NULL, NULL, NULL, ''),
(85, 460, 45, 7, 4, NULL, NULL, NULL, ''),
(79, 476, 42, 7, 4, '', '2026-03-12', '10:53:12', '186.67.42.46'),
(77, 478, 42, 42, 3, 'Se modifica con solución alternativa', '2026-03-09', '15:53:10', '186.67.42.46'),
(78, 486, 42, 6, 4, 'Correcciones OK', '2026-03-10', '15:34:20', '186.67.42.46'),
(76, 487, 42, 42, 4, 'Licencias OK', '2026-03-09', '15:51:30', '186.67.42.46'),
(67, 471, 42, 42, 4, 'Todo Queda OK', '2026-01-12', '10:57:34', '186.67.42.46'),
(68, 469, 42, 7, 4, 'Arreglado muy rapidamente', '2026-01-12', '10:58:05', '186.67.42.46'),
(69, 485, 42, 7, 4, 'Perfecto', '2026-03-09', '15:44:52', '186.67.42.46'),
(70, 483, 42, 42, 4, 'OK', '2026-03-09', '15:45:42', '186.67.42.46'),
(71, 482, 42, 7, 4, 'quedan OK', '2026-03-09', '15:46:10', '186.67.42.46'),
(72, 479, 42, 42, 4, 'Listo', '2026-03-09', '15:46:40', '186.67.42.46'),
(73, 477, 42, 42, 4, 'Perfil OK', '2026-03-09', '15:47:07', '186.67.42.46'),
(74, 473, 42, 42, 4, 'Curso probado OK', '2026-03-09', '15:47:44', '186.67.42.46'),
(75, 488, 42, 42, 4, 'EPHC1 OK', '2026-03-09', '15:51:03', '186.67.42.46'),
(66, 451, 42, 42, 4, 'El tecnico realizÃ³ el trabajo a la perfeccion', '2025-10-09', '15:13:50', '186.67.42.46'),
(64, 418, 42, 6, 4, 'Recibido OK', '2025-07-30', '16:52:50', '186.67.42.46'),
(63, 419, 42, 6, 4, 'Archivo recibido correctamente', '2025-07-30', '16:52:15', '186.67.42.46'),
(62, 394, 45, 7, 2, 'probando', '2025-07-24', '14:19:12', '186.67.42.46'),
(59, 385, 31, 7, 4, 'todo perfecto !!!', '2025-07-08', '16:27:30', '186.67.42.46'),
(60, 391, 31, 7, 4, 'buen trabajo', '2025-07-11', '12:23:46', '186.67.42.46'),
(86, 11, 10, 6, 4, '', '2026-03-24', '10:00:59', '186.67.42.46'),
(87, 513, 42, 42, 4, 'Cambio OK', '2026-03-24', '10:06:43', '186.67.42.46'),
(88, 521, 42, 42, 4, 'Correos OK', '2026-03-25', '13:04:07', '186.67.42.46'),
(89, 527, 8, 8, 4, 'ok.', '2026-04-10', '10:43:04', '186.67.42.46'),
(90, 529, 42, 6, 4, 'ok', '2026-04-10', '10:46:15', '186.67.42.46'),
(91, 520, 8, 8, 4, 'listo', '2026-04-10', '12:11:32', '186.67.42.46'),
(92, 579, 42, 42, 4, 'OK', '2026-05-19', '09:07:08', '186.67.42.46'),
(93, 578, 42, 6, 4, '', '2026-07-22', '16:22:22', '186.67.42.46'),
(94, 575, 42, 42, 4, '', '2026-07-22', '16:22:46', '186.67.42.46'),
(95, 580, 42, 42, 4, '', '2026-07-22', '16:23:01', '186.67.42.46'),
(96, 468, 42, 6, 4, '', '2026-08-14', '14:26:16', '186.67.42.46'),
(97, 582, 42, 42, 4, '', '2026-08-14', '14:26:32', '186.67.42.46'),
(98, 581, 42, 42, 4, '', '2026-08-14', '14:27:23', '186.67.42.46'),
(99, 528, 8, 8, 1, NULL, NULL, NULL, '186.67.42.46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_de_ticket`
--

CREATE TABLE `categoria_de_ticket` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abreviacion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `id_colegio` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria_de_ticket`
--

INSERT INTO `categoria_de_ticket` (`id_categoria`, `nombre_categoria`, `abreviacion`, `icono`, `orden`, `estado`, `id_colegio`) VALUES
(1, 'Seduc Servicios', 'S.Servicios', 'bi-building', 1, 1, NULL),
(2, 'Siae', 'Siae', 'bi-person-workspace', 2, 1, NULL),
(3, 'Redes', 'Redes', 'bi-wifi', 3, 1, NULL),
(4, 'Telefonía', 'Telefonia', 'bi-telephone', 4, 1, NULL),
(5, 'Impresiones', 'Impresiones', 'bi-printer', 5, 1, NULL),
(6, 'Soporte', 'Soporte', 'bi-tools', 6, 1, NULL),
(7, 'Correos', 'Correos', 'bi-envelope', 7, 1, NULL),
(10, 'Otros', 'Otros', 'bi-puzzle', 11, 1, NULL),
(8, 'Moddle', 'Moodle', 'bi-box', 8, 1, NULL),
(9, 'Follett', 'Follett', 'bi-book', 9, 1, NULL),
(11, ' Sistema de  Ticket', 'S. Ticket', 'bi-server', 10, 1, NULL),
(12, 'Web', 'Paginas Web ', 'bi bi-diagram-3', 12, 1, NULL),
(16, 'Licenciamiento', 'Licen', 'bi-key-fill', 13, 1, NULL),
(17, 'BUK', 'buk', 'bi-people-fill', 14, 1, NULL),
(18, 'Envio Mail', 'EMail', 'bi-envelope-fill', 15, 1, NULL),
(19, 'Colexia', 'cla', 'bi-wifi', 16, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_herramienta_catalogo`
--

CREATE TABLE `categoria_herramienta_catalogo` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria_herramienta_catalogo`
--

INSERT INTO `categoria_herramienta_catalogo` (`id_categoria`, `nombre_categoria`, `activo`) VALUES
(1, 'Manual', 1),
(2, 'Electrica', 1),
(3, 'Medicion', 1),
(4, 'Seguridad', 1),
(5, 'Mantencion', 1),
(6, 'Jardineria', 1),
(7, 'Limpieza', 1),
(8, 'Otro', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_tecnico`
--

CREATE TABLE `categoria_tecnico` (
  `id_categoria_tecnico` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_tecnico` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria_tecnico`
--

INSERT INTO `categoria_tecnico` (`id_categoria_tecnico`, `id_categoria`, `id_tecnico`) VALUES
(140, 4, 7),
(134, 1, 6),
(114, 2, 6),
(139, 3, 7),
(141, 5, 8),
(142, 8, 42),
(144, 9, 42),
(145, 6, 7),
(156, 18, 6),
(147, 11, 8),
(148, 7, 7),
(155, 16, 7),
(154, 17, 42),
(153, 12, 7),
(158, 19, 42);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colegio`
--

CREATE TABLE `colegio` (
  `id_colegio` int(11) NOT NULL,
  `nom_colegio` varchar(60) COLLATE utf8_spanish_ci NOT NULL,
  `rza_colegio` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `nco_colegio` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `dir_colegio` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `rbd_colegio` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `id_dependencia` int(11) NOT NULL,
  `id_comuna` int(11) NOT NULL,
  `tel_colegio` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `web_colegio` varchar(60) COLLATE utf8_spanish_ci NOT NULL,
  `bd` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `orden` int(11) NOT NULL,
  `email_entrevista` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `num_r_educacion` int(11) NOT NULL,
  `ano_r_educacion` int(11) NOT NULL,
  `ip` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `email_comunicaciones` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `sexo` int(11) NOT NULL DEFAULT '0',
  `multi_cole` varchar(2) COLLATE utf8_spanish_ci NOT NULL DEFAULT 'no',
  `identificador` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `rut_colegio` varchar(12) COLLATE utf8_spanish_ci NOT NULL,
  `correo_contrato` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `url_pagina` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT '1' COMMENT '1 = activo |\r\n0 = inactivo\r\n\r\n',
  `color_principal` varchar(7) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Color principal',
  `color_secundario` varchar(7) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Color secundario',
  `color_terciario` varchar(7) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Color terciario',
  `color_cuaternario` varchar(7) COLLATE utf8_spanish_ci DEFAULT NULL COMMENT 'Color cuaternario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `colegio`
--

INSERT INTO `colegio` (`id_colegio`, `nom_colegio`, `rza_colegio`, `nco_colegio`, `dir_colegio`, `rbd_colegio`, `id_dependencia`, `id_comuna`, `tel_colegio`, `web_colegio`, `bd`, `orden`, `email_entrevista`, `num_r_educacion`, `ano_r_educacion`, `ip`, `email_comunicaciones`, `sexo`, `multi_cole`, `identificador`, `rut_colegio`, `correo_contrato`, `url_pagina`, `estado`, `color_principal`, `color_secundario`, `color_terciario`, `color_cuaternario`) VALUES
(1, 'Colegio Cordillera de las Condes', 'SEDUC Spa y Compañia CPA Cuatro', 'Colegio Cordillera', 'Los Pumas Nro 12015', '8902-8', 2, 290, '4295900', 'http://www.colegiocordillera.cl', 'seduc', 4, 'info@colegiocordillera.cl', 40, 1983, '200.111.15.3', 'comunicacion@colegiocordillera.cl', 1, 'no', 'CBHgdA8z7j74n3933pKQ', '79848720-5', 'cm.mail.com', 'cordillera.php', 1, '#0D3B8E', '#C5332E', '#EAEAEA', '#6F87A8'),
(8, 'Colegio Tabancura', 'SEDUC Spa y Compañia CPA Dos', 'Colegio Tabancura', 'Las Hualtatas Nro 10650', '8862-5', 2, 308, '8976300', 'http://www.tabancura.cl', 'seduc', 2, 'consejodireccion@tabancura.cl', 152, 1986, '152.231.78.42', 'consejodireccion@tabancura.cl', 1, 'no', '64N93dwf6k76P3N9y858', '83332100-5', 'administradorColegioTabancura@gmail.com', 'tabancura.php', 1, '#050251', '#185519', NULL, NULL),
(9, 'Colegio Los Andes de Vitacura', 'SEDUC Spa y Compañia CPA Uno', 'Colegio Los Andes', 'San Damian Nro 0100', '8871-4', 5, 308, '232232500', 'http://www.colegiolosandes.cl/', 'seduc', 1, 'entrevista@colegiolosandes.cl', 10118, 1979, '152.231.77.98', 'colegiolosandes@colegiolosandes.cl', 2, 'no', 'uoB53uIn51qb1ad4537u', '70054800-7', 'administacionColegiolosandes@gmail.com', 'losAndess.php', 1, '#2D2F86', '#F13B45', '#B8953C', '#3F5F57'),
(10, 'Colegio Los Alerces', 'SEDUC Spa y Compañia CPA Cinco', 'Colegio Los Alerces', 'El Radal Nro 437', '24979-3', 4, 291, '228207900', 'http://www.colegiolosalerces.cl/', 'seduc', 5, 'colegioalerces@siae.cl', 3127, 1996, '186.67.49.138', 'colegiolosalerces@colegiolosalerces.cl', 2, 'no', 'RxLNwR61Vee02s2VzKyi', '87152200-6', 'lupejorquera@gmail.com', 'losAlerces.php', 1, '#4E7C6F', '#C53A34', '#3F6A5F', '#D9D9D9'),
(11, 'Colegio Huelén', 'SEDUC Spa y Compañia CPA Tres', 'COLEGIO HUELEN', 'Av. Santa María Nro 6.480', '8953-2', 308, 308, '', 'http://www.colegiohuelen.cl/', 'seduc', 3, 'entrevista@huelen.cl', 5171, 1978, '152.231.77.114', 'comunicaciones@huelen.cl', 2, 'no', '2sc922imu2eFvyvsX53B', '87042000-5', 'administradorCOLEGIOHUELEN@gmail.com', 'hulen.php', 1, '#0A3E8A', '#66C05D', '#E6E6E6', '#1A2A5A'),
(12, 'Colegio Cantagallo', 'SEDUC Spa y Compañia CPA Siete', 'Colegio Cantagallo', ' Av. Monseñor Escrivá de Balaguer Nro 13.322', '12345-6', 0, 290, '0', 'http://colegiocantagallo.cl/', 'seduc', 10, 'secretaria@colegiocantagallo.cl', 0, 0, '190.82.87.210', 'secretaria@colegiocantagallo.cl', 1, 'si', 'j6i8IdpDB7jsp52yE3Iw', '76328035-7', 'cm.jorquerag@gmail.com', NULL, 1, '#7C9E10', '#95B62A', '#F4F4F4', '#8B6A2B'),
(13, 'Colegio Huinganal', 'SEDUC Spa y Compañia CPA Seis', 'Colegio Huinganal', 'Av. Monseñor Adolfo Rodríguez Nro 13210', '20311-4', 0, 308, '225921720', 'http://www.colegiohuinganal.cl/', 'seduc', 7, 'huinganal@siae.cl', 3523, 2014, '', 'comunicaciones@colegiohuinganal.cl', 1, 'no', 'KH0RWJv5N7P4HcL333WK', '76232345-1', 'cm.jorquerag@gmail.com', 'huinganal.php', 1, '#001F3F', '#D9A12A', '#F5F5F5', '#143B5C'),
(14, 'Colegio Prueba', 'Colegio Prueba Ltda', 'Colegio Prueba', 'direccion', '9999-9', 1, 290, '111111', '', 'prueba', 1, 'soporte@siae.cl', 1999, 2011, '', 'soporte@siae.cl', 0, 'no', '3K67M9z8801aF3YNt6c0', '', 'administradorColegioPrueba@gmail.com', 'colegioPrueba.php', 1, '#6E6E6E', NULL, NULL, NULL),
(15, 'Seduc SpA', 'Seduc SpA', 'Seduc', 'Las Hualtatas 10030', '9876-5', 3, 290, '224380300', 'www.seduc.cl', 'seduc', 0, 'seduc.informa@seduc.cl', 0, 0, '186.67.42.46', 'seduc.informa@seduc.cl', 0, 'no', '024Jw16809k8gopl85fJ', '', 'cm.jorquerag@gmail.com', 'seduc.php', 1, '#163A5F', '#3F6EA6', '#6AA84F', '#E2A728'),
(17, 'Valle Alegre', 'Valle Alegre', 'Valle Alegre', ' ', '11111-1', 0, 290, '0', 'http://www.valegre.cl/', 'seduc', 11, 'secretaria@valegre.cl', 0, 0, '190.82.87.210', 'secretaria@valegre.cl', 1, 'no', 'tte45asashw22346hh', '', 'administra@gmail.com', NULL, 2, '#73C242', NULL, NULL, NULL),
(22, 'Colegio Pinares', 'Sociedad Administradora Educacional y Cía. CPA Pin', 'Pinares', 'Camino a Chiguayante N 5583', 'b', 0, 0, '', '', '', 0, '', 0, 0, '', '', 0, 'no', '', '87019400-5', 'administradorPinares@gmail.com', NULL, 1, NULL, NULL, NULL, NULL),
(23, 'Colegio Itahue', 'Sociedad Administradora Educacional y Cía. CPA Ita', 'Itahue', '', 'a', 0, 0, '', '', '', 0, '', 0, 0, '', '', 0, 'no', '', '87019200-2', 'administradorItahue@gmail.com', NULL, 2, NULL, NULL, NULL, NULL),
(24, 'Alto Rio', 'Alto Rio', 'Alto Rio', '', 'd', 0, 0, '', '', '', 0, '', 0, 0, '', '', 0, 'no', '', '', 'administradorAlto Rio@gmail.com', NULL, 2, NULL, NULL, NULL, NULL),
(25, 'Adesa', 'Adesa', 'Adesa', '', 'E', 0, 0, '', '', '', 0, '', 0, 0, '', '', 0, 'no', '', '', 'administradorAdesa@gmail.com', NULL, 2, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colegio_departamento`
--

CREATE TABLE `colegio_departamento` (
  `id` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_departamento` int(11) NOT NULL,
  `correo_departamento` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contenedor`
--

CREATE TABLE `contenedor` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `url_` varchar(500) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `descripcion` varchar(100) NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `contenedor`
--

INSERT INTO `contenedor` (`id`, `id_usuario`, `nombre`, `url_`, `imagen`, `descripcion`, `fecha`, `hora`) VALUES
(1, 8, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(221, 8, 'seduc', 'https://mail.google.com/mail/u/0/#inbox', 'gmail.jpg', '', '2025-02-26', '15:14:01'),
(4, 8, 'cpanel', 'https://cp.seduc.cl:2083/', 'panel-de-control-de-cpanel-1.png', '', '2024-04-24', '16:04:29'),
(5, 8, 'La tercera', 'https://www.latercera.com/', 'la_tercera.png', '', '2024-04-24', '16:04:29'),
(6, 8, 'Hosgator', 'https://cliente.hostgator.cl/', 'hosgator.png', '', '2024-04-24', '16:04:29'),
(254, 8, 'Netflix', 'https://www.netflix.com/browse', 'usuarios_contenedores/usuario_1/netflix-20260515192829.png', '', '2026-05-15', '19:27:18'),
(8, 3, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(9, 3, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(10, 4, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(250, 8, 'Sistema central Seduc', 'https://qa.seduc.cl/jorquera/sistema_panel-qa/version2/login.php', '', '', '2026-05-10', '18:04:31'),
(12, 4, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(13, 5, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(14, 5, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(15, 5, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(16, 6, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(17, 6, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(212, 7, 'GOOGLE', 'www.google.cl', 'google_.png', '', '2024-06-19', '10:24:13'),
(215, 7, 'CORREO', 'https://https//mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'resized_GMAIL.jpeg', '', '2024-06-19', '10:30:31'),
(21, 7, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(264, 8, 'prepuesto qa', 'https://qa.seduc.cl/jorquera/sistema_personal_8/index.php', 'imagenes/usuarios_contenedores/usuario_8/contenedor_0484053bb1d635d9f466c9c2.png', '', NULL, NULL),
(174, 28, 'Seduc', 'www.seduc.cl', '', '', '2024-06-04', '10:36:30'),
(222, 8, 'Duoc', 'https://campusvirtual.duoc.cl/ultra/stream', 'duoc_ava.jpg', '', '2025-05-15', '20:05:05'),
(25, 9, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(26, 9, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(27, 9, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(28, 10, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(29, 10, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(30, 10, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(31, 11, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(32, 11, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(33, 11, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(34, 12, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(35, 12, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(36, 12, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(37, 13, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(38, 13, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(39, 13, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(40, 14, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(41, 14, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(42, 14, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(43, 15, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(44, 15, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(45, 15, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(46, 16, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(47, 16, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(48, 16, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(49, 17, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(50, 17, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(51, 17, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(52, 18, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(53, 18, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(54, 18, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(55, 19, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(56, 19, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(57, 19, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(58, 20, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(59, 20, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(60, 20, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(61, 21, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(62, 21, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(63, 21, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(64, 22, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(65, 22, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(66, 22, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(67, 23, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(68, 23, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(69, 23, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(70, 24, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(71, 24, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(72, 24, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(73, 25, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(74, 25, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(75, 25, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(76, 26, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(77, 26, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(78, 26, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(79, 27, 'Google', 'www.google.cl', 'google.png', '', '2024-04-24', '16:04:29'),
(80, 27, 'Correo', 'https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox', 'correo_electronico.png', '', '2024-04-24', '16:04:29'),
(81, 27, 'Rexm', 'URL_de_rexm', 'rex_mas.png', '', '2024-04-24', '16:04:29'),
(198, 35, '4444444', '444444', 'resized_deporte.jpeg', '', '2024-06-12', '10:30:41'),
(143, 7, 'FIN700', 'http://198.41.33.125/Fin700Seduc/', 'logo_contenedor.png', '', '2024-05-10', '15:23:54'),
(263, 8, 'Claude', 'https://claude.ai/new', 'imagenes/usuarios_contenedores/usuario_8/contenedor_8703eed9d9f67a68c08e315b.png', '', NULL, NULL),
(142, 7, 'Seduc', 'www.seduc.cl', 'logo_contenedor.png', '', '2024-05-03', '09:43:23'),
(145, 7, 'DBNet', 'https://fe.cl.dbnetcorp.com/SE62/DBNET_GENE', 'logo_contenedor.png', '', '2024-05-10', '15:25:19'),
(223, 8, 'Banco Chile', 'https://login.portales.bancochile.cl/login?state=hKFo2SByLU5ySUdCUG9oZTJTdUpCX19oQUF1cTNNdHEzNTFCd6FupWxvZ2luo3RpZNkgczdEOE5EeThUaHBHNkVGMnU5QTdkc3JHek5VaUZ4NkGjY2lk2SBmUDdKVmRvcGV2VGFvZmJYcmZrMUZUZW9sVTVYTDE2NQ&client=fP7JVdopevTaofbXrfk1FTeolU5XL165&pro', 'el-banco-de-chile-vector-logo.png', '', '2026-03-03', '08:35:03'),
(153, 25, 'YOUTUBE', 'https://www.youtube.com/watch?v=t62CCTH-OlY&list=RD1rcU4v-R_gY&index=32', 'trabajo.jpg', '', '2024-05-27', '09:10:57'),
(156, 7, 'NIC', 'www.nic.cl', 'Oferta comercial SEDUC.png', '', '2024-05-31', '15:06:26'),
(196, 35, '3333', '3333', 'resized_grafico_de_barras.jpg', '', '2024-06-12', '10:29:03'),
(170, 28, 'SIAE', 'www.siae.cl', 'logo_contenedor.png', '', '2024-06-03', '14:54:33'),
(224, 8, 'Yotube', 'https://www.youtube.com/?app=desktop&hl=es', 'Youtube_logo.png', '', '2026-03-03', '08:48:19'),
(225, 8, 'banco scotiabank', 'https://banco.scotiabank.cl/', 'banco-santander-20260329202156.png', '', '2026-03-26', '18:53:40'),
(259, 8, 'reconocimiento facial alumnos', 'https://qa.seduc.cl/jorquera/sistema_reconocimiento_facial/', 'usuarios_contenedores/usuario_1/reconocimientofacial_alumnos-20260526143843.jpg', '', '2026-05-26', '14:37:53'),
(226, 8, 'Sistema Ticket', 'https://acceso.seduc.cl/', 'logo_ticket.jpeg', '', '2026-03-26', '18:59:05'),
(227, 8, 'sistema calculo horas', 'https://qa.seduc.cl/calculo_horas/login.php', 'sistema-calculo-horas-20260326190108.png', '', '2026-03-26', '19:01:08'),
(228, 8, 'Sistema Caja Tesis', 'https://qa.seduc.cl/jorquera/tesis/', 'seduc_servicios.png', '', '2026-03-26', '19:05:31'),
(229, 8, 'sisema personal seduc', 'https://personas.seduc.cl/', 'sistema_personal_seduc.jpg', '', '2026-03-26', '19:08:42'),
(230, 8, 'sitema finanzas', 'https://qa.seduc.cl/jorquera/sistema_finanzas/explicacion.php', 'FINAZAS.jpeg', '', '2026-03-26', '19:12:59'),
(231, 8, 'sitema compras', 'https://qa.seduc.cl/jorquera/sistema_compras/explicacion.php', 'sistema_universal.jpg', '', '2026-03-26', '19:14:19'),
(232, 8, 'duoc VIVO', 'https://experienciavivo.duoc.cl/alumnos/?_gl=1*li8iqv*_gcl_au*MTA3NzU3NzkxMS4xNzc0Mjk4OTAx*_ga*MTY2ODM2OTE1MC4xNzcyNDYyMTg3*_ga_JMVN3R1WDD*czE3NzQ1Njc5NTYkbzQ2JGcxJHQxNzc0NTY4MTkzJGozNyRsMCRoMA..', 'duoc-VIVO-20260326203856.png', '', '2026-03-26', '20:37:20'),
(233, 8, 'colegio san pablo', 'https://qa.seduc.cl/jorquera/colegiosanpablo/', 'colegio-san-pablo-20260327090208.png', '', '2026-03-27', '09:01:32'),
(234, 8, 'chat gpt', 'https://chatgpt.com/', 'chat-gpt-20260327111324.png', '', '2026-03-27', '11:12:22'),
(235, 8, 'Banco Itau', 'https://banco.itau.cl/wps/portal/newolb/web/login/!ut/p/z1/04_Sj9CPykssy0xPLMnMz0vMAfIjo8ziLf1dzIw8DYz8DLxcjA3MTIJNA52MQwwMjMz1wwkpiAJKG-AAjgZA_VFgJXAT_D28DQ0CvY0CLT0NAgP8jQygCvCYUZAbYZDpqKgIAFKI8TE!/dz/d5/L2dBISEvZ0FBIS9nQSEh/?action=logout', 'Banco-Itau-20260329201834.jpg', '', '2026-03-29', '20:17:31'),
(237, 8, 'BIK', 'http://seduc.buk.cl/users/login', 'BIK-20260407095549.png', '', '2026-04-07', '09:54:55'),
(238, 8, 'webnia', 'https://www.webnia.cl/', 'webnia-20260407104303.png', '', '2026-04-07', '10:31:18'),
(239, 8, 'sistema finanzas graduaciion', 'https://qa.seduc.cl/jorquera/sistema_finanzas_graduacion/login.php', 'sistema-finanzas-graduaciion-20260408145119.jpg', '', '2026-04-08', '14:35:19'),
(240, 1, 'Metro Gas', 'https://sucursalvirtual.metrogas.cl/cuentas/resumen', 'Metro-Gas-20260412190421.png', '', '2026-04-12', '19:04:21'),
(242, 1, 'Notebook', 'https://notebooklm.google.com/?icid=home_maincta&_gl=1*1s8ntgz*_ga*OTQyMzYwNDY0LjE3NzYxMjIwNjM.*_ga_W0LDH41ZCB*czE3NzYxMjIwNjMkbzEkZzEkdDE3NzYxMjIxMTMkajEwJGwwJGgw', 'usuarios_contenedores/usuario_1/mantenedor_php-20260515094014.png', '', '2026-04-13', '19:19:40'),
(243, 1, 'COLEGIÓ SAN PABLO ORIGINAL', 'https://sanpablo.edu.uy/', '', '', '2026-04-14', '18:17:52'),
(245, 1, 'claude', 'https://claude.ai/new', 'claude-20260425192602.png', '', '2026-04-25', '19:25:14'),
(246, 1, 'Sistema Mariachi', 'https://qa.seduc.cl/jorquera/sistema_mariachi/', '', '', '2026-04-28', '17:52:14'),
(247, 1, 'PAGO TAG', 'https://botonpago.unired.cl/Pago/IniciarFlujoOnline?u=8O5NVE5BEq0YCkr2hmB9Lm6DtZnzm8lPKQrdqVKbAbU%3D', 'PAGO-TAG-20260429101037.jpg', '', '2026-04-29', '10:10:37'),
(248, 1, 'google colab', 'https://colab.research.google.com/drive/1oGA4f0zR_7L6fgK_Vh8BKWASZcLY1g7n#scrollTo=1btKzFp_kB0-', '', '', '2026-04-29', '20:07:24'),
(255, 1, 'Disney', 'https://www.disneyplus.com/es-419/play/53607923-8a63-488e-8c5d-2486a140359a', 'usuarios_contenedores/usuario_1/disney-20260515193146.jpg', '', '2026-05-15', '19:31:46'),
(257, 1, 'venta artículos escolares', 'articulosescolares.webnia.cl', 'usuarios_contenedores/usuario_1/venta-articulos-escolares-20260518175234.png', '', '2026-05-18', '11:49:55'),
(258, 1, 'Big -Data', 'https://www.skills.google/', 'usuarios_contenedores/usuario_1/bigdata-20260518192310.jpg', '', '2026-05-18', '19:17:06'),
(260, 1, 'nocedal -ramon', 'https://190.114.253.146:2083/logout/?locale=es', 'usuarios_contenedores/usuario_1/nocedal-20260527140853.jpg', '', '2026-05-27', '14:08:20'),
(261, 1, 'SISTEMA PERSONAL', 'https://qa.seduc.cl/jorquera/sistema_personal_8/', 'usuarios_contenedores/usuario_1/sistema_personal-20260603124611.png', '', '2026-06-03', '12:44:56'),
(262, 1, 'CONECTIA', 'http://conectaria.webnia.cl/', '', '', '2026-06-28', '19:14:43'),
(265, 8, 'JIRA', 'https://duoc-uc-capstone-grupo-2.atlassian.net/jira/software/projects/SCRUM/boards/1?filter=assignee%20%3D%205fe413bc9edf28007510b770&groupBy=none', 'imagenes/usuarios_contenedores/usuario_8/contenedor_4f5b74ce58b058684d7f8ca1.jpg', '', NULL, NULL),
(266, 8, 'sistema base antiguo', 'https://qa.seduc.cl/jorquera/sistema_panel-qa/version2/login.php', NULL, '', NULL, NULL),
(267, 8, 'COLEGIO NOCEDAL', 'https://nocedal.webnia.cl/public/login', 'imagenes/usuarios_contenedores/usuario_8/contenedor_f6a0dadd3ed15bde7942800c.jpg', '', NULL, NULL),
(268, 8, 'PLESK-AMAZON', 'https://plesk.sanpablo.uy:8443/login_up.php', 'imagenes/usuarios_contenedores/usuario_8/contenedor_c825de2b7742f0fda1dcb1e8.png', '', NULL, NULL),
(269, 8, 'AGUA-PAGAR', 'https://www.aguasandinas.cl/web/aguasandinas/login', 'imagenes/usuarios_contenedores/usuario_8/contenedor_e2d7133cffe2320e734e229e.jpg', '', NULL, NULL),
(270, 8, 'MOVISTAR-PAGAR', 'https://pagos.movistar.cl/publico', 'imagenes/usuarios_contenedores/usuario_8/contenedor_093d31950c605d053c0ccbb2.jpg', '', NULL, NULL),
(271, 8, 'ENEL-PAGAR', 'https://www.enel.cl/es/private-area/result-pago.html?transaction_id=1790774266201430341306&transaction_status=OK&installments=&authorization_id=&identification_id=86899707&bt=&cn=&pt=&url_comprobante=https://rdd.enel.com/btnRDD/webservices/comprobante', 'imagenes/usuarios_contenedores/usuario_8/contenedor_283d6ef388cff640dac23d60.jpg', '', NULL, NULL),
(272, 42, 'GOOGLE', 'https://www.google.com/', 'imagenes/usuarios_contenedores/usuario_42/contenedor_67fa2aeddf2024b631b0e4e7.png', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int(11) NOT NULL,
  `nombre_departamento` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sigla` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8_unicode_ci,
  `estado` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id_departamento`, `nombre_departamento`, `sigla`, `descripcion`, `estado`) VALUES
(1, 'INFORMATICA', 'info', 'Departamento de Informática', 1),
(2, 'CONTABILIDAD', 'cont', 'Departamento de Contabilidad', 1),
(3, 'RECURSOS HUMANOS', 'rrhh', 'Departamento de Recursos Humanos', 1),
(4, 'PROFESORAS', 'prof', 'Departamento de Profesoras', 1),
(5, 'PREVENCION', 'prev', 'Departamento de Prevención', 1),
(6, 'VISITAS', 'vis', 'Departamento de Visitas', 1),
(7, 'GERENCIA', 'ger', 'Departamento de Gerencia', 1),
(8, 'FINANZAS', 'fin', 'Departamento de Finanzas', 1),
(9, 'PERIODISTAS', 'per', 'Departamento de Periodistas', 1),
(10, 'MATEMATICAS', 'mat', 'Departamento de Matemáticas', 1),
(11, 'INGLES', 'ing', 'Departamento de Inglés', 1),
(12, 'ENFERMERIA', 'enf', 'Departamento de Enfermería', 1),
(13, 'DUEÑA DE CASA', 'duc', 'Departamento Dueña de Casa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos_colegio`
--

CREATE TABLE `departamentos_colegio` (
  `id` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `nombre_departamento` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sigla` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `departamentos_colegio`
--

INSERT INTO `departamentos_colegio` (`id`, `id_colegio`, `nombre_departamento`, `sigla`, `estado`) VALUES
(1, 15, 'INFORMATICA', 'info', 1),
(2, 15, 'CONTABILIDAD', 'cont', 1),
(3, 15, 'RECURSOS HUMANOS', 'rrhh', 1),
(4, 15, 'FINANZAS', 'fin', 1),
(5, 15, 'GERENCIA', 'ger', 1),
(6, 1, 'INFORMATICA', 'info', 1),
(7, 1, 'MATEMATICAS', 'mat', 1),
(8, 1, 'LENGUAJE', 'len', 1),
(9, 1, 'CIENCIAS', 'cien', 1),
(10, 9, 'INFORMATICA', 'info', 1),
(11, 9, 'PSICOLOGIA', 'psic', 1),
(12, 15, 'PEDAGOGIA', 'PED', 1),
(13, 15, 'PERIODISMO', 'PER', 1);

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
  `nombre_personalizado` varchar(150) DEFAULT NULL,
  `fabricante` varchar(255) DEFAULT NULL,
  `producto` varchar(255) DEFAULT NULL,
  `numero_serie` varchar(255) DEFAULT NULL,
  `tipo_pc` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT '1',
  `eliminado` tinyint(4) NOT NULL DEFAULT '0',
  `fecha_eliminado` datetime DEFAULT NULL,
  `id_usuario_elimina` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos_compra`
--

CREATE TABLE `equipos_compra` (
  `id_equipo` int(11) NOT NULL,
  `valor_equipo` int(10) DEFAULT NULL,
  `proveedor` varchar(255) DEFAULT NULL,
  `numero_factura` varchar(255) DEFAULT NULL,
  `fecha_compra` date DEFAULT '0000-00-00',
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_almacenamiento`
--

CREATE TABLE `equipo_almacenamiento` (
  `id_equipo` int(11) NOT NULL,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_capacidad` varchar(50) DEFAULT NULL,
  `equipo_tamano` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_asignacion_historial`
--

CREATE TABLE `equipo_asignacion_historial` (
  `id_asignacion` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_usuario_anterior` int(11) DEFAULT NULL,
  `id_usuario_nuevo` int(11) DEFAULT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_estado_historial`
--

CREATE TABLE `equipo_estado_historial` (
  `id_historial` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_estado_anterior` int(11) DEFAULT NULL,
  `id_estado_nuevo` int(11) NOT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observacion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_fotos`
--

CREATE TABLE `equipo_fotos` (
  `id_foto` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `tipo_foto` enum('normal','otro') NOT NULL DEFAULT 'normal',
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `orden_foto` int(11) NOT NULL DEFAULT '1',
  `fecha_subida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_memoria`
--

CREATE TABLE `equipo_memoria` (
  `id_memoria` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `designacion_memoria` varchar(255) DEFAULT NULL,
  `formato_memoria` varchar(255) DEFAULT NULL,
  `tipo_memoria` varchar(255) DEFAULT NULL,
  `tamano_memoria` int(11) DEFAULT NULL,
  `frecuencia_memoria` int(11) DEFAULT NULL,
  `marca_memoria` varchar(255) DEFAULT NULL,
  `orden_memoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_monitor`
--

CREATE TABLE `equipo_monitor` (
  `id_monitor` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `modelo_monitor` varchar(255) DEFAULT NULL,
  `codigo_monitor` varchar(255) DEFAULT NULL,
  `serie_monitor` varchar(11) DEFAULT NULL,
  `tamano_monitor` int(10) DEFAULT NULL,
  `resolucion_monitor` varchar(50) DEFAULT NULL,
  `orden_monitor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_movimiento`
--

CREATE TABLE `equipo_movimiento` (
  `id_movimiento` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_ubicacion_origen` int(11) DEFAULT NULL,
  `id_ubicacion_destino` int(11) NOT NULL,
  `id_usuario_movimiento` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_procesador`
--

CREATE TABLE `equipo_procesador` (
  `id_equipo` int(11) NOT NULL,
  `equipo_fabricante` varchar(100) DEFAULT NULL,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_velocidad` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_software`
--

CREATE TABLE `equipo_software` (
  `id_equipo` int(11) NOT NULL,
  `windows` varchar(50) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `antivirus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `estado` tinyint(4) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_ticket`
--

CREATE TABLE `estados_ticket` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `color_degradado` varchar(100) DEFAULT NULL,
  `descripcion_estado` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `estados_ticket`
--

INSERT INTO `estados_ticket` (`id`, `nombre`, `orden`, `color`, `color_degradado`, `descripcion_estado`) VALUES
(1, 'Recibido', 1, '#E6E6FA', 'linear-gradient(135deg, #E6E6FA, #FFFFFF)', 'El ticket ha sido creado, pero aun no ha sido asignado a un tecnico para su atencion.\n'),
(2, 'Asignado', 2, '#FFF4C1', 'linear-gradient(135deg, #FFF4C1, #FFFFE0)', 'El ticket ya fue asignado a un tecnico y esta a la espera de ser atendido.\n'),
(3, 'En proceso', 3, '#FFE0E0', 'linear-gradient(135deg, #FFE0E0, #FFF0F0)', 'El tecnico esta trabajando activamente en la solucion del ticket.\n'),
(5, 'Terminado', 4, '#C8F7C5', 'linear-gradient(135deg, #D8F8D8, #F0FFF0)', 'El tecnico ha finalizado su trabajo. El ticket esta pendiente de revision o cierre.\n'),
(4, 'Borrador', 4, '#F3E5F5', 'linear-gradient(135deg, #F3E5F5, #FFFFFF)', 'El ticket ha sido creado pero aun no ha sido activado. Esta en etapa de preparacion y no sera visibl'),
(6, 'Cerrado', 6, '#E0E0E0', 'linear-gradient(135deg, #E0E0E0, #F7F7F7)', 'El ticket fue resuelto y cerrado definitivamente por el sistema o un administrador.\n'),
(7, 'Demorado', 7, '#FFD6A5', 'linear-gradient(135deg, #FFD6A5, #FFF0E0)', 'El ticket esta atrasado respecto a su fecha limite de resolucion. Se requiere atencion urgente.\n\n');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_equipo`
--

CREATE TABLE `estado_equipo` (
  `id_estado` int(11) NOT NULL,
  `nombre_estado` varchar(100) NOT NULL,
  `color_badge` varchar(50) NOT NULL,
  `estado` tinyint(4) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `estado_equipo`
--

INSERT INTO `estado_equipo` (`id_estado`, `nombre_estado`, `color_badge`, `estado`) VALUES
(1, 'Activo', 'success', 1),
(2, 'Bodega', 'secondary', 1),
(3, 'Reparación', 'warning', 1),
(4, 'Baja', 'danger', 1),
(5, 'Prestado', 'info', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_herramienta`
--

CREATE TABLE `estado_herramienta` (
  `id_estado` int(11) NOT NULL,
  `nombre_estado` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_badge` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'secondary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estado_herramienta`
--

INSERT INTO `estado_herramienta` (`id_estado`, `nombre_estado`, `color_badge`) VALUES
(1, 'Disponible', 'success'),
(2, 'En bodega', 'secondary'),
(3, 'En reparacion', 'warning'),
(4, 'Baja', 'danger'),
(5, 'Prestada', 'info'),
(6, 'Extraviada', 'dark');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `hora_evento` time NOT NULL,
  `con_audio` tinyint(1) DEFAULT '0',
  `solo_presentacion` tinyint(1) DEFAULT '0',
  `musica_ambiental` tinyint(1) DEFAULT '0',
  `cantidad_personas` int(11) NOT NULL,
  `ubicacion` varchar(150) DEFAULT NULL,
  `tipo_evento` varchar(50) NOT NULL DEFAULT 'reunion',
  `estado` varchar(30) NOT NULL DEFAULT 'programado',
  `color_evento` varchar(20) DEFAULT NULL,
  `observaciones_logisticas` text,
  `correo_enviado` tinyint(1) NOT NULL DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL,
  `responsable_id` int(11) NOT NULL,
  `eliminado` varchar(2) NOT NULL,
  `id_colegio` int(11) DEFAULT NULL COMMENT 'ID del colegio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `hora_evento`, `con_audio`, `solo_presentacion`, `musica_ambiental`, `cantidad_personas`, `ubicacion`, `tipo_evento`, `estado`, `color_evento`, `observaciones_logisticas`, `correo_enviado`, `creado_en`, `actualizado_en`, `responsable_id`, `eliminado`, `id_colegio`) VALUES
(12, 'Reunionwwww', 'Coordinacion mensual con todo el equipo.', '2025-08-01', '2025-08-01', '10:00:00', NULL, '10:00:00', 1, 0, 1, 15, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 1, 'sÃ', 11),
(13, 'Capacitacion interna', 'Formacion sobre ciberseguridad.', '2025-08-02', '2025-08-02', '15:30:00', NULL, '15:30:00', 1, 1, 0, 12, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 2, 'sÃ', 10),
(14, 'Charla externa', 'Charla con invitado internacional.', '2025-07-29', '2025-07-29', '11:00:00', NULL, '11:00:00', 1, 1, 1, 30, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 3, 'sÃ', 11),
(15, 'Cumpleanos del jefe', 'Celebracion informal.', '2025-08-04', '2025-08-04', '13:00:00', NULL, '13:00:00', 0, 0, 1, 20, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 4, 'sÃ', 13),
(16, 'Evaluacion de proyectos', 'Revision del estado de avance.', '2025-08-05', '2025-08-05', '09:00:00', NULL, '09:00:00', 1, 1, 1, 10, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 1, 'no', 14),
(17, 'Taller de innovacion', 'Ideas para nuevos procesos.', '2025-08-06', '2025-08-06', '14:00:00', NULL, '14:00:00', 1, 1, 1, 25, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 2, 'no', 22),
(18, 'Reunion Informatica', 'reuniÃ³n para ver avances en el departamento', '2025-08-07', '2025-08-07', '16:30:00', NULL, '16:30:00', 1, 1, 0, 8, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 3, 'no', 22),
(19, 'Prueba de sonido', 'Configuracion para evento grande.', '2025-08-08', '2025-08-08', '08:00:00', NULL, '08:00:00', 1, 0, 1, 5, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 4, 'sÃ', 1),
(20, 'Almuerzo Despedida', 'Despedida de Carola Barros', '2025-07-29', '2025-07-29', '13:32:00', NULL, '13:32:00', 0, 0, 0, 10, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 1, 'sÃ', 15),
(21, 'Actividad recreativa', 'Dinamica grupal para el equipo.', '2025-08-10', '2025-08-10', '17:00:00', NULL, '17:00:00', 0, 0, 1, 18, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 19:07:15', NULL, 2, 'no', 12),
(22, 'pppppppppppp', 'qwqwqw', '2025-08-12', '2025-08-12', '20:59:00', NULL, '20:59:00', 1, 1, 1, 22, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-28 20:56:06', NULL, 8, 'no', 1),
(23, 'guadalupe Jorquera', 'guadalupe Jorquera', '2025-07-29', '2025-07-29', '13:24:00', NULL, '13:24:00', 1, 1, 1, 22, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-29 15:23:25', NULL, 8, 'sÃ', 11),
(24, 'agusto', 'agusto', '2025-07-30', '2025-07-30', '15:26:00', NULL, '15:26:00', 0, 0, 0, 22, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-29 15:24:09', NULL, 8, 'no', 15),
(25, '34534534', '34535345', '2025-07-09', '2025-07-09', '14:31:00', NULL, '14:31:00', 1, 1, 1, 333, NULL, 'reunion', 'programado', NULL, NULL, 0, '2025-07-29 15:29:54', NULL, 8, 'sÃ', 15),
(26, 'EVENTO DE PRUEBA', 'qweqwe', '2026-03-16', '2026-03-16', '17:33:00', '18:37:00', '17:33:00', 1, 1, 1, 23, 'www', 'capacitacion', 'cancelado', '#059669', '323432', 1, '2026-03-15 16:33:43', '2026-03-15 22:05:19', 8, '0', 1),
(27, 'EVENTO DE PRUEBA', 'qweqwe', '2026-03-16', '2026-03-16', '17:33:00', '18:37:00', '17:33:00', 1, 1, 1, 23, 'www', 'capacitacion', 'programado', '#ebebeb', '323432', 1, '2026-03-15 16:33:50', '2026-03-15 13:39:35', 8, '0', 12),
(28, 'qweqwe', 'qweqwe', '2026-03-17', '2026-03-17', '13:55:00', '17:55:00', '13:55:00', 1, 1, 1, 0, '', 'reunion', 'cancelado', '#e7eb24', 'qweqwe', 1, '2026-03-15 16:55:47', '2026-03-15 22:17:33', 8, '0', 14),
(29, 'prueba', 'evento d eprueba', '2026-05-24', '2026-05-24', '08:33:00', '18:34:00', '08:33:00', 0, 0, 0, 123, '', 'capacitacion', 'programado', '#940505', '', 1, '2026-05-11 16:34:21', '2026-05-11 12:34:33', 8, '0', 1),
(30, 'EVENTO DE PRUEBA', '123213', '2026-05-29', '2026-05-29', '12:40:00', '12:41:00', '12:40:00', 0, 0, 0, 0, '', 'reunion', 'programado', '#24ebc3', '213123', 1, '2026-05-11 16:35:51', '2026-05-11 12:36:16', 8, '0', 10),
(31, 'EVENTO DE PRUEBA', '123', '2026-05-11', '2026-05-11', '14:38:00', '16:40:00', '14:38:00', 1, 1, 1, 22, '', 'celebracion', 'programado', '#d97706', '', 1, '2026-05-11 16:36:55', '2026-05-11 12:36:57', 8, '0', 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramientas`
--

CREATE TABLE `herramientas` (
  `id_herramienta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `nombre_herramienta` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marca` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `modelo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `numero_serie` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_estado` int(11) NOT NULL DEFAULT '1',
  `cantidad` int(11) NOT NULL DEFAULT '1',
  `stock_minimo` int(11) NOT NULL DEFAULT '0',
  `ubicacion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_compra`
--

CREATE TABLE `herramienta_compra` (
  `id_compra` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `valor_herramienta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `proveedor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `numero_factura` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `fecha_compra` date DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_detalle`
--

CREATE TABLE `herramienta_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `tipo_energia` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `medida` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `capacidad` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `requiere_mantencion` tinyint(1) NOT NULL DEFAULT '0',
  `frecuencia_mantencion_dias` int(11) NOT NULL DEFAULT '0',
  `fecha_ultima_mantencion` date DEFAULT NULL,
  `fecha_proxima_mantencion` date DEFAULT NULL,
  `garantia_hasta` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_fotos`
--

CREATE TABLE `herramienta_fotos` (
  `id_foto` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `ruta_foto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `orden_foto` int(11) NOT NULL DEFAULT '1',
  `fecha_subida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_historial`
--

CREATE TABLE `herramienta_historial` (
  `id_historial` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `accion` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_mantenciones`
--

CREATE TABLE `herramienta_mantenciones` (
  `id_mantencion` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `fecha_mantencion` date DEFAULT NULL,
  `tipo_mantencion` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `proveedor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `costo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `proxima_fecha` date DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_prestamos`
--

CREATE TABLE `herramienta_prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `responsable_entrega` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `responsable_recibe` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `fecha_salida` date DEFAULT NULL,
  `fecha_devolucion_prevista` date DEFAULT NULL,
  `fecha_devolucion_real` date DEFAULT NULL,
  `estado_prestamo` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `observacion` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_software_datos_sensibles`
--

CREATE TABLE `inventario_software_datos_sensibles` (
  `id_dato_sensible` int(11) NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario_software_datos_sensibles`
--

INSERT INTO `inventario_software_datos_sensibles` (`id_dato_sensible`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'RUT', 'Identificador tributario o de identidad', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(2, 'Nombre', 'Nombre completo de personas', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(3, 'Nombre completo', 'Nombre y apellidos de una persona', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(4, 'Email', 'Correo electrónico personal o institucional', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(5, 'Teléfono', 'Número de contacto', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(6, 'Dirección', 'Dirección física o de despacho', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(7, 'Tarjeta de crédito', 'Números o información de pago', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(8, 'Curso', 'Curso o nivel del alumno', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(9, 'Calificaciones', 'Notas y evaluaciones', 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_software_tipo_usuario`
--

CREATE TABLE `inventario_software_tipo_usuario` (
  `id_tipo_usuario` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario_software_tipo_usuario`
--

INSERT INTO `inventario_software_tipo_usuario` (`id_tipo_usuario`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Colaborador', NULL, 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(2, 'Alumno', NULL, 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(3, 'Apoderado', NULL, 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(4, 'Proveedor', NULL, 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16'),
(5, 'Otro', NULL, 1, '2026-11-06 15:38:16', '2026-11-06 15:38:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jefatura_departamento`
--

CREATE TABLE `jefatura_departamento` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_departamento_colegio` int(11) DEFAULT NULL,
  `id_colegio` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` int(11) DEFAULT '1',
  `tipo_jefatura` enum('Admin_Colegio','Admin_Departamento') COLLATE utf8_unicode_ci DEFAULT 'Admin_Departamento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `jefatura_departamento`
--

INSERT INTO `jefatura_departamento` (`id`, `id_usuario`, `id_departamento_colegio`, `id_colegio`, `fecha_asignacion`, `estado`, `tipo_jefatura`) VALUES
(13, 2492, 1, 15, '2026-09-10 13:26:56', 1, 'Admin_Departamento'),
(15, 2497, 1, 10, '2026-09-10 13:30:35', 1, 'Admin_Departamento'),
(17, 2493, 10, 9, '2026-09-10 13:31:26', 1, 'Admin_Departamento'),
(18, 43, 5, 15, '2026-09-10 13:31:43', 1, 'Admin_Departamento'),
(19, 2495, 1, 8, '2026-09-10 13:32:01', 1, 'Admin_Departamento'),
(22, 39, 2, 15, '2026-09-10 13:32:44', 1, 'Admin_Departamento'),
(24, 9, 5, 15, '2026-09-10 13:35:05', 1, 'Admin_Departamento'),
(25, 14, 5, 15, '2026-09-10 13:35:17', 1, 'Admin_Departamento'),
(26, 16, 3, 15, '2026-09-10 13:35:36', 1, 'Admin_Departamento'),
(27, 31, 6, 1, '2026-09-10 13:36:10', 1, 'Admin_Departamento'),
(28, 26, 4, 15, '2026-09-10 13:37:00', 1, 'Admin_Departamento'),
(29, 49, 10, 9, '2026-09-10 13:37:40', 1, 'Admin_Departamento'),
(31, 46, 1, 11, '2026-09-10 13:38:38', 1, 'Admin_Departamento'),
(32, 20, 4, 15, '2026-09-10 13:38:54', 1, 'Admin_Departamento'),
(33, 7, 1, 15, '2026-09-10 13:39:27', 1, 'Admin_Departamento'),
(34, 10, 13, 15, '2026-09-10 13:39:46', 1, 'Admin_Departamento'),
(35, 33, 1, 13, '2026-09-10 13:40:00', 1, 'Admin_Departamento'),
(36, 2488, 8, 1, '2026-09-10 13:40:27', 1, 'Admin_Departamento'),
(37, 24, 4, 15, '2026-09-10 13:41:20', 1, 'Admin_Departamento'),
(38, 48, 6, 1, '2026-09-10 13:41:39', 1, 'Admin_Departamento'),
(39, 18, 12, 15, '2026-09-10 13:42:32', 1, 'Admin_Departamento'),
(40, 8, 1, 15, '2026-09-10 13:42:47', 1, 'Admin_Departamento'),
(41, 21, 4, 15, '2026-09-10 13:45:11', 1, 'Admin_Departamento'),
(42, 6, 1, 15, '2026-09-10 13:46:30', 1, 'Admin_Departamento'),
(43, 12, 3, 15, '2026-09-10 13:46:37', 1, 'Admin_Departamento'),
(44, 4, 2, 15, '2026-09-10 13:46:42', 1, 'Admin_Departamento'),
(45, 41, 12, 15, '2026-09-10 13:46:48', 1, 'Admin_Departamento'),
(46, 25, 4, 15, '2026-09-10 13:46:54', 1, 'Admin_Departamento'),
(47, 1, 4, 15, '2026-09-10 13:47:01', 1, 'Admin_Departamento'),
(48, 42, 1, 15, '2026-09-10 13:47:06', 1, 'Admin_Departamento'),
(49, 3, 2, 15, '2026-09-10 13:47:12', 1, 'Admin_Departamento'),
(50, 15, 5, 15, '2026-09-10 13:47:17', 1, 'Admin_Departamento'),
(52, 35, 13, 15, '2026-09-10 13:47:30', 1, 'Admin_Departamento'),
(53, 17, 3, 15, '2026-09-10 13:47:44', 1, 'Admin_Departamento'),
(54, 2, 2, 15, '2026-09-10 13:47:53', 1, 'Admin_Departamento'),
(55, 5, 2, 15, '2026-09-10 13:48:00', 1, 'Admin_Departamento'),
(56, 2496, 1, 10, '2026-09-10 13:48:22', 1, 'Admin_Departamento'),
(57, 31, NULL, 1, '2026-09-21 09:07:39', 1, 'Admin_Colegio'),
(61, 44, 12, 15, '2026-09-21 14:42:19', 1, 'Admin_Departamento'),
(62, 45, NULL, 8, '2026-09-22 17:48:59', 1, 'Admin_Colegio'),
(63, 2476, NULL, 9, '2026-09-22 17:50:01', 1, 'Admin_Colegio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_chat`
--

CREATE TABLE `mensajes_chat` (
  `id` int(11) NOT NULL,
  `id_padre` int(11) DEFAULT NULL,
  `id_conversacion` int(11) NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `de` int(11) NOT NULL,
  `para` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) DEFAULT '0',
  `urgente` tinyint(1) DEFAULT '0',
  `eliminado` tinyint(1) DEFAULT '0',
  `prioridad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_1`
--

CREATE TABLE `menu_1` (
  `id_menu` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `abreviacion` varchar(50) DEFAULT NULL,
  `archivo` varchar(50) DEFAULT NULL,
  `icono` varchar(100) NOT NULL,
  `caracteristica` text NOT NULL,
  `orden` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `menu_1`
--

INSERT INTO `menu_1` (`id_menu`, `nombre`, `abreviacion`, `archivo`, `icono`, `caracteristica`, `orden`) VALUES
(1, 'Colaboradores', 'col', 'colaboradores.php', '<i class=\"bi bi-people-fill\" style=\"color:black;\"></i>', 'Administra y consulta la información de los colaboradores del sistema, facilitando su organización y seguimiento.', '2'),
(2, 'Mensajes', 'men', 'mensaje.php', '<i class=\"bi bi-chat-dots-fill\" style=\"color:black;\"></i>', 'Gestiona la comunicación interna con tus colaboradores mediante envío y revisión de mensajes dentro del sistema.', '3'),
(7, 'Configuracion', 'conf', 'configuracion/permisos.php', '<i class=\"bi bi-gear-fill\" style=\"color:black;\"></i>', 'Configura permisos, accesos, parámetros generales y opciones del sistema según el perfil de cada usuario.', '7'),
(8, 'INICIO', 'index', 'dashboard.php', '<i class=\"bi bi-house-door-fill\" style=\"color:black;\"></i>', 'Panel principal del sistema donde puedes ver accesos rápidos, métricas, gráficos y un resumen general de la plataforma.', '1'),
(9, 'Inventario', 'bita', 'inventario', '<i class=\"bi bi-pc-display-horizontal\" style=\"color:black;\"></i>', 'Registra, controla y da seguimiento al inventario de equipos, dispositivos y recursos tecnológicos disponibles.', '8'),
(10, 'Generador de Qr ', 'GQ-T', 'generadorQr.php', '<i class=\"bi bi-qr-code-scan\" style=\"color:black;\"></i>', 'Genera códigos QR para identificar recursos, equipos o accesos de forma rápida y facilitar su control dentro del sistema.', '9'),
(11, 'Estadistica', 'est', 'Estadistica', '<i class=\"bi bi-bar-chart-line-fill\" style=\"color:black;\"></i>', 'Consulta reportes, indicadores y estadísticas del sistema para apoyar el análisis y la toma de decisiones.', '10'),
(12, 'Acoso Laboral', 'al', 'denunciaAcoso.php', '<i class=\"bi bi-exclamation-octagon-fill\" style=\"color:black;\"></i>', 'Módulo destinado al registro, seguimiento y gestión de denuncias o situaciones relacionadas con acoso laboral.', '11'),
(13, 'Eventos', 'calen', 'eventos/index.php', '<i class=\"bi bi-calendar-event-fill\" style=\"color:black;\"></i>', 'Permite registrar, organizar y visualizar eventos, actividades importantes, reuniones o fechas relevantes del sistema.', '12'),
(15, 'Licenciamientos', 'licen', 'licenciamiento/index.php', '<i class=\"bi bi-shield-check\" style=\"color:black;\"></i>', 'Módulo de gestión de licencias de software que permite registrar, controlar vencimientos, responsables, proveedores y estado de cada licencia.', '13'),
(16, 'Administrar Colegios', 'adm_col', 'configuracion/mantenedor_colegios.php', '<i class=\"bi bi-building\" style=\"color:black;\"></i>', 'Administra y gestiona los colegios del sistema', '14'),
(17, 'Contenedores', 'cont', 'contenedores.php', '<i class=\"bi bi-grid-fill\" style=\"color:black;\"></i>', 'Accesos directos y enlaces rápidos a sistemas externos', '15'),
(18, 'Ticket', 'tick', 'ticket.php', '<i class=\"bi bi-ticket-perforated-fill\" style=\"color:black;\"></i>', 'Gestión de tickets del sistema', '4'),
(19, 'Almuerzos', NULL, 'almuerzos/index.php', '<i class=\"bi bi-cup-hot\"></i>', 'Almuerzos', '16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_1_sub`
--

CREATE TABLE `menu_1_sub` (
  `id_submenu` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `menu_1_sub`
--

INSERT INTO `menu_1_sub` (`id_submenu`, `id_menu`, `nombre`, `archivo`, `icono`, `orden`) VALUES
(2, 2, 'Recibidos', 'mensaje.php', '<i class=\"bi bi-inbox-fill\" style=\"color:black;\"></i>', 2),
(4, 7, 'Perrmisos', 'permisos.php', '<i class=\"bi bi-shield-lock-fill\" style=\"color:black;\"></i>', 3),
(8, 9, 'Inventario Aseo', 'inventario_aseo/index.php', '<i class=\"bi bi-droplet-fill\" style=\"color:black;\"></i>', 3),
(9, 9, 'Inventario Herramientas', 'inventario_herramintas/index.php', '<i class=\"bi bi-tools\" style=\"color:black;\"></i>', 2),
(10, 9, 'Inventario PC', 'inventario/index.php', '<i class=\"bi bi-pc-display-horizontal\" style=\"color:black;\"></i>', 1),
(11, 11, 'Vida del Ticket', 'estadistica/vida_ticket.php', '<i class=\"bi bi-diagram-3-fill\" style=\"color:black;\"></i>', 1),
(12, 11, 'Estadística General', 'estadistica/index.php', '<i class=\"bi bi-graph-up-arrow\" style=\"color:black;\"></i>', 2),
(13, 7, 'Listados Usuarios', 'configuracion/listado_usuarios.php', '<i class=\"bi bi-people-fill\" style=\"color:black;\"></i>', 0),
(14, 7, 'Admin categorías', 'configuracion/admin_categorias.php', '<i class=\"bi bi-tags-fill\" style=\"color:black;\"></i>', 0),
(15, 7, 'Mantenimiento de Ticket', 'configuracion/admin_ticket.php', '<i class=\"bi bi-tools\" style=\"color:black;\"></i>', 0),
(16, 7, 'Eliminar Ticket', 'configuracion/mantenimiento_ticket_eliminar.php', '<i class=\"bi bi-trash3-fill\" style=\"color:black;\"></i>', 0),
(17, 7, 'Recuperar Ticket', 'configuracion/mantenimiento_ticket_recuperar.php', '<i class=\"bi bi-arrow-counterclockwise\" style=\"color:black;\"></i>', 0),
(18, 7, 'Fisionar Ticket', 'configuracion/mantenimiento_ticket_fusion.php', '<i class=\"bi bi-intersect\" style=\"color:black;\"></i>', 0),
(20, 15, 'Sofware', 'licenciamiento/sofware_licenciamiento.php', '<i class=\"bi bi-pc-display\" style=\"color:black;\"></i>', 0),
(21, 15, 'Sitios web  App', 'licenciamiento/sitios_web_apps.php', '<i class=\"bi bi-globe2\" style=\"color:black;\"></i>', 0),
(22, 11, 'Estadística General version 2', 'estadistica/probando.php', '<i class=\"bi bi-graph-up-arrow\" style=\"color:black;\"></i>', 3),
(23, 11, 'Dashboard Inventario PC', 'inventario/dashboard.php', '<i class=\"bi bi-diagram-3-fill\" style=\"color:black;\"></i>', 4),
(24, 18, 'Ticket Nuevo', 'ticket/ticket.php', '<i class=\"bi bi-plus-circle-fill\" style=\"color:black;\"></i>', 1),
(25, 18, 'Ticket Administrador', 'ticket/ticket_admin_v2.php', '<i class=\"bi bi-clipboard-data-fill\" style=\"color:black;\"></i>', 2),
(26, 18, 'Ticket Asignados', 'ticket/ticket_asignados.php', '<i class=\"bi bi-person-workspace\" style=\"color:black;\"></i>', 3);

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
  `id_estado` int(11) NOT NULL DEFAULT '1',
  `nombre_monitor` varchar(150) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(150) DEFAULT NULL,
  `numero_serie` varchar(150) DEFAULT NULL,
  `codigo_interno` varchar(100) DEFAULT NULL,
  `tamano_monitor` varchar(50) DEFAULT NULL,
  `resolucion_monitor` varchar(100) DEFAULT NULL,
  `tipo_panel` varchar(100) DEFAULT NULL,
  `tipo_conexion` varchar(150) DEFAULT NULL,
  `observacion` text,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitor_asignacion_historial`
--

CREATE TABLE `monitor_asignacion_historial` (
  `id_asignacion` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL,
  `id_usuario_anterior` int(11) DEFAULT NULL,
  `id_usuario_nuevo` int(11) DEFAULT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitor_compra`
--

CREATE TABLE `monitor_compra` (
  `id_monitor` int(11) NOT NULL,
  `valor_monitor` int(11) DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `numero_factura` varchar(100) DEFAULT NULL,
  `fecha_compra` date DEFAULT NULL,
  `observacion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitor_fotos`
--

CREATE TABLE `monitor_fotos` (
  `id_foto` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `tipo_foto` enum('normal','panoramica') NOT NULL DEFAULT 'normal',
  `principal` tinyint(4) NOT NULL DEFAULT '0',
  `orden_foto` int(11) NOT NULL DEFAULT '0',
  `fecha_subida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles`
--

CREATE TABLE `perfiles` (
  `id_perfil` int(11) NOT NULL,
  `nombre_perfil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` int(11) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `perfiles`
--

INSERT INTO `perfiles` (`id_perfil`, `nombre_perfil`, `descripcion`, `estado`) VALUES
(1, 'usuario', 'Usuario general del sistema. Puede crear y consultar tickets, registrar y gestionar inventarios y utilizar las funciones habilitadas según sus permisos y colegio asociado.', 1),
(2, 'tecnico', 'Personal del Departamento de Informática. Responsable de atender, gestionar y resolver tickets, además de realizar tareas técnicas y de soporte dentro del sistema.', 1),
(3, 'super_admin', 'Super administrador del sistema', 1),
(4, 'admin_colegio', 'Administrador de Colegio...', 1),
(5, 'admin_area', 'Administrador de Área...', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_menu_1`
--

CREATE TABLE `permisos_menu_1` (
  `id` int(11) NOT NULL,
  `id_menu1` int(11) DEFAULT NULL,
  `id_submenu` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_tipo_permiso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `permisos_menu_1`
--

INSERT INTO `permisos_menu_1` (`id`, `id_menu1`, `id_submenu`, `id_usuario`, `id_tipo_permiso`) VALUES
(1, 13, NULL, 1, 3),
(2, 12, NULL, 1, 3),
(3, 11, NULL, 1, 3),
(4, 10, NULL, 1, 3),
(5, 9, NULL, 1, 3),
(6, 8, NULL, 1, 1),
(7, 7, NULL, 1, 3),
(11, 2, NULL, 1, 1),
(12, 1, NULL, 1, 1),
(25, 13, NULL, 42, 1),
(26, 12, NULL, 42, 3),
(27, 11, NULL, 42, 1),
(28, 10, NULL, 42, 1),
(29, 9, NULL, 42, 1),
(30, 8, NULL, 42, 1),
(31, 7, NULL, 42, 1),
(35, 2, NULL, 42, 1),
(36, 1, NULL, 42, 1),
(49, 13, NULL, 15, 3),
(50, 12, NULL, 15, 3),
(51, 11, NULL, 15, 3),
(52, 10, NULL, 15, 3),
(53, 9, NULL, 15, 3),
(54, 8, NULL, 15, 1),
(55, 7, NULL, 15, 3),
(59, 2, NULL, 15, 1),
(60, 1, NULL, 15, 1),
(61, 13, NULL, 35, 1),
(62, 12, NULL, 35, 3),
(63, 11, NULL, 35, 3),
(64, 10, NULL, 35, 3),
(65, 9, NULL, 35, 3),
(66, 8, NULL, 35, 3),
(67, 7, NULL, 35, 3),
(71, 2, NULL, 35, 1),
(72, 1, NULL, 35, 1),
(85, 13, NULL, 8, 1),
(86, 12, NULL, 8, 3),
(87, 11, NULL, 8, 1),
(88, 10, NULL, 8, 1),
(89, 9, NULL, 8, 1),
(90, 8, NULL, 8, 1),
(91, 7, NULL, 8, 1),
(95, 2, NULL, 8, 1),
(96, 1, NULL, 8, 1),
(133, 13, NULL, 17, 3),
(134, 12, NULL, 17, 3),
(135, 11, NULL, 17, 3),
(136, 10, NULL, 17, 3),
(137, 9, NULL, 17, 3),
(138, 8, NULL, 17, 1),
(139, 7, NULL, 17, 3),
(143, 2, NULL, 17, 1),
(144, 1, NULL, 17, 1),
(193, 13, NULL, 39, 3),
(194, 12, NULL, 39, 3),
(195, 11, NULL, 39, 3),
(196, 10, NULL, 39, 3),
(197, 9, NULL, 39, 3),
(198, 8, NULL, 39, 1),
(199, 7, NULL, 39, 3),
(203, 2, NULL, 39, 1),
(204, 1, NULL, 39, 1),
(205, 13, NULL, 43, 1),
(206, 12, NULL, 43, 3),
(207, 11, NULL, 43, 1),
(208, 10, NULL, 43, 3),
(209, 9, NULL, 43, 3),
(210, 8, NULL, 43, 1),
(211, 7, NULL, 43, 3),
(215, 2, NULL, 43, 1),
(216, 1, NULL, 43, 1),
(217, 13, NULL, 14, 3),
(218, 12, NULL, 14, 3),
(219, 11, NULL, 14, 3),
(220, 10, NULL, 14, 3),
(221, 9, NULL, 14, 3),
(222, 8, NULL, 14, 1),
(223, 7, NULL, 14, 3),
(227, 2, NULL, 14, 1),
(228, 1, NULL, 14, 1),
(229, 13, NULL, 12, 3),
(230, 12, NULL, 12, 3),
(231, 11, NULL, 12, 3),
(232, 10, NULL, 12, 3),
(233, 9, NULL, 12, 3),
(234, 8, NULL, 12, 1),
(235, 7, NULL, 12, 3),
(239, 2, NULL, 12, 1),
(240, 1, NULL, 12, 1),
(241, 13, NULL, 16, 3),
(242, 12, NULL, 16, 3),
(243, 11, NULL, 16, 3),
(244, 10, NULL, 16, 3),
(245, 9, NULL, 16, 3),
(246, 8, NULL, 16, 1),
(247, 7, NULL, 16, 3),
(251, 2, NULL, 16, 1),
(252, 1, NULL, 16, 1),
(253, 13, NULL, 9, 3),
(254, 12, NULL, 9, 3),
(255, 11, NULL, 9, 3),
(256, 10, NULL, 9, 3),
(257, 9, NULL, 9, 3),
(258, 8, NULL, 9, 1),
(259, 7, NULL, 9, 3),
(263, 2, NULL, 9, 1),
(264, 1, NULL, 9, 1),
(289, 13, NULL, 26, 3),
(290, 12, NULL, 26, 3),
(291, 11, NULL, 26, 3),
(292, 10, NULL, 26, 3),
(293, 9, NULL, 26, 3),
(294, 8, NULL, 26, 1),
(295, 7, NULL, 26, 3),
(299, 2, NULL, 26, 1),
(300, 1, NULL, 26, 1),
(313, 13, NULL, 7, 1),
(314, 12, NULL, 7, 3),
(315, 11, NULL, 7, 1),
(316, 10, NULL, 7, 1),
(317, 9, NULL, 7, 1),
(318, 8, NULL, 7, 1),
(319, 7, NULL, 7, 1),
(323, 2, NULL, 7, 1),
(324, 1, NULL, 7, 1),
(325, 13, NULL, 20, 3),
(326, 12, NULL, 20, 3),
(327, 11, NULL, 20, 3),
(328, 10, NULL, 20, 3),
(329, 9, NULL, 20, 3),
(330, 8, NULL, 20, 1),
(331, 7, NULL, 20, 3),
(335, 2, NULL, 20, 1),
(336, 1, NULL, 20, 1),
(337, 13, NULL, 10, 3),
(338, 12, NULL, 10, 3),
(339, 11, NULL, 10, 3),
(340, 10, NULL, 10, 3),
(341, 9, NULL, 10, 3),
(342, 8, NULL, 10, 1),
(343, 7, NULL, 10, 3),
(347, 2, NULL, 10, 1),
(348, 1, NULL, 10, 1),
(349, 13, NULL, 24, 3),
(350, 12, NULL, 24, 3),
(351, 11, NULL, 24, 3),
(352, 10, NULL, 24, 3),
(353, 9, NULL, 24, 3),
(354, 8, NULL, 24, 1),
(355, 7, NULL, 24, 3),
(359, 2, NULL, 24, 1),
(360, 1, NULL, 24, 1),
(373, 13, NULL, 4, 3),
(374, 12, NULL, 4, 3),
(375, 11, NULL, 4, 3),
(376, 10, NULL, 4, 3),
(377, 9, NULL, 4, 3),
(378, 8, NULL, 4, 1),
(379, 7, NULL, 4, 3),
(383, 2, NULL, 4, 1),
(384, 1, NULL, 4, 1),
(385, 13, NULL, 6, 3),
(386, 12, NULL, 6, 3),
(387, 11, NULL, 6, 3),
(388, 10, NULL, 6, 3),
(389, 9, NULL, 6, 1),
(390, 8, NULL, 6, 1),
(391, 7, NULL, 6, 3),
(395, 2, NULL, 6, 1),
(396, 1, NULL, 6, 1),
(409, 13, NULL, 44, 3),
(410, 12, NULL, 44, 3),
(411, 11, NULL, 44, 3),
(412, 10, NULL, 44, 3),
(413, 9, NULL, 44, 3),
(414, 8, NULL, 44, 1),
(415, 7, NULL, 44, 3),
(419, 2, NULL, 44, 1),
(420, 1, NULL, 44, 1),
(421, 13, NULL, 41, 3),
(422, 12, NULL, 41, 3),
(423, 11, NULL, 41, 3),
(424, 10, NULL, 41, 3),
(425, 9, NULL, 41, 3),
(426, 8, NULL, 41, 1),
(427, 7, NULL, 41, 3),
(431, 2, NULL, 41, 1),
(432, 1, NULL, 41, 1),
(433, 13, NULL, 18, 3),
(434, 12, NULL, 18, 3),
(435, 11, NULL, 18, 3),
(436, 10, NULL, 18, 3),
(437, 9, NULL, 18, 3),
(438, 8, NULL, 18, 1),
(439, 7, NULL, 18, 3),
(443, 2, NULL, 18, 1),
(444, 1, NULL, 18, 1),
(445, 13, NULL, 5, 3),
(446, 12, NULL, 5, 3),
(447, 11, NULL, 5, 3),
(448, 10, NULL, 5, 3),
(449, 9, NULL, 5, 3),
(450, 8, NULL, 5, 1),
(451, 7, NULL, 5, 3),
(455, 2, NULL, 5, 1),
(456, 1, NULL, 5, 1),
(469, 13, NULL, 31, 3),
(470, 12, NULL, 31, 3),
(471, 11, NULL, 31, 3),
(472, 10, NULL, 31, 3),
(473, 9, NULL, 31, 3),
(474, 8, NULL, 31, 1),
(475, 7, NULL, 31, 3),
(479, 2, NULL, 31, 3),
(480, 1, NULL, 31, 3),
(493, 13, NULL, 33, 3),
(494, 12, NULL, 33, 3),
(495, 11, NULL, 33, 3),
(496, 10, NULL, 33, 3),
(497, 9, NULL, 33, 3),
(498, 8, NULL, 33, 1),
(499, 7, NULL, 33, 3),
(503, 2, NULL, 33, 3),
(504, 1, NULL, 33, 3),
(517, 13, NULL, 25, 3),
(518, 12, NULL, 25, 3),
(519, 11, NULL, 25, 3),
(520, 10, NULL, 25, 3),
(521, 9, NULL, 25, 3),
(522, 8, NULL, 25, 1),
(523, 7, NULL, 25, 3),
(527, 2, NULL, 25, 1),
(528, 1, NULL, 25, 1),
(601, 1, NULL, 45, 1),
(606, 7, NULL, 45, 3),
(607, 8, NULL, 45, 1),
(608, 9, NULL, 45, 1),
(609, 10, NULL, 45, 3),
(610, 11, NULL, 45, 3),
(611, 12, NULL, 45, 3),
(612, 13, NULL, 45, 3),
(614, 15, NULL, 45, 1),
(628, 1, NULL, 49, 3),
(629, 2, NULL, 49, 3),
(636, 7, NULL, 49, 3),
(637, 8, NULL, 49, 1),
(638, 9, NULL, 49, 1),
(639, 10, NULL, 49, 3),
(640, 11, NULL, 49, 3),
(641, 12, NULL, 49, 3),
(642, 1, NULL, 48, 3),
(643, 2, NULL, 48, 3),
(648, 7, NULL, 48, 3),
(649, 8, NULL, 48, 1),
(650, 9, NULL, 48, 3),
(651, 10, NULL, 48, 3),
(652, 11, NULL, 48, 3),
(653, 12, NULL, 48, 3),
(656, 1, NULL, 2476, 3),
(657, 2, NULL, 2476, 3),
(661, 7, NULL, 2476, 3),
(662, 8, NULL, 2476, 1),
(663, 9, NULL, 2476, 3),
(664, 10, NULL, 2476, 3),
(665, 11, NULL, 2476, 3),
(666, 12, NULL, 2476, 3),
(667, 13, NULL, 2476, 3),
(680, 15, NULL, 8, 1),
(681, 15, NULL, 6, 1),
(682, 15, NULL, 7, 1),
(683, 15, NULL, 42, 1),
(814, 1, NULL, 2488, 3),
(815, 2, NULL, 2488, 3),
(819, 7, NULL, 2488, 3),
(820, 8, NULL, 2488, 1),
(821, 9, NULL, 2488, 3),
(822, 10, NULL, 2488, 3),
(823, 11, NULL, 2488, 3),
(824, 12, NULL, 2488, 3),
(825, 13, NULL, 2488, 3),
(826, 15, NULL, 2488, 1),
(840, 15, NULL, 48, 1),
(841, 13, NULL, 48, 3),
(868, 1, NULL, 2492, 3),
(869, 2, NULL, 2492, 3),
(873, 7, NULL, 2492, 3),
(874, 8, NULL, 2492, 1),
(875, 9, NULL, 2492, 1),
(876, 10, NULL, 2492, 3),
(877, 11, NULL, 2492, 3),
(878, 12, NULL, 2492, 3),
(879, 13, NULL, 2492, 3),
(880, 15, NULL, 2492, 1),
(881, 1, NULL, 2493, 3),
(882, 2, NULL, 2493, 3),
(886, 7, NULL, 2493, 3),
(887, 8, NULL, 2493, 1),
(888, 9, NULL, 2493, 1),
(889, 10, NULL, 2493, 3),
(890, 11, NULL, 2493, 3),
(891, 12, NULL, 2493, 3),
(892, 13, NULL, 2493, 3),
(893, 15, NULL, 2493, 1),
(894, 15, NULL, 49, 1),
(895, 13, NULL, 49, 1),
(909, 1, NULL, 2495, 3),
(910, 2, NULL, 2495, 3),
(914, 7, NULL, 2495, 3),
(917, 10, NULL, 2495, 3),
(918, 11, NULL, 2495, 3),
(919, 12, NULL, 2495, 3),
(920, 13, NULL, 2495, 3),
(922, 1, NULL, 2496, 3),
(923, 2, NULL, 2496, 3),
(927, 7, NULL, 2496, 3),
(928, 8, NULL, 2496, 1),
(929, 9, NULL, 2496, 1),
(930, 10, NULL, 2496, 3),
(931, 11, NULL, 2496, 3),
(932, 12, NULL, 2496, 3),
(933, 13, NULL, 2496, 3),
(934, 15, NULL, 2496, 1),
(935, 1, NULL, 2497, 3),
(936, 2, NULL, 2497, 3),
(940, 7, NULL, 2497, 3),
(941, 8, NULL, 2497, 1),
(942, 9, NULL, 2497, 1),
(943, 10, NULL, 2497, 3),
(944, 11, NULL, 2497, 3),
(945, 12, NULL, 2497, 3),
(946, 13, NULL, 2497, 3),
(947, 15, NULL, 2497, 1),
(962, 17, NULL, 8, 1),
(965, 18, NULL, 8, 1),
(967, 8, NULL, 2495, 1),
(968, 18, NULL, 2495, 1),
(969, 7, NULL, 2495, 1),
(970, 9, NULL, 2495, 1),
(971, 13, NULL, 2495, 1),
(972, 15, NULL, 2495, 1),
(973, 17, NULL, 2495, 1),
(1122, 8, NULL, 3, 1),
(1123, 1, NULL, 3, 1),
(1124, 2, NULL, 3, 1),
(1125, 18, NULL, 3, 1),
(1126, 9, NULL, 3, 1),
(1127, 13, NULL, 3, 1),
(1128, 15, NULL, 3, 1),
(1129, 8, NULL, 2, 1),
(1130, 1, NULL, 2, 1),
(1131, 2, NULL, 2, 1),
(1132, 18, NULL, 2, 1),
(1133, 7, NULL, 2, 1),
(1134, 9, NULL, 2, 1),
(1135, 13, NULL, 2, 1),
(1136, 15, NULL, 2, 1),
(1137, 17, NULL, 2, 1),
(1138, 19, NULL, 6, 1),
(1139, 19, NULL, 7, 1),
(1140, 19, NULL, 8, 1),
(1141, 19, NULL, 42, 1),
(1142, 19, NULL, 49, 1),
(1143, 19, NULL, 2492, 1),
(1144, 19, NULL, 2493, 1),
(1145, 19, NULL, 2, 1),
(1146, 19, NULL, 3, 1),
(1147, 19, NULL, 4, 1),
(1148, 19, NULL, 5, 1),
(1149, 19, NULL, 39, 1),
(1150, 19, NULL, 12, 1),
(1151, 19, NULL, 16, 1),
(1152, 19, NULL, 17, 1),
(1153, 19, NULL, 18, 1),
(1154, 19, NULL, 20, 1),
(1155, 19, NULL, 41, 1),
(1156, 19, NULL, 2488, 1),
(1157, 19, NULL, 2495, 1),
(1158, 19, NULL, 9, 1),
(1159, 19, NULL, 15, 1),
(1160, 19, NULL, 44, 1),
(1161, 19, NULL, 14, 1),
(1162, 19, NULL, 24, 1),
(1163, 19, NULL, 43, 1),
(1164, 19, NULL, 1, 1),
(1165, 19, NULL, 25, 1),
(1166, 19, NULL, 26, 1),
(1167, 19, NULL, 10, 1),
(1168, 19, NULL, 31, 1),
(1169, 19, NULL, 33, 1),
(1170, 19, NULL, 35, 1),
(1171, 19, NULL, 46, 1),
(1172, 19, NULL, 2476, 1),
(1173, 19, NULL, 45, 1),
(1174, 19, NULL, 48, 1),
(1175, 19, NULL, 2496, 1),
(1176, 19, NULL, 2497, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso_sub_menu`
--

CREATE TABLE `permiso_sub_menu` (
  `id_permiso_sub` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_submenu` int(11) NOT NULL,
  `permiso` tinyint(1) DEFAULT '1' COMMENT '1=visible, 0=oculto',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permiso_sub_menu`
--

INSERT INTO `permiso_sub_menu` (`id_permiso_sub`, `id_usuario`, `id_submenu`, `permiso`, `fecha_creacion`) VALUES
(2, 2495, 2, 0, '2026-09-22 12:22:44'),
(4, 2495, 4, 0, '2026-09-22 12:22:44'),
(5, 2495, 13, 1, '2026-09-22 12:22:44'),
(6, 2495, 14, 0, '2026-09-22 12:22:44'),
(7, 2495, 15, 0, '2026-09-22 12:22:44'),
(8, 2495, 16, 0, '2026-09-22 12:22:44'),
(9, 2495, 17, 0, '2026-09-22 12:22:44'),
(10, 2495, 18, 0, '2026-09-22 12:22:44'),
(11, 2495, 8, 1, '2026-09-22 12:22:44'),
(12, 2495, 9, 1, '2026-09-22 12:22:44'),
(13, 2495, 10, 1, '2026-09-22 12:22:44'),
(14, 2495, 11, 0, '2026-09-22 12:22:44'),
(15, 2495, 12, 0, '2026-09-22 12:22:44'),
(16, 2495, 22, 0, '2026-09-22 12:22:44'),
(17, 2495, 23, 0, '2026-09-22 12:22:44'),
(18, 2495, 20, 1, '2026-09-22 12:22:44'),
(19, 2495, 21, 1, '2026-09-22 12:22:44'),
(20, 2495, 24, 1, '2026-09-22 12:22:44'),
(21, 2495, 25, 0, '2026-09-22 12:22:44'),
(22, 2495, 26, 1, '2026-09-22 12:22:44'),
(24, 2, 2, 1, '2026-09-22 12:23:45'),
(26, 2, 4, 0, '2026-09-22 12:23:45'),
(27, 2, 13, 1, '2026-09-22 12:23:45'),
(28, 2, 14, 0, '2026-09-22 12:23:45'),
(29, 2, 15, 0, '2026-09-22 12:23:45'),
(30, 2, 16, 0, '2026-09-22 12:23:45'),
(31, 2, 17, 0, '2026-09-22 12:23:45'),
(32, 2, 18, 0, '2026-09-22 12:23:45'),
(33, 2, 8, 1, '2026-09-22 12:23:45'),
(34, 2, 9, 1, '2026-09-22 12:23:45'),
(35, 2, 10, 1, '2026-09-22 12:23:45'),
(36, 2, 11, 0, '2026-09-22 12:23:45'),
(37, 2, 12, 0, '2026-09-22 12:23:45'),
(38, 2, 22, 0, '2026-09-22 12:23:45'),
(39, 2, 23, 0, '2026-09-22 12:23:45'),
(40, 2, 20, 1, '2026-09-22 12:23:45'),
(41, 2, 21, 1, '2026-09-22 12:23:45'),
(42, 2, 24, 1, '2026-09-22 12:23:45'),
(43, 2, 25, 0, '2026-09-22 12:23:45'),
(44, 2, 26, 0, '2026-09-22 12:23:45'),
(222, 3, 2, 1, '2026-09-23 00:17:53'),
(224, 3, 4, 0, '2026-09-23 00:17:53'),
(225, 3, 8, 1, '2026-09-23 00:17:53'),
(226, 3, 9, 0, '2026-09-23 00:17:53'),
(227, 3, 10, 1, '2026-09-23 00:17:53'),
(228, 3, 11, 0, '2026-09-23 00:17:53'),
(229, 3, 12, 0, '2026-09-23 00:17:53'),
(230, 3, 13, 0, '2026-09-23 00:17:53'),
(231, 3, 14, 0, '2026-09-23 00:17:53'),
(232, 3, 15, 0, '2026-09-23 00:17:53'),
(233, 3, 16, 0, '2026-09-23 00:17:53'),
(234, 3, 17, 0, '2026-09-23 00:17:53'),
(235, 3, 18, 0, '2026-09-23 00:17:53'),
(236, 3, 20, 1, '2026-09-23 00:17:53'),
(237, 3, 21, 1, '2026-09-23 00:17:53'),
(238, 3, 22, 0, '2026-09-23 00:17:53'),
(239, 3, 23, 0, '2026-09-23 00:17:53'),
(240, 3, 24, 1, '2026-09-23 00:17:53'),
(241, 3, 25, 0, '2026-09-23 00:17:53'),
(242, 3, 26, 1, '2026-09-23 00:17:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridad`
--

CREATE TABLE `prioridad` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `prioridad`
--

INSERT INTO `prioridad` (`id`, `nombre`, `orden`) VALUES
(1, 'Alta', 1),
(2, 'Media', 2),
(3, 'Baja', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso_tickets`
--

CREATE TABLE `proceso_tickets` (
  `id_proceso` int(11) NOT NULL,
  `id_ticket` int(11) DEFAULT NULL,
  `fecha_creacion_inicio` date DEFAULT NULL,
  `hora_creacion_inicio` time DEFAULT NULL,
  `fecha_estimada_admin` date DEFAULT NULL,
  `dias_estimada_admin` int(11) DEFAULT NULL,
  `fecha_asignacion_tecnico` date DEFAULT NULL,
  `hora_asignacion_tecnico` time DEFAULT NULL,
  `fecha_comienzo_ticket` date DEFAULT NULL,
  `hora_comienzo_ticket` time DEFAULT NULL,
  `fecha_termino_ticket` date DEFAULT NULL,
  `hora_termino_ticket` time DEFAULT NULL,
  `fecha_cierre_ticket` date DEFAULT NULL,
  `hora_cierre_ticket` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proceso_tickets`
--

INSERT INTO `proceso_tickets` (`id_proceso`, `id_ticket`, `fecha_creacion_inicio`, `hora_creacion_inicio`, `fecha_estimada_admin`, `dias_estimada_admin`, `fecha_asignacion_tecnico`, `hora_asignacion_tecnico`, `fecha_comienzo_ticket`, `hora_comienzo_ticket`, `fecha_termino_ticket`, `hora_termino_ticket`, `fecha_cierre_ticket`, `hora_cierre_ticket`) VALUES
(1, 1, '2024-05-10', '09:12:32', '2024-05-10', 1, '2024-05-10', '09:13:13', '2024-05-10', '09:23:13', '2024-05-10', '10:23:13', '2025-04-01', '22:21:58'),
(11, 11, '2024-06-06', '12:05:09', '2024-06-10', 4, '2024-06-06', '12:05:57', '2024-06-10', '12:25:32', '2024-06-10', '12:25:40', '2026-03-24', '10:00:59'),
(13, 13, '2024-06-10', '16:45:13', '2024-06-14', 4, '2024-06-10', '16:45:53', '2024-06-14', '09:30:47', '2024-07-29', '16:54:08', NULL, NULL),
(17, 17, '2024-06-19', '09:38:31', '0000-00-00', 3, '0000-00-00', '00:00:00', '2024-06-19', '09:38:43', '2024-06-25', '08:20:01', NULL, NULL),
(32, 36, '2024-07-10', '10:12:03', '2024-07-22', 10, '2024-07-10', '10:12:03', '2024-07-12', '11:10:28', '2024-07-29', '16:55:05', NULL, NULL),
(35, 39, '2024-07-10', '10:14:26', '2024-07-17', 5, '2024-07-10', '10:14:26', '2024-07-12', '11:11:07', NULL, NULL, NULL, NULL),
(205, 223, '2025-05-16', '12:00:12', '0000-00-00', 0, '2025-05-16', '12:00:12', '2025-05-16', '12:04:02', '2026-04-07', '15:11:11', NULL, NULL),
(213, 231, '2025-06-04', '12:01:09', '0000-00-00', 0, '2025-06-06', '11:05:31', '2025-06-06', '12:28:38', '2025-06-09', '15:12:44', NULL, NULL),
(367, 385, '2025-07-08', '15:41:58', '2025-07-11', 3, '2025-07-08', '15:41:58', '2025-07-08', '16:05:47', '2025-07-08', '16:13:42', '2025-07-08', '16:27:30'),
(373, 391, '2025-07-11', '12:13:25', '2025-07-14', 3, '2025-07-11', '12:13:25', '2025-07-11', '12:18:21', '2025-07-11', '12:21:25', '2025-07-11', '12:23:46'),
(376, 394, '2025-07-15', '10:07:25', '2025-07-15', 0, NULL, NULL, '2025-07-15', '10:52:00', '2025-07-15', '11:05:10', '2025-07-24', '14:19:12'),
(392, 410, '2025-07-29', '12:13:41', '2025-07-31', 2, '2025-07-29', '12:13:41', '2025-07-29', '16:57:25', '2025-07-30', '10:13:48', NULL, NULL),
(393, 411, '2025-07-30', '15:47:23', '2025-07-31', 1, '2025-07-30', '15:47:23', '2025-07-30', '15:49:09', '2025-07-31', '12:36:07', NULL, NULL),
(394, 412, '2025-07-30', '15:48:35', '2025-08-01', 2, '2025-07-30', '15:48:35', '2025-07-30', '15:49:46', '2026-03-16', '15:11:51', NULL, NULL),
(395, 413, '2025-07-30', '15:52:53', '2025-08-15', 16, '2025-07-30', '15:52:53', '2025-07-30', '15:53:27', '2026-03-16', '15:12:52', NULL, NULL),
(400, 418, '2025-07-30', '16:47:59', '2025-07-30', 0, '2025-07-30', '16:47:59', '2025-07-30', '16:49:16', '2025-07-30', '16:49:34', '2025-07-30', '16:52:50'),
(401, 419, '2025-07-30', '16:49:10', '2025-07-30', 1, '2025-07-30', '16:49:10', '2025-07-30', '16:49:56', '2025-07-30', '16:50:18', '2025-07-30', '16:52:15'),
(403, 421, '2025-08-21', '08:43:11', '2026-04-03', 7, '2025-08-21', '08:43:11', '2026-03-27', '09:41:22', NULL, NULL, NULL, NULL),
(405, 423, '2025-08-21', '17:19:40', '2025-08-25', 3, '2025-08-21', '17:19:40', '2025-08-22', '12:07:37', '2026-03-16', '15:29:29', NULL, NULL),
(406, 424, '2025-09-02', '09:53:19', '0000-00-00', 0, '2025-09-03', '08:41:01', '2025-09-03', '09:19:29', '2025-09-03', '09:19:45', NULL, NULL),
(416, 434, '2025-09-04', '09:56:03', '2025-09-05', 1, '2025-09-04', '10:08:30', '2025-09-04', '10:11:41', '2025-09-04', '11:37:11', NULL, NULL),
(417, 435, '2025-09-04', '09:59:28', '2026-03-27', 0, '2025-09-04', '09:59:28', '2026-03-27', '09:34:51', '2026-03-27', '09:44:32', NULL, NULL),
(418, 436, '2025-09-04', '10:00:40', '2025-09-05', 1, '2025-09-04', '10:00:40', '2025-09-04', '10:27:37', '2025-09-04', '10:28:13', NULL, NULL),
(433, 451, '2025-10-09', '08:34:20', '2025-10-09', 0, '2025-10-09', '08:34:20', '2025-10-09', '08:35:22', '2025-10-09', '08:45:31', '2025-10-09', '15:13:50'),
(434, 452, '2025-10-13', '11:18:07', '2026-03-12', 1, '2025-10-13', '11:18:07', '2026-03-11', '20:32:56', '2026-03-12', '10:22:34', NULL, NULL),
(435, 453, '2025-10-13', '16:39:09', '2025-10-17', 3, '2025-10-13', '16:39:09', '2025-10-14', '12:16:34', '2026-03-16', '15:30:04', NULL, NULL),
(438, 456, '2025-10-14', '14:53:40', '2026-03-27', 0, '2025-10-14', '14:53:40', '2026-03-27', '09:35:34', '2026-03-27', '09:46:03', NULL, NULL),
(439, 457, '2025-10-14', '14:55:44', '2026-03-27', 0, '2025-10-14', '14:55:44', '2026-03-27', '09:36:01', '2026-03-27', '09:46:27', NULL, NULL),
(440, 458, '2025-10-23', '08:42:12', '2025-10-24', 1, '2025-10-23', '08:42:12', '2025-10-23', '08:52:38', '2025-10-23', '09:11:43', NULL, NULL),
(441, 459, '2025-10-23', '08:46:29', '2025-10-24', 1, '2025-10-23', '08:46:29', '2025-10-23', '09:12:26', '2025-10-23', '10:00:07', NULL, NULL),
(442, 460, '2025-10-28', '09:04:46', '2025-10-28', 0, '2025-10-28', '09:04:46', '2025-10-28', '13:57:13', '2025-10-28', '14:08:38', NULL, NULL),
(443, 461, '2025-10-29', '11:43:33', '2025-10-29', 0, '2025-10-29', '11:43:33', '2025-10-29', '11:53:19', '2025-10-29', '11:55:26', NULL, NULL),
(444, 462, '2025-11-04', '12:41:34', '2025-11-06', 0, '2025-11-04', '12:41:34', '2025-11-06', '14:27:13', '2025-11-06', '14:28:36', NULL, NULL),
(445, 463, '2025-11-10', '09:11:08', '0000-00-00', 0, '2026-01-08', '15:15:51', '2026-03-17', '12:50:34', '2026-03-17', '12:50:58', NULL, NULL),
(446, 464, '2025-11-10', '11:22:29', '2025-11-11', 0, '2025-11-10', '11:22:29', '2025-11-11', '12:21:06', '2025-11-11', '12:21:50', NULL, NULL),
(447, 465, '2025-11-27', '12:14:34', '2025-12-01', 3, '2025-11-27', '12:14:34', '2025-11-28', '09:26:28', '2026-03-16', '15:30:28', NULL, NULL),
(448, 466, '2025-12-15', '16:07:44', '2026-03-27', 0, '2025-12-15', '16:07:44', '2026-03-27', '09:36:24', '2026-03-27', '09:49:24', NULL, NULL),
(449, 467, '2026-01-05', '08:30:43', '2026-03-27', 0, '2026-01-05', '08:30:43', '2026-03-27', '09:37:09', '2026-03-27', '09:44:54', NULL, NULL),
(450, 468, '2026-01-07', '16:04:51', '0000-00-00', 0, '2026-01-07', '16:04:51', '2026-03-24', '11:26:53', '2026-03-27', '09:49:43', '2026-08-14', '14:26:16'),
(451, 469, '2026-01-07', '16:23:40', '2026-01-08', 0, '2026-01-07', '16:23:40', '2026-01-08', '09:48:23', '2026-01-08', '09:50:24', '2026-01-12', '10:58:05'),
(452, 470, '2026-01-09', '11:38:36', '0000-00-00', 0, '2026-01-09', '11:38:36', '2026-03-16', '15:18:51', '2026-04-09', '14:44:35', NULL, NULL),
(453, 471, '2026-01-09', '12:11:57', '2026-01-13', 4, '2026-01-09', '12:12:48', '2026-01-09', '12:14:03', '2026-01-12', '10:54:39', '2026-01-12', '10:57:34'),
(454, 472, '2026-01-13', '09:10:29', '2026-03-12', 1, '2026-01-13', '09:10:29', '2026-03-11', '20:31:23', '2026-03-12', '11:49:25', '2026-03-16', '15:07:29'),
(455, 473, '2026-01-13', '09:11:29', '2026-03-02', 7, '2026-01-13', '09:12:05', '2026-02-23', '11:01:52', '2026-02-26', '11:36:15', '2026-03-09', '15:47:44'),
(456, 474, '2026-01-13', '09:12:59', '2026-03-03', 0, '2026-01-13', '09:12:59', '2026-03-03', '15:20:07', '2026-03-11', '20:54:33', NULL, NULL),
(457, 475, '2026-02-20', '08:40:41', '2026-02-20', 0, '2026-02-20', '08:40:41', '2026-02-20', '10:48:10', '2026-02-20', '10:48:29', NULL, NULL),
(458, 476, '2026-02-23', '10:53:58', '2026-03-02', 0, '2026-02-23', '10:53:58', '2026-03-02', '11:52:10', '2026-03-02', '11:52:40', '2026-03-12', '10:53:12'),
(459, 477, '2026-02-23', '10:54:59', '2026-02-23', 0, '2026-02-23', '10:54:59', '2026-02-23', '11:01:28', '2026-02-23', '14:32:28', '2026-03-09', '15:47:07'),
(460, 478, '2026-02-23', '10:56:19', '2026-02-23', 0, '2026-02-23', '10:56:19', '2026-02-23', '11:01:02', '2026-02-24', '11:10:36', '2026-03-09', '15:53:10'),
(461, 479, '2026-02-24', '11:11:56', '2026-02-27', 1, '2026-02-24', '11:12:52', '2026-02-26', '11:36:54', '2026-02-26', '11:37:06', '2026-03-09', '15:46:40'),
(462, 480, '2026-02-26', '11:24:48', '2026-04-03', 7, '2026-02-26', '11:24:48', '2026-03-27', '09:37:44', '2026-05-26', '11:38:36', NULL, NULL),
(463, 481, '2026-02-26', '11:27:27', '2026-04-03', 7, '2026-02-26', '11:27:27', '2026-03-27', '09:38:11', NULL, NULL, NULL, NULL),
(464, 482, '2026-02-26', '11:28:55', '2026-03-02', 0, '2026-02-26', '11:28:55', '2026-03-02', '11:55:53', '2026-03-05', '14:26:08', '2026-03-09', '15:46:10'),
(465, 483, '2026-02-27', '12:33:51', '2026-03-03', 0, '2026-02-27', '12:33:51', '2026-03-03', '10:30:34', '2026-03-03', '10:30:59', '2026-03-09', '15:45:42'),
(466, 484, '2026-02-27', '12:43:22', '0000-00-00', 0, '2026-02-27', '12:43:22', '2026-03-24', '11:26:01', '2026-03-27', '09:47:02', NULL, NULL),
(467, 485, '2026-03-02', '08:19:48', '2026-03-02', 0, '2026-03-02', '08:19:48', '2026-03-02', '11:55:10', '2026-03-05', '14:26:17', '2026-03-09', '15:44:52'),
(468, 486, '2026-03-02', '08:24:58', '2026-03-10', 8, '2026-03-02', '08:24:58', '2026-03-10', '10:01:55', '2026-03-10', '10:02:06', '2026-03-10', '15:34:20'),
(469, 487, '2026-03-03', '14:49:10', '2026-03-09', 0, '2026-03-03', '14:50:26', '2026-03-09', '15:49:37', '2026-03-09', '15:50:09', '2026-03-09', '15:51:30'),
(470, 488, '2026-03-05', '14:19:05', '2026-03-09', 0, '2026-03-05', '14:19:28', '2026-03-09', '15:48:29', '2026-03-09', '15:49:15', '2026-03-09', '15:51:03'),
(471, 489, '2026-03-09', '15:54:39', '2026-03-16', 7, '2026-03-09', '15:54:39', '2026-03-09', '16:17:10', '2026-05-13', '16:02:22', NULL, NULL),
(472, 490, '2026-03-09', '15:55:22', '2026-03-16', 7, '2026-03-09', '15:55:22', '2026-03-09', '16:16:50', '2026-04-07', '14:45:05', NULL, NULL),
(473, 491, '2026-03-09', '15:56:14', '2026-03-13', 4, '2026-03-09', '15:56:14', '2026-03-09', '16:16:31', '2026-03-16', '15:31:18', NULL, NULL),
(474, 492, '2026-03-09', '15:57:09', '2026-04-17', 2, '2026-03-09', '15:57:09', '2026-03-09', '16:16:13', NULL, NULL, NULL, NULL),
(475, 493, '2026-03-10', '08:40:38', '2026-03-13', 1, '2026-03-10', '08:40:38', '2026-03-12', '16:45:24', '2026-03-16', '15:35:16', NULL, NULL),
(476, 494, '2026-03-10', '08:42:16', '2026-03-13', 1, '2026-03-10', '08:42:16', '2026-03-12', '16:45:08', '2026-03-16', '15:32:55', NULL, NULL),
(477, 495, '2026-03-11', '11:34:53', '2026-03-12', 0, '2026-03-12', '10:56:20', '2026-03-12', '10:57:05', '2026-03-13', '12:29:01', '2026-03-16', '11:00:52'),
(478, 496, '2026-03-11', '11:35:31', '0000-00-00', 0, '2026-03-12', '10:56:09', '2026-03-12', '10:56:37', '2026-03-13', '12:28:47', '2026-03-16', '11:01:17'),
(479, 497, '2026-03-11', '16:27:10', '2026-03-11', 0, '2026-03-11', '16:27:10', '2026-03-11', '16:27:31', '2026-03-11', '16:29:10', NULL, NULL),
(480, 498, '2026-03-11', '16:28:49', '2026-03-11', 0, '2026-03-11', '16:28:49', '2026-03-11', '16:29:29', '2026-03-11', '16:29:40', NULL, NULL),
(481, 499, '2026-03-12', '10:54:32', '2026-04-18', 3, '2026-03-12', '10:54:32', '2026-03-27', '09:43:44', NULL, NULL, NULL, NULL),
(482, 500, '2026-03-12', '10:55:30', '2026-04-03', 7, '2026-03-13', '12:28:17', '2026-03-27', '09:48:57', '2026-04-09', '14:44:15', NULL, NULL),
(485, 503, '2026-03-12', '15:11:51', '2026-03-17', 1, '2026-03-13', '12:28:02', '2026-03-16', '13:38:12', '2026-03-16', '15:35:00', NULL, NULL),
(486, 504, '2026-03-12', '16:39:09', '2026-03-12', 0, '2026-03-12', '16:39:09', '2026-03-12', '16:43:37', '2026-03-12', '16:44:43', '2026-03-16', '11:00:14'),
(487, 505, '2026-03-13', '12:26:47', '0000-00-00', 0, '2026-03-13', '12:29:34', '2026-03-13', '12:29:47', '2026-04-07', '15:34:17', NULL, NULL),
(488, 506, '2026-03-16', '13:39:14', '0000-00-00', 0, '2026-03-16', '13:39:14', '2026-03-18', '16:27:54', '2026-03-30', '14:54:35', NULL, NULL),
(489, 507, '2026-03-16', '14:18:22', '2026-03-18', 2, '2026-03-16', '14:18:22', '2026-03-16', '14:18:47', '2026-03-27', '09:50:23', NULL, NULL),
(490, 508, '2026-03-16', '16:30:52', '2026-04-17', 2, '2026-03-16', '16:30:52', '2026-03-27', '09:48:38', '2026-05-13', '16:02:11', NULL, NULL),
(495, 513, '2026-03-23', '10:08:09', '0000-00-00', 0, '2026-03-23', '10:08:09', '2026-03-23', '16:51:10', '2026-03-23', '16:52:15', '2026-03-24', '10:06:43'),
(496, 514, '2026-03-23', '16:50:03', '2026-04-17', 2, '2026-03-23', '16:50:03', '2026-03-27', '09:48:17', '2026-04-16', '08:09:45', NULL, NULL),
(497, 515, '2026-03-23', '16:54:53', '2026-04-15', 0, '2026-03-23', '16:55:21', '2026-03-24', '02:00:01', NULL, NULL, NULL, NULL),
(502, 520, '2026-03-24', '20:39:58', '2026-03-27', 3, '2026-03-24', '20:39:58', '2026-03-24', '20:40:35', '2026-03-30', '10:29:54', '2026-04-10', '12:11:32'),
(503, 521, '2026-03-25', '13:02:20', '2026-03-25', 0, '2026-03-25', '13:02:20', '2026-03-25', '13:02:50', '2026-03-25', '13:03:28', '2026-03-25', '13:04:07'),
(505, 523, '2026-03-30', '14:56:03', '2026-03-31', 1, '2026-03-30', '14:57:15', '2026-03-30', '14:57:41', '2026-04-08', '15:21:29', NULL, NULL),
(506, 524, '2026-03-30', '15:44:18', '2026-03-31', 1, '2026-03-30', '15:44:18', '2026-03-30', '15:44:52', '2026-04-07', '14:21:06', NULL, NULL),
(509, 527, '2026-04-01', '12:01:44', '2026-04-06', 5, '2026-04-01', '12:02:43', '2026-04-01', '12:03:02', '2026-04-01', '14:58:56', '2026-04-10', '10:43:04'),
(510, 528, '2026-04-06', '11:54:17', '2026-04-13', 7, '2026-04-06', '11:54:17', '2026-04-06', '11:54:42', '2026-04-14', '11:25:33', NULL, NULL),
(511, 529, '2026-04-07', '11:26:47', '2026-04-07', 0, '2026-04-07', '11:26:47', '2026-04-07', '14:44:16', '2026-04-07', '14:44:29', '2026-04-10', '10:46:15'),
(512, 530, '2026-04-07', '14:19:18', '2026-04-09', 2, '2026-04-07', '14:19:18', '2026-04-07', '14:19:46', '2026-04-08', '15:20:40', NULL, NULL),
(515, 533, '2026-04-09', '09:21:09', '2026-04-10', 1, '2026-04-09', '09:21:27', '2026-04-09', '09:21:45', '2026-04-09', '14:41:18', NULL, NULL),
(533, 551, '2026-04-14', '10:13:19', '2026-04-14', 0, '2026-04-14', '10:13:19', '2026-04-14', '11:23:29', '2026-04-14', '11:23:42', NULL, NULL),
(544, 562, '2026-04-15', '14:35:58', '2026-04-16', 0, '2026-04-15', '14:35:58', '2026-04-16', '08:08:53', '2026-04-16', '08:09:05', NULL, NULL),
(545, 563, '2026-04-15', '14:36:26', '2026-04-21', 5, '2026-04-15', '14:36:26', '2026-04-16', '08:09:26', NULL, NULL, NULL, NULL),
(547, 565, '2026-04-15', '14:51:16', '2026-05-06', 0, '2026-04-16', '14:23:13', '2026-05-06', '14:43:45', '2026-05-06', '14:43:59', NULL, NULL),
(548, 566, '2026-04-28', '11:35:54', '2026-05-13', 0, '2026-04-28', '11:35:54', '2026-05-13', '16:10:32', '2026-05-13', '16:10:42', NULL, NULL),
(549, 567, '2026-04-28', '12:14:01', '2026-05-20', 7, '2026-04-28', '12:14:01', '2026-05-13', '16:11:53', NULL, NULL, NULL, NULL),
(550, 568, '2026-04-28', '12:14:45', '2026-05-13', 0, '2026-04-28', '12:14:45', '2026-05-13', '16:09:01', '2026-05-13', '16:09:19', NULL, NULL),
(551, 569, '2026-05-11', '14:38:09', '2026-05-20', 7, '2026-05-11', '14:38:09', '2026-05-13', '16:11:40', NULL, NULL, NULL, NULL),
(552, 570, '2026-05-11', '14:38:46', '2026-05-20', 7, '2026-05-11', '14:38:46', '2026-05-13', '16:11:27', NULL, NULL, NULL, NULL),
(553, 571, '2026-05-11', '14:39:43', '2026-06-11', 31, '2026-05-11', '14:41:22', '2026-05-11', '14:42:33', NULL, NULL, NULL, NULL),
(554, 572, '2026-05-11', '14:40:28', '2026-06-11', 31, '2026-05-11', '14:41:05', '2026-05-11', '14:42:18', NULL, NULL, NULL, NULL),
(555, 573, '2026-05-12', '14:48:00', '2026-05-20', 7, '2026-05-12', '14:48:00', '2026-05-13', '16:11:13', NULL, NULL, NULL, NULL),
(556, 574, '2026-05-12', '14:51:34', '2026-05-13', 0, '2026-05-12', '14:51:34', '2026-05-13', '16:04:54', '2026-05-13', '16:05:58', NULL, NULL),
(557, 575, '2026-05-14', '11:47:33', '2026-05-21', 7, '2026-05-14', '11:48:07', '2026-05-14', '11:55:58', '2026-05-18', '12:11:09', '2026-07-22', '16:22:46'),
(558, 576, '2026-05-15', '09:57:45', '2026-05-15', 0, '2026-05-15', '09:57:45', '2026-05-15', '09:58:36', '2026-05-15', '09:58:48', NULL, NULL),
(559, 577, '2026-05-18', '11:35:05', '2026-05-18', 0, '2026-05-18', '11:35:05', '2026-05-18', '11:35:22', '2026-05-18', '11:36:07', NULL, NULL),
(560, 578, '2026-05-18', '16:27:25', '2026-05-19', 0, '2026-05-18', '16:27:25', '2026-05-19', '12:15:46', '2026-05-19', '12:16:08', '2026-07-22', '16:22:22'),
(561, 579, '2026-05-19', '08:51:07', '2026-05-19', 0, '2026-05-19', '08:51:54', '2026-05-19', '09:06:20', '2026-05-19', '09:06:30', '2026-05-19', '09:07:08'),
(562, 580, '2026-05-20', '10:16:53', '2026-05-26', 1, '2026-05-20', '10:16:53', '2026-05-25', '12:54:07', '2026-05-25', '12:54:19', '2026-07-22', '16:23:01'),
(563, 581, '2026-05-27', '10:18:03', '2026-05-27', 0, '2026-05-27', '10:18:14', '2026-05-27', '10:44:12', '2026-05-27', '11:15:16', '2026-08-14', '14:27:23'),
(564, 582, '2026-05-27', '11:21:08', '2026-05-27', 0, '2026-05-27', '11:21:35', '2026-05-27', '11:21:54', '2026-05-27', '15:50:28', '2026-08-14', '14:26:32'),
(565, 583, '2026-06-22', '15:41:33', '0000-00-00', 0, '2026-06-22', '15:41:33', NULL, NULL, NULL, NULL, NULL, NULL),
(566, 584, '2026-08-14', '14:23:54', '0000-00-00', 0, '2026-08-14', '14:23:54', NULL, NULL, NULL, NULL, NULL, NULL),
(567, 585, '2026-08-14', '14:24:31', '2026-09-25', 23, '2026-08-14', '14:24:31', '2026-09-02', '10:48:48', NULL, NULL, NULL, NULL),
(568, 586, '2026-09-02', '09:34:48', '2026-09-02', 0, '2026-09-02', '09:34:48', '2026-09-02', '10:15:39', '2026-09-02', '10:24:17', NULL, NULL),
(569, 587, '2026-09-02', '09:36:33', '2026-09-02', 0, '2026-09-02', '09:36:33', '2026-09-02', '10:36:36', '2026-09-02', '10:37:01', NULL, NULL),
(570, 588, '2026-09-08', '10:28:44', '2026-09-11', 3, '2026-09-08', '10:28:44', '2026-09-08', '10:31:56', NULL, NULL, NULL, NULL),
(571, 588, '2026-09-08', '13:46:03', NULL, NULL, '2026-09-08', '13:46:03', NULL, NULL, NULL, NULL, NULL, NULL),
(572, 589, '2026-09-08', '13:47:19', NULL, NULL, '2026-09-08', '13:47:19', NULL, NULL, NULL, NULL, NULL, NULL),
(578, 595, '2026-09-08', '14:20:35', NULL, NULL, '2026-09-08', '14:20:35', NULL, NULL, NULL, NULL, NULL, NULL),
(579, 596, '2026-09-09', '08:03:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(580, 597, '2026-09-09', '09:34:03', NULL, NULL, '2026-09-09', '09:34:03', NULL, NULL, NULL, NULL, NULL, NULL),
(581, 598, '2026-09-09', '09:34:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sitios_web_catalogo`
--

CREATE TABLE `sitios_web_catalogo` (
  `id_sitio` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_usuario_responsable` int(11) NOT NULL DEFAULT '0',
  `nombre_sitio` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_sitio` enum('Web','App','Cliente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Web',
  `url_sitio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proveedor_hosting` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_sitio` enum('Activo','Mantenimiento','En desarrollo','Inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `id_usuario` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software_catalogo`
--

CREATE TABLE `software_catalogo` (
  `id_software` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_usuario_responsable` int(11) NOT NULL DEFAULT '0',
  `id_tipo_usuario` int(11) NOT NULL DEFAULT '0',
  `nombre_software` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version_software` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad_licencias` int(11) NOT NULL DEFAULT '1',
  `tipo_licenciamiento` enum('Suscripcion','Licencia perpetua','Gratuita') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Suscripcion',
  `fecha_inicio_licencia` date DEFAULT NULL,
  `fecha_fin_licencia` date DEFAULT NULL,
  `pagado_por` enum('Colegio','Seduc','Persona') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colegio',
  `costo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `proveedor` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `id_usuario` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software_datos_almacenamiento`
--

CREATE TABLE `software_datos_almacenamiento` (
  `id_dato` int(11) NOT NULL,
  `id_software` int(11) NOT NULL,
  `nombre_contacto` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rut_contacto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_contacto` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otros_datos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden_dato` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software_datos_sensibles_rel`
--

CREATE TABLE `software_datos_sensibles_rel` (
  `id_relacion` int(11) NOT NULL,
  `id_software` int(11) NOT NULL,
  `id_dato_sensible` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software_historial`
--

CREATE TABLE `software_historial` (
  `id_historial` int(11) NOT NULL,
  `id_software` int(11) NOT NULL,
  `accion` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_usuario` int(11) NOT NULL DEFAULT '0',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software_tipo_usuario_rel`
--

CREATE TABLE `software_tipo_usuario_rel` (
  `id_relacion` int(11) NOT NULL,
  `id_software` int(11) NOT NULL,
  `id_tipo_usuario` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnicos`
--

CREATE TABLE `tecnicos` (
  `id_tecnico` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ape_paterno` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ape_materno` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tecnicos`
--

INSERT INTO `tecnicos` (`id_tecnico`, `nombre`, `ape_paterno`, `ape_materno`, `email`, `telefono`, `anexo`, `foto`, `estado`) VALUES
(1, 'Manuel', 'Gutierrez', 'Gutierrez', 'mgutierrez@seduc.cl', '123456789', '358', NULL, 1),
(2, 'Ramon', 'Oliva', 'Zenteno', 'roliva@seduc.cl', '+5687545621', '359', NULL, 1),
(3, 'Cristian', 'Jorquera', 'Gonzalez', 'cjorquera@seduc.cl', '+56988302735', '322', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `descripcion_ticket` text,
  `id_categoria_ticket` int(11) DEFAULT NULL,
  `id_estado` int(11) DEFAULT NULL,
  `id_prioridad` int(11) DEFAULT NULL,
  `id_tecnico` int(11) DEFAULT NULL,
  `comentario_administrador` text,
  `comentario_final` varchar(100) DEFAULT NULL,
  `comentario_reactivacion` varchar(100) NOT NULL,
  `identificador` varchar(16) NOT NULL,
  `estado` int(10) NOT NULL DEFAULT '1' COMMENT '1=activo / 2= borrado '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tickets`
--

INSERT INTO `tickets` (`id_ticket`, `id_usuario`, `asunto`, `descripcion_ticket`, `id_categoria_ticket`, `id_estado`, `id_prioridad`, `id_tecnico`, `comentario_administrador`, `comentario_final`, `comentario_reactivacion`, `identificador`, `estado`) VALUES
(1, 1, 'Procesar boletas faltantes', 'Apoderado necesita las boletas A8795 06/05/2024', 5, 5, 1, 7, 'se deben descargar de la caja y procesar en DBNET', '', '', '', 1),
(11, 10, 'Familia Ugarte Vidaurre tema alumno', 'De acuerdo a lo conversado, te detallo la informaciÃ³n del exalumno del Cordillera para ser incorporado como antecedente en la ficha de la familia UGARTE VIDAURRE, en SEDUC Servicios.\r\n', 1, 5, 1, 6, 'Ramon, ver opcion de campo observaciones en la ficha del alumno', '', '', 'iUmndwYkbEuttc8T', 1),
(13, 25, 'Habilitar pago webpay cheque a fecha y protestado', 'Habilitar pago webpay cheque a fecha y protestado', 1, 5, 1, 6, 'se entregara el criterio para su implementacion.\r\n', '', '', 'A2zCj68nE2ThkpVi', 1),
(17, 17, 'Email de cobranza mensual', 'Modificar enviÃ³ actual, filtrar por mes e incluir toda la deuda del apoderado', 1, 5, 3, 6, NULL, '', '', 'gX5rzCaFgHTzuvQV', 1),
(36, 29, 'Acceso a notas Jefas de Departamento', 'Las Jefas de Departamento no tienen acceso a ver todas las notas de su asignatura en los distintos cursos. Por lo tanto, serÃ­a muy Ãºtil dar acceso a Jefas de Departamento a que vean todas las notas de sus asignaturas.', 2, 5, 1, 6, '', '', '', '1Jx0cELhRR9vsT2p', 1),
(39, 29, 'Acceso a biblioteca digital', 'Profesoras de lenguaje necesitan desde su SIAE acceso a la biblioteca digital, para saber quÃ© libros estÃ¡n disponibles. (sÃ³lo que tengan acceso, entiendo que en el SIAE no gestionan la biblioteca)', 2, 5, 1, 6, '', '', '', 'R7SyimM6Wo8T8TcE', 1),
(223, 45, 'Problema Apoderada', 'Estimados,\n\nReciba­ esta solicitud de una mama¡. \n\nDe antemano muchas gracias\n\nEstimado Gerardo,\n\nComo esta¡?\nSin querer, ayer puse â€œdesuscribirmeâ€ a la lista de correos del colegio. Le pido por favor si me pudiera agregar nuevamente.\n\nMi correo es valentinagar@gmail.com\n\nMuchas gracias por su ayuda.\nSaludos cordiales,\n\nValentina GarcÃ­a\nMamÃ¡ de Augusto (8C) e Ismael Poblete (1A)', 7, 5, 1, 36, NULL, '', '', 'puYh6Z90lbL0j9hE', 1),
(231, 45, 'Office MAC', 'Estimados,\n\nNecesitamos contar con el acceso a las licencias de office para equipos Mac, con PC estamos OK, pero no hemos recibido informaciÃ³n de mac y no contamos actualmente con la herramienta.\n\nQuedo atento y muchas gracias.', 10, 5, 0, 7, '', '', '', 'swMWqPy6KoVQ3GN0', 1),
(385, 31, 'Enviar claves MS', 'Enviar claves de Office y Win', 7, 5, 2, 7, NULL, '', '', '468sL4BGJxRmpwKt', 1),
(391, 31, 'Revisar propuesta red', 'ver tema de cambio de equipos ubiquiti', 3, 5, 1, 7, NULL, '', '', 'LQhxlBtquL0jh273', 1),
(394, 45, 'Redireccionar web', 'Estimados,\r\n\r\nPor favor redireccionar URL\r\noutdoor.tabancura.cl a https://sites.google.com/tabancura.cl/outdoortabancura2025?usp=sharing \r\n\r\nDe antemano gracias.', 10, 5, 2, 7, NULL, '', '', 'Dhk6ExpvacELxKtu', 1),
(410, 45, 'Problema en cÃ¡lculo de notas', 'Estimados, \r\n\r\nEl promedio del alumno JUAN DE DIOS FUENZALIDA IZQUIERDO estÃ¡ calculÃ¡ndose mal, el promedio correcto es: 62,71428571 y arroja un 6.0 en su libreta de notas.\r\n\r\nTengo la impresiÃ³n que es la primera informativa la que se estÃ¡ calculando mal.\r\n\r\nQuedo atento a sus comentarios.\r\n\r\nDe antemano, gracias.', 2, 5, 2, 6, NULL, '', '', 'IYZKtsWSpcyjxOSN', 1),
(411, 7, 'Problema SW Casino', 'Visita maÃ±ana jueves 31/07, para diagnosticar falla del equipo y posible reemplazo', 3, 5, 1, 7, NULL, '', '', 'UynEZv9iGJ3MW4yR', 1),
(412, 7, 'Sin Interner Ed. Fisica', 'problema sala de profesores Ed. Fisica por obras de mejoramiento cancha, al parecer cortaron enlace, se debe evaluar alternativa de enlace inalambrico o recableado', 3, 5, 1, 7, NULL, '', '', 'Y75jFPTYv426kQDL', 1),
(413, 7, 'Implementacion Firewall Ubiquiti', 'Implementar y coordinar con proveedor el cambio de Firewall de Mikrotik a EFG Ubiquiti, para que quede todo en la misma plataforma.', 3, 5, 1, 7, NULL, '', '', 'cUI0cWYSgwukPoUC', 1),
(418, 42, 'Reporte de envÃ­o de Correos', 'Gererar reporte de envÃ­o de Correos del mes de julio para evaluar rebotes y entregas', 2, 5, 2, 6, NULL, '', '', '3MdUO2612dQWHmUR', 1),
(419, 42, 'Reporte de envÃ­o de correos U de Navarra', 'Enviar reporte del envio para la charla de la universidad de Navarra', 2, 5, 2, 6, NULL, '', '', 'JkAkCKWjRNtUY60B', 1),
(421, 42, 'Lectura de RUT a partir de QR para porteria', 'Prrmitir la lectura de QR de RUT para registro de proterÃ­a', 2, 7, 2, 6, NULL, NULL, '', 'fk5bnRaVVNSoExQr', 1),
(423, 45, 'SIAE', 'Estimado,\r\n\r\nTenemos porcentjes que se estÃ¡n presentando mal en este archivo (Ciencias Naturales y Ed FÃ­sica y Salud)\r\n\r\nDe antemano muchas gracias.', 2, 5, 2, 6, NULL, '', '', 'ip0WCdNjfxxYI5LJ', 1),
(424, 45, 'Redireccionamiento', 'Estimados,\r\n\r\nFavor redireccionar\r\nhttp://arte.tabancura.cl/\r\na\r\nhttps://docs.google.com/forms/d/e/1FAIpQLSdX6HYKw6SDcWTNfMX7yForKaeUEApEzCykOXQzY2UBnuXfOw/viewform\r\n\r\nDe antemano gracias.', 10, 5, 0, 7, 'Se procedera a la brevedad.', '', '', 'rj0zWJ2ltxL49efd', 1),
(434, 45, 'Office 365', 'Estimados,\r\n\r\nIda Mege y Antonia Muzard nos solicitan office 365, podrÃ­an colaborarnos?\r\nSus correos son imege@tabancura.cl, amuzard@tabancura.cl\r\n\r\nDe antemano gracias.', 10, 5, 2, 7, 'Asignar licencia', '', '', 'so7sX7zDbpfs5x8f', 1),
(435, 45, 'Leccionarios 2023 y 2024', 'Estimados,\r\n\r\nEl profesor Luis Jovel solicita:\r\nLeccionarios de ReligiÃ³n de IIÂ° Medio, del aÃ±o pasado y de 2023\r\n\r\nPor favor, nos pueden ayudar?', 2, 5, 2, 6, NULL, '', '', 'pFIU1PpVrjWkupDM', 1),
(436, 45, 'Atrasos', 'Estimados,\r\n\r\nHe recibido este correo de Miguel Sandoval coordinador de IIIer ciclo.\r\n\r\n\"Estuve revisando los atrasos de nuestros alumnos en el SIAE, pero en esta primera informativa (2Âº semestre) ningÃºn curso del III ciclo tiene registros. UtilicÃ© las dos rutas que nos dio RamÃ³n Oliva en el primer semestre, y en ambas el resultado es el mismo: cero atrasos en todos los cursos.\r\nLas rutas son las siguientes:\r\nAcadÃ©mica --> Libro de clase --> Atrasos periodo.\r\nLibreta de notas periodo.\r\nTe pido, por favor, que puedas preguntar si ocurriÃ³ algÃºn error en el sistema.\r\nDesde ya muchas gracias por la colaboraciÃ³n.\r\n\r\nSaludos cordiales.\"\r\n\r\nNos pueden ayudar?', 2, 5, 2, 6, NULL, '', '', 'GKAfTZXBX6fqFEaj', 1),
(451, 42, 'Carga Profesores', 'Carga profesores en sistema Folett (Colegio Huinganal)', 9, 5, 1, 42, NULL, 'Se cargaron los profesores a Follett', '', 'IQQxFZCdqgHmffLy', 1),
(452, 2476, 'Implementacion sistema TICKET', 'enviar datos de acceso a sistema', 11, 5, 3, 8, NULL, 'Usuario creado correctamente en el Sistema de Tickets. Se envió un correo de activación a la casilla', '', 'cDLBHl2YstjtnYHq', 1),
(453, 33, 'prueba - revision de red', 'puerta en marcha de sistema ticket', 3, 5, 2, 7, NULL, '', '', 'c80c9rNRTqfrAECM', 1),
(456, 7, 'Envio de contratos educacionales', 'Favor trabajar en el envio de contratos educacionales firmados y con pago de matricula 2026 validado', 2, 5, 2, 6, NULL, '', '', '1qt4esZ3fwafYPWP', 1),
(457, 7, 'Descuento resolucion ctas devengadas', 'Favor trabajar en la adecuación de las cuentas devengadas de los descuentos resolución, que debe aplicarse desde mes de pago hasta diciembre y no como ahora desde ene-dic', 1, 5, 2, 6, NULL, '', '', 'kZqZjHYXxsU3rBdf', 1),
(458, 45, 'Entrevista con Padres - Cambio de fecha', 'Estimados,\r\n\r\nRecibí esta solicitud del profesor Francisco Salazar.\r\n\"La entrevista realizada por quien escribe el día 28 de Julio con la familia Saelzer Benavente, corresponde a la entrevista del II Semestre. Sin embargo, el sistema la tomó como entrevista del I Semestre. ¿Es posible que pueda modificarse para que quede asignada al II Semestre?\"\r\n\r\nQuedo atento a sus comentarios', 2, 5, 2, 6, NULL, 'Estimados\nLa entrevista registrada con fecha 28 de julio de 2025 no aparece en los resúmenes del seg', '', 'OgSuAPYd5gZrlsoW', 1),
(459, 45, 'Atrasos', 'Estimados,\r\n\r\nLa levantamos una solicitud ticket el 4 de septiembre y se ve solucionada el 5 del mismo mes, pero me notificaron que continúa el problema (es el ticket 00-436)\r\nCabe destacar que el IC es el único curso del ciclo que arroja correctamente los atrasos.\r\n\r\nAdjunto comentarios del docente:\r\n\"Nuevamente tenemos problemas con los registros de los atrasos en el SIAE. En el primer semestre ocurrió lo mismo y se corrigió, pero ahora vuelve a ocurrir lo mismo:\r\nCuando se sigue la siguiente ruta \"Académica - Libro de clase - Atrasos período - Segundo semestre\" los siguientes cursos aparecen con 0 atrasos en la 1ª y 2ª informativa: IA, IB, IIA, IIB y IIC.\r\nPor favor ayúdanos con esta situación.\r\n\r\nSaludos cordiales.\"', 2, 5, 2, 6, NULL, 'Se respondió por email', '', 'jhIJrfF41SWlWzvz', 1),
(460, 45, 'Redireccionamiento web', 'Estimados,\r\n\r\nPor favor habilitar el redireccionamiento teatro.tabancura.cl a https://sites.google.com/tabancura.cl/teatro2025\r\n\r\nGracias', 12, 5, 2, 7, NULL, 'Gerardo, ya se realizo la habilitacion y prueba del direccionamiento.\nSaludos', '', '8rdeJYiVz1B3fGvD', 1),
(461, 45, 'Redireccionamiento web', 'Estimados,\r\n\r\n¿Nos pueden ayudar con este redireccionamiento?\r\nconcierto.tabancura.cl a \r\nhttps://sites.google.com/tabancura.cl/concierto2025/inicio\r\n\r\nGracias!', 12, 5, 2, 7, NULL, 'Gerardo, redireccionamiento creado y funcionando.\nSaludos', '', '3LUKAurYwYRNxrPv', 1),
(462, 45, 'Redireccionamiento Web', 'Estimados,\r\n\r\nPor favor redireccionar prevencion.tabancura.cl a https://sites.google.com/tabancura.cl/test-ponografia\r\n\r\nGracias.', 12, 5, 2, 7, NULL, 'Redireccionamiento creado y validado.', '', 'b7EEErTxzStL0DFs', 1),
(463, 45, 'Licencia Office 365', 'Estimado,\r\n\r\nRequiero licencia de office 365 para los siguientes usuarios:\r\nMiguel Sandoval msandoval@tabancura.cl\r\nIda Mege imege@tabancura.cl\r\nJorge Toro jorgetoro@tabancura.cl\r\n\r\nQuedo atento, gracias.', 10, 5, 0, 7, 'EWEQW', '', '', 'o8eRug5ONIHJkqW7', 1),
(464, 45, 'Redireccionamiento web', 'Estimados,\r\n\r\nFavor redireccionar:\r\nferiadeciencias.tabancura.cl\r\na\r\n\r\nhttps://sites.google.com/tabancura.cl/feriadeciencias2024/charlas-científicas-feria-de-ciencias-2025\r\n\r\nDe antemano gracias.', 12, 5, 2, 7, NULL, 'Se realiza redirecionamiento estado validdo y funcionando', '', '5Np1dGX6GFVYUMC0', 1),
(465, 45, 'Problema borrado de notas', 'Estimados,\r\n\r\nDos profesores me indicaron el mismo error, de distintos cursos y me preocupé, por lo mismo les pido si nos ayudan a dilucidar que pudo pasar:\r\nEl profesor Antoni Avendaño comenta que ayer jueves de 14:50 a las 15:30 puso notas en \r\n2A Social Studies\r\nHoy en la mañana no estaban. Se percató previamente que en 2 ocasiones que se habían borrado por lo que probó 3 veces con distintos dispositivos, volviendo a iniciar sesión con distintas redes de internet.\r\n__\r\nLo mismo le ocurrió el día martes, miércoles y jueves con la asignatura\r\n2A Science.\r\n\r\nEl problema estuvo en la nota 18 (segundo semestre, 3era informativa), ya lo ingresó nuevamente y ahora si se guardaron.\r\n\r\n__\r\nMismo caso ocurrió con Rodrigo Valenzuela del 2C English\r\nSegundo semestre, 3era informativa, Nota 18, ya la ingresó nuevamente.\r\n\r\nNo sé si tiene algo que ver, pero ayer entre las 10:50 y 11:30 (aprox, quizás fue antes) no pudimos ingresar al sistema, no cargaba la página.\r\n\r\nQuedo atento a sus comentarios, de antemano gracias.', 2, 5, 2, 6, NULL, '', '', 'fe4AQeE6SbRYObex', 1),
(466, 45, 'Problemas con leccionarios', 'Estimados,\r\n\r\nAgradezco que, por favor, nos puedan ayudar a solucionar un problema con la exportación de los leccionarios. En SIAE 2.0 no resulta exportar el archivo y en ambas versiones, al descargarlo para imprimir el archivo sale con múltiples errores, especialmente en los tildes y caracteres especiales, lo que dificulta muchísimo la lectura.\r\n\r\nMuchas gracias.\r\n\r\nSaludos,', 2, 5, 2, 6, NULL, '', '', 'YO9ZchbGwj6oPq65', 1),
(467, 45, 'Rol Coordinadores', 'Estimados,\r\n\r\nLes escribo para solicitar su ayuda para poder configurar el perfil de coordinador en SIAE.\r\nLa idea es que pueda acceder a anotaciones, testamentos e informes de notas de los estudiantes de todo el colegio, ¿nos pueden colaborar con esto?, de antemano gracias.\r\n\r\nQuedo atento a sus comentarios', 2, 5, 2, 6, NULL, '', '', 'mWa8LOKAU6VzLjBO', 1),
(468, 42, 'Perfil SIAE Macarena Errazuriz', 'Permitir estos accesos\r\n- hacer cambios en los datos de los apoderados (teléfonos, correos, etc)\r\n- hacer envíos de mails a través del SIAE', 2, 5, 0, 6, NULL, '', '', 'OAetoMVb1zMAbI6L', 1),
(469, 42, 'Larga Distancia no funciona', 'Da mensaje de telefono no autorizado al tratar de hacer llamadas', 4, 5, 2, 7, NULL, 'Alejandro, el servicio ya se encuentra vaidado para llamadas internacionales', '', 'HknEDBZIWDqpV5v2', 1),
(470, 42, 'Desarrollar utilitario de calculo de horas', 'Hacer calculadora de horas lectivas / cronológicas similar a SeducServicios pero standAlone para entregar a los colegios', 1, 5, 0, 6, NULL, '', '', 'Qxwb7Iqs32QvDMWx', 1),
(471, 42, 'Carga Cursos Históricos en BUK', 'Carga cursos den BUK a partir de archivo histórico REX+', 17, 5, 2, 42, 'Cargar los datos', '', '', 'R95COaLmaoiJazPS', 1),
(472, 42, 'Cambio te técnico', 'Permitir que el administrador cambie el tecnico asignado antes de iniciar un ticket', 11, 5, 2, 8, NULL, 'Se implementó un botón en la tabla de tickets para gestionar la asignación de técnicos.\n\n.Si el tick', '', 'faYRpy5JG3ZbuG1u', 1),
(473, 42, 'Migración Curso DEC a plataforma LMS BUK', 'Crear curso DEC en BUK', 17, 5, 2, 42, 'Crear Curso', '', '', '7Nk5PJVNSmQA51mk', 1),
(474, 42, 'Al asignar tecnico oblig a llenar campos innecesarios', 'Quitar la obligatoriedad de llemar el campo comentario técnico al asignar técnico', 11, 5, 3, 8, NULL, 'ahora ya no es necesario dar un comentario para aignar tecnico', '', 'vkEsxd6X2A9U1lmb', 1),
(475, 42, 'Bloqueo WebPay', 'Bloquear pago de matricula por webpay cuando existan cheques en cartera. Desbloquear solo 6 disas posterior al vencimiento del último cheque', 2, 5, 1, 6, NULL, '', '', 'kpRm0BlvTZm2GoPd', 1),
(476, 42, 'Crear Correo etreligión@seduc.cl', 'Crear correo indicado', 7, 5, 2, 7, NULL, 'Correo creado e informado a usuario', '', 'ws0cQxuXIGPPW267', 1),
(477, 42, 'Asignar permisos a Carolina martinez', 'acceso al Moodle de Religión de nuestras 4 carpetas: 7°, 8°, I° y II° Medio con este nuevo correo: etreligionseduc@gmail.cl que está asignado a Carolina martinez', 8, 5, 2, 42, NULL, '', '', 'B1Y5pP4Z4PSZhzC9', 1),
(478, 42, 'Corregir correo de Pilar Valdivieso', 'pvaldivieso@colegiolosalerces.cl', 9, 5, 1, 42, NULL, '', '', 'nPvvCI7ShciYCiTr', 1),
(479, 42, 'Plantilla de evaulacion de software', 'Desarrollar plantilla de evaluación de software', 10, 5, 2, 42, 'Hacer planilla', '', '', 'nY9XlIWxNAsVBUcM', 1),
(480, 42, 'Comentario en atenciones de enfermerí', 'Permitir agregar un comentario en la atención de enfermería luego de ser grabada', 2, 5, 2, 6, NULL, '', '', 'xVppkQyrqEN4V0JQ', 1),
(481, 42, 'Enviar notificación a TENS', 'Enviar notificación de correo a TENS correspondiente cuando se modifica una ficha médica', 2, 7, 2, 6, NULL, NULL, '', 'uHXgFJUKqAggXuYj', 1),
(482, 42, 'Crear correos enfemería', 'Crear correos para enfermeria@xxxxxx  para los colegios: Huinganal, Hulén Cantagallo y Cordillera', 7, 5, 2, 7, NULL, '', '', 's9mdKtfJzEKsLa85', 1),
(483, 42, 'Carga alumnos nuevos', 'Carga alumnos en Follett', 9, 5, 1, 42, NULL, '', '', 'LFadOwKXZDHnEwEH', 1),
(484, 42, 'Separar anotaciones positivas y negativas (Mostrar 2 numeros)', 'Separar las sume de anotaciones en 2: (Positivas y negativas)', 2, 5, 0, 6, NULL, '', '', 'JpWhGpruhCxeqZ6X', 1),
(485, 42, 'Creación Correos para plataforma evaluación', 'Crar correo evaluación@colegioxxx.cl  para todos los colegios menos los alerces.', 7, 5, 2, 7, NULL, '', '', '2U8VYKyG72AFOpOH', 1),
(486, 42, 'Corregir ortografía SIAE', 'En pantalla inicial SIAE 2.0\r\n\r\nDice \"Asignatura que han bajado el promedio\"  agregar s a Asignatura \"Asignaturas\"\r\nDice ACADéMICA, poner la E con tilde en Mayuscula \"ACADÉMICA\"\r\nDice ENFERMERÏA, poner tilde \"ENFERMERÍA\"', 2, 5, 3, 6, NULL, '', '', 'teevmGluMV1J8xaH', 1),
(487, 42, 'Crear licencias Zipgrade', 'Crear licencias Zipgrade con los correos evaluaciones@colegioxx', 10, 5, 1, 42, 'Asignar licencias a correos indicados', 'Licencias OK para todos los colegios', '', 'Fz4th0D0XpYGnX2s', 1),
(488, 42, 'COnfigurar Ensayo PAES HISTORIA', 'Configurar EPHC1', 10, 5, 1, 42, 'Asignar', '', '', 'fjdhDRMMiJ2apsIl', 1),
(489, 42, 'Permitir adjuntar archivos en comunicaciones de la App de padres', 'El solicitar un retiro o inasistencia permitir adjuntar archivos', 2, 5, 2, 6, NULL, '', '', 'NnsZZd1ivpwcWwwF', 1),
(490, 42, 'Adjuntar archivos en módulo de enfermería', 'Permitir adjuntar recetas medicas en la ficha del alumno (recetas)', 2, 5, 2, 6, NULL, '', '', 'k2CLa1UTnk8amZsx', 1),
(491, 42, 'Corregir módulo de calendarpi en SIAE 2.0', 'Terminar implementación modulo calendario en SIAE', 2, 5, 2, 6, NULL, '', '', 'tErTdoS8LmEYU6eS', 1),
(492, 42, 'Funcionalidad de citas de entrevistas', 'Implementar funcionalidad de citas de entrevistas en SIAE', 2, 7, 2, 6, NULL, NULL, '', 'LpJ2hYeDWB8JJAdt', 1),
(493, 42, 'Enviar contraseña SIAE', 'Crear cuenta patricia Subercaseaux', 6, 5, 2, 7, NULL, '', '', '57Glj4N2uh1s6MMo', 1),
(494, 42, 'Usuario SIAE', 'Usuario SIAE para Dora Oyerse', 6, 5, 2, 7, NULL, '', '', 'fMpDPTjYh7hIhjdG', 1),
(495, 42, 'Geolocalizar Familias Nuevas', 'Geolocalizar Familias Nuevas', 10, 5, 1, 42, '', '', '', 'IS3ruuuG9SPy8PrX', 1),
(496, 42, 'Descargar Familias por colegio', 'Descargar Familias por colegio', 2, 5, 0, 42, '', '', '', 'j4CQ28oxCENEWNdB', 1),
(497, 6, 'Envío mail Oxford', 'DESDE: SEDUC Informa, con plantilla de Seduc y el logo que incluye el texto, a la derecha.\r\nPARA: papá y mamá (HIJO MENOR) de todos los colegios, incluyendo Cantagallo.', 2, 5, 2, 6, NULL, '', '', 'vRoPusECzvAstlEh', 1),
(498, 6, 'Enviar email Alumni', 'De acuerdo a lo conversado, le envío el listado de los egresados que debieran recibir el correo cargado por Diego Barrios en el sistema SIAE esta mañana.\r\nAsunto: De egresados a apoderados, bienvenidos a casa.', 2, 5, 2, 6, NULL, '', '', 'MakryG1TzZJt6cNx', 1),
(499, 42, 'Agrupar visión de todos los hijos de una familia', 'Permitir ver todoas los hijos de una familia en SEDUC o Colegios, lo que sea mas facil', 2, 7, 2, 6, NULL, NULL, '', 'HhnZ3dC0cCg7zcau', 1),
(500, 42, 'pantalla de configuracion de envios comunicaciones', 'Permitir que los envis de comunicaciones via app de padres se puedan configurar por colegio los destinatarios de la comunicación', 2, 5, 2, 6, '', '', '', 'WjoTZ1lWqAJ8xheo', 1),
(503, 42, 'Verificar usuario y enviar credenciales', 'Verificar usuario de Catalina Irarrazaval cirarrazaval@colegiolosandes.cl y enviarle enlace y credenciales', 2, 5, 2, 6, '', '', '', 'jVPoYhrlYGiF5aR8', 1),
(504, 42, 'Usuarios Office 365', 'Crear usuarios Office 365 para María Paz Correa y María Luisa pavic', 6, 5, 2, 7, NULL, 'Cuentas creadas y ambas contraseña inicial: Seduc.2026', '', 'aLFtrE2BaMeSjlOa', 1),
(505, 42, 'Formulario de Inventario de Herramientas Academicas', 'Crear formulario y compartir Colegios', 10, 5, 0, 42, '', '', '', 'x8ppm2MhVoTatP94', 1),
(506, 6, 'Envio email Seguro uandes', 'Enviar email por colegio mas colaboradores colegios y seduc', 7, 5, 0, 7, NULL, 'enviado', '', 'Pp7BuTD2jORjVKNv', 1),
(507, 6, 'Informe descuentos 2025', 'Informe descuento 2025 para anuario\r\n- familias con descuento\r\n- numero de alumnos\r\n- descuento promedio', 2, 5, 2, 6, NULL, '', '', 'RejvAPcSoSnpxQGY', 1),
(508, 42, 'Permitir eliminar reserva de salas', 'Permitir eliminar reserva de salas de SIAE', 2, 5, 2, 6, NULL, '', '', 'Lmg4neiymFnoISeJ', 1),
(513, 42, 'Cambiar período de prestamo', 'Bajar prestamos de 2 a 1 semana', 9, 5, 0, 42, NULL, '', '', '1FNMru4zQVZisnQC', 1),
(514, 42, 'Copiar una prueba a otro curso del mismo nivel', 'Permitir la copia de un prueba de un curso a otro del nivel', 2, 5, 2, 6, NULL, '', '', 'NzUAxCqMSDk3L6uj', 1),
(515, 42, 'pantalla de ingreso test FLO', 'Desarrollar pantalla de ingreso test FLO de acuerdo a formulario', 10, 5, 2, 8, '', '', '', 'Y69QcUzJQkJPKigl', 1),
(520, 8, 'Capacidad de poder Eliminar un Ticket', 'Crear la lógica para eliminar un ticket (solo perfil admin)', 11, 5, 2, 8, NULL, 'Se implementa módulo Eliminar Ticket.\nLa eliminación cambia el estado del ticket, por lo que no se m', '', 'MztP6Ubv4iFbPocU', 2),
(521, 42, 'Carga Correos Follett', 'Cargar Correos de Padre y Madre en Follett', 9, 5, 1, 42, NULL, 'Correos cargados en Campos email2 e email3 respectivamente', '', 'HkajZfjvCzTZs8zd', 1),
(523, 7, 'Facturar Instrumentos Musicales CPA2', 'Facturar taller instrumentos musicales CPA2, segun planilla enviada en correo', 10, 5, 2, 7, 'Solicitare archivo de facturacion a ROLIVA', 'Se realizo con exito carga de archivo de facturacion a sistema Fin700', '', '2m5uz9Xhu0FcUI7n', 1),
(524, 7, 'Requerimeinto ptos de red - Huelén', 'Se solicita visita a Colegio Huelen, para ver requerimeinto de nuevos puntos de red en salas', 3, 5, 2, 7, NULL, '', '', 'YdA0kiBPse7T7JtH', 1),
(527, 8, 'sistema calculo de horas docentes', 'Crear un sistema de cálculo de horas docentes que permita registrar y administrar la distribución horaria semanal de funcionarios por colegio. El sistema deberá permitir cargar horarios de lunes a viernes (mañana y tarde), validar la consistencia de los tramos horarios y calcular automáticamente el resumen de horas cronológicas, lectivas y no lectivas. Además, deberá generar un listado de funcionarios que permita revisar, modificar y mantener trazabilidad de cada registro, considerando dos perfiles de acceso: Super admin (visualiza todos los colegios) y Administrador hora colegio (solo funcionarios de su colegio).', 10, 5, 1, 8, '', '', '', 'PjvHbrYxrLTJCLbt', 1),
(528, 8, 'modulo Inventario', 'Se solicita crear un módulo de Inventario de Software que permita administrar las distintas licencias y páginas web utilizadas por la organización. El módulo deberá permitir registrar información relevante como tipo de software, licencias, responsable del sistema, datos sensibles asociados al licenciamiento (credenciales o información de acceso), proveedor y observaciones. El objetivo es centralizar la información y facilitar la gestión, control y seguimiento de los sistemas y servicios utilizados.', 11, 5, 1, 8, NULL, '', '', 'hoOzi1KDsBD89Js0', 1),
(529, 42, 'Cambir boton pantalla inicial \"Acceso Colegio\" por Acceso Profesores y Funcionarios', 'Cambir boton pantalla inicial \"Acceso Colegio\" por Acceso Profesores y Funcionarios', 2, 5, 2, 6, NULL, '', '', 'ILhkDFG2zDXk7XOJ', 1),
(530, 7, 'Renoavcion de Dominios', 'Se solicta renovar x 3 años dominios de seduc y sus colegios que estan por vencer', 12, 5, 2, 7, NULL, 'Se realizo renovacion de todos los dominios hasta año 2029', '', '7QzEHzYnU0Tzfwsn', 1),
(533, 7, 'Facturar Instrumentos Musicales CPA4', 'Facturar instrumentos musicales CPA4 con archivo enviado por correo', 10, 5, 2, 7, '', '', '', 'AYdgxfJjl8e1Ptwo', 1),
(551, 42, 'Listado de uso de imagen', '<p>Generar listado de padres que autorizaron y no autorizaron uso de imagen y listado que quienes no lo han hecho</p>', 2, 5, 2, 6, NULL, '', '', 'bCjMySYvjz0umJDy', 1),
(562, 42, 'Cita entrevista \"solicitada por padres\"', '<p>Agregar motivo indicado en cita a entrevista</p>', 2, 5, 2, 6, NULL, '', '', 'fMu02aljx3UYkkj3', 1),
(563, 42, 'Notificaciones Push', '<p>Implementar notificaciones Push en la APP</p>', 2, 7, 2, 6, NULL, NULL, '', 'pPQNqKR8Nk3ZfFqo', 1),
(565, 42, 'Compra Notebooks Huinganal', '<p>Compra 30 equipos Huinganal para Oxford</p>', 6, 5, 2, 7, '', '', '', 'yvOUZu2dbQzlLkk7', 1),
(566, 42, 'Inconsistencia en anotaciones', '<p>Reportes de anotaciones por alumno y por curso presentan inconsistencias. Además arreglar tildes en reporte por curso</p>', 2, 5, 2, 6, NULL, '', '', 'F85rjN9SbnoDjpFW', 1),
(567, 42, 'Exportar Familias en 2.0', '<p>Agregar opción de Exportar en SIAE 2.0</p>', 2, 7, 2, 6, NULL, NULL, '', 'AgdHVA5yYqG8xem4', 1),
(568, 42, 'Tildes en listado de Ex Alumnos (Familia)', '<p>Listado ex alumnos aparece con set de caracteres incorrecto</p>', 2, 5, 2, 6, NULL, '', '', 'YOzRJu68ovXKVOBx', 1),
(569, 42, 'Dashboard de Tutorías', '<p>Dashboard de tutorías de acuerdo a especificaciones</p>', 2, 7, 2, 6, NULL, NULL, '', 'QyO2wETtv2NxO8wc', 1),
(570, 42, 'Dashboard de entrevistas', '<p>Dashboard de entrevistas según especificaciones</p>', 2, 7, 2, 6, NULL, NULL, '', '7UNYWUkMXHWt3icG', 1),
(571, 42, 'Politica de revisión de contraseñas', '<p>Politica de revisión de contraseñas para efectos de CIS 18 Ciberseguridad</p>', 10, 7, 1, 42, '', NULL, '', '4sMz6L0qrJTzoLrU', 1),
(572, 42, 'Politica de creación de contraseñas', '<p>Politica de creación de contraseñas Para CIS 18 ciberseguridad</p>', 10, 7, 1, 42, '', NULL, '', 'vghSELerKlrDwZt5', 1),
(573, 42, 'Revisar acceso de profesores asignatura a ver todas las anotaciones', '<p>Revisar acceso de profesores asignatura a ver todas las anotaciones</p>', 2, 7, 2, 6, NULL, NULL, '', 'yD7I9KCoIEONs0Wn', 1),
(574, 42, 'Asistente IA', '<p>Permitir marcar no volver a mostrar </p>', 2, 5, 2, 6, NULL, '', '', 'IWvmUe1B1cPrHaN3', 1),
(575, 42, 'Alumnos 7° a IV', '<p>Exportar Alumnos 7° a IV</p>', 2, 5, 1, 42, '', '', '', 'uV9A2mlUWlWkPEnt', 1),
(576, 6, 'BBDD Seduc para Consultoría', '<p>Hola Ramón, tal como conversamos en relación a la BDD que necesitamos como Seduc para avanzar en una consultoría, tomando en cuenta la reunión que tuve con ellos por este tema, a continuación te detallo el requerimiento de esta BDD.</p><p><br></p><p><br></p><p><strong><u>Características y criterios para armar las bases de datos</u></strong></p><p><br></p><p><span style=\"color: rgb(0, 0, 0); background-color: transparent;\">Se necesitan:</span></p><ul><li><span style=\"background-color: transparent;\">Mínimo 240 contactos. Entre más grande la base de contactos, más probable es completar la muestra.</span></li><li><span style=\"background-color: transparent;\">120 con hijos en colegios femeninos y 120 con hijos en colegios masculinos.</span></li><li><span style=\"background-color: transparent;\">Apoderados: 50% hombres y 50% mujeres</span></li><li><span style=\"background-color: transparent;\">Apoderados de Pre kinder y kinder (ojalá 50% de cada nivel)</span></li><li><span style=\"background-color: transparent;\">Apoderados que postularon el 2026 y sus hijos ingresarán al colegio el 2027 (máximo 40% de los contactos totales)</span></li></ul><p><br></p><ol><li><span style=\"background-color: transparent;\">Información que tiene que venir en la base:</span></li></ol><ul><li><span style=\"background-color: transparent;\">ambos apoderados (padres y madres)</span></li><li><span style=\"background-color: transparent;\">indicar los que son padres de un mismo niño/a&nbsp;</span></li><li><span style=\"background-color: transparent;\">colegio en el que tiene a su hijo.&nbsp;</span></li><li><span style=\"background-color: transparent;\">nivel o curso del que es apoderado (pre kinder o kinder)</span></li><li><span style=\"background-color: transparent;\">teléfono de contacto</span></li><li><span style=\"background-color: transparent;\">mail personal</span></li><li><span style=\"background-color: transparent;\">comuna de residencia</span></li><li><span style=\"background-color: transparent;\">hijos en otros colegios y curso en el que están</span></li><li><span style=\"background-color: transparent;\">colegio del que el apoderado es ex alumno (deseable)</span></li></ul><p><br></p><ol><li><strong style=\"background-color: transparent;\">Requisitos de los contactos de la base apoderados</strong></li></ol><ul><li><span style=\"background-color: transparent;\">Apoderados que NO sean exalumnos de colegios Seduc, es decir, apoderados de familias nuevas</span></li><li><span style=\"background-color: transparent;\">Apoderados de los cursos Kinder y Pre-Kinder en colegios femeninos</span></li><li><span style=\"background-color: transparent;\">Apoderados de 1ro y 2do básico en colegios masculinos. Podrían incluirse apoderados de colegios masculinos con hijos en kinder y pre kinder, pero solo se van a contactar si no se logra tener las cuotas de participantes definidas.</span></li><li><span style=\"background-color: transparent;\">Podrían tener hijos en colegios Seduc pero hijos que estén actualmente máximo en 8° básico (14&nbsp; años)</span></li><li><span style=\"background-color: transparent;\">Que no sean papás que participan del Centro de padres&nbsp;</span></li></ul><p><br></p><p><br></p><p>Esta base debemos poder entregarla a más tardar el martes 12/5/26. ¿puedes tenerla en esa fecha?</p><p><br></p><p>Cualquier duda me comentas</p><p><br></p><p>Muchas gracias</p>', 2, 5, 2, 6, NULL, '', '', 'fyWYGrNSbGaXARLM', 1),
(577, 6, 'Cargar presupuesto 2025', '<p><span style=\"color: rgb(34, 34, 34);\">&nbsp;</span><strong style=\"color: rgb(34, 34, 34);\">presupuesto 2025 de los gastos operacionales de los colegios</strong><span style=\"color: rgb(34, 34, 34);\">. T</span></p>', 2, 5, 2, 6, NULL, '', '', '58CXyEfOt28TGpoJ', 1),
(578, 42, 'LIbretas Preescolar', '<p>CArga indicadores preescolar en SIAE</p>', 2, 5, 2, 6, NULL, '', '', 'er90XDNKhfiDiJxS', 1),
(579, 42, 'sociograma de 4° básico a IV medio', '<p>En la primera pregunta del autorreporte, nos gustaría que en la descripción de la pregunta especificar:</p><p>Responde las siguientes preguntas considerando que</p><p>1 = Muy mal</p><p>2 = Mal&nbsp;</p><p>3 = Más o menos</p><p>4 = Bien&nbsp;</p><p>5 = Muy bien&nbsp;</p><p><br></p><p>En la primera pregunta agregar acento: En general, ¿<strong>cómo</strong> te has sentido?</p><p>La segunda está ok</p><p>En la tercera separar las palabras: <strong>resultado organizarte&nbsp;</strong></p><p><strong>Eliminar la pregunta ¿cuánto sientes que has aprendido?</strong></p><p><br></p><p>En cuanto al resto de las preguntas, te pedimos que sea una sola pregunta por página.</p><p><br></p><p>Selecciona el nombre de una compañera que crees que puede sentirse sola o lo esté pasando mal en tu curso: <strong>Agregar en las opciones : Ninguna&nbsp;</strong></p><p><br></p><p>¿Hay alguna compañera que observes que te moleste a ti o a las demás frecuentemente? <strong>Agregar en las alternativas de respuesta: Ninguna&nbsp;</strong></p><p>En esta misma pregunta ponerla con el mismo formato de las anteriores. Está con un formato distinto.</p><p><br></p><p>Respecto a los comentarios finales, también que estén en páginas separadas y que tengan 200 carácteres de respuesta.&nbsp;</p><p>Además partir con mayúscula: <strong>¿Qué</strong> consideras que podrían mejorar como curso? ¿Hay algo que te preocupe, incomode o que te gustaría mejorara dentro de éste?</p><p>Respecto a la segunda, agregar acentos: ¿Hay algo <strong>más</strong> que quieras contar con respecto a <strong>cómo</strong> te sientes o algo de lo que creas que te podemos ayudar?&nbsp;</p><p><br></p><p>Quedaría pendiente revisar el sociograma de 1° a 3° básico&nbsp;</p><p><br></p><p>Quedamos atentas a cualquier duda o comentario,&nbsp;</p><p>Cariños&nbsp;</p>', 10, 5, 1, 42, '', '', '', 'lD6UxbXkwL3sioiz', 1),
(580, 42, 'Encuesta Homologación', '<p>Ingresar encuesta según correo MP Correa</p>', 19, 5, 1, 42, NULL, '', '', 'qMXdgaKPZFFfyTNm', 1),
(581, 42, 'Carga alumno Zipgrade', '<p>Carga alumnos basicca todos los colegios en Zipgrade</p>', 10, 5, 1, 42, '', '', '', 'FrkDXAQkiaHDe8cM', 1),
(582, 42, 'Pauta Ensayos Simce', '<p>Cargar pautas ensayo simbe</p>', 10, 5, 1, 42, 'Carga de acuerdo a pauta emviada', '', '', 'TdP0do02ed200j8v', 1),
(583, 6, 'Encuesta de capacitación 2026', '<p><br></p><h3><strong style=\"color: rgb(31, 31, 31);\">Constanza Nieto</strong></h3><p><br></p><p>Estimado Ramón,</p><p>De acuerdo a lo conversado, adjunto cambios a incorporar en la encuesta de capacitación de este año, en el mismo formato que el año pasado. Hay algunos cursos que desaparecen, no se van a incluir en la encuesta 2026, sin embargo, la idea es que las personas que tenían puntaje en ellos hasta 2025 lo mantengan.</p><p><br></p><p>Después de revisarlo podemos juntarnos para aclarar dudas.</p><p><br></p><p>Saludos y gracias,</p>', 2, 2, NULL, 6, NULL, NULL, '', 'iOVFEsdJuj4OEyls', 1),
(584, 42, 'Error Reserva de Salas Cantagallo', '<p>Salas reservadas se sobreponen varias reservas</p>', 2, 2, NULL, 6, NULL, NULL, '', 'cRso5DEBXee8VsGL', 1),
(585, 42, 'Sitio WEB Cantagallo', '<p>Recuperar estructura y carga datos nuevos Cantagallo</p>', 12, 7, 1, 7, NULL, NULL, '', 'tZV7jSP0QR9Ikk4p', 1),
(586, 42, 'Porteria Cordillera', '<p>Crear Usuario Porteria Cordillera</p>', 2, 5, 2, 6, NULL, 'el usuario lo debe crear el colegio, ya que deben usar email creado en el colegio.\ny deben asignar t', '', '4a47TCHl8InSCkHo', 1),
(587, 42, 'Usuario Congreso Integra', '<p>Crear usuario Correo Congreso Integra</p>', 7, 5, 2, 7, NULL, '', '', 'vPUHl0zNAVMafug9', 1),
(589, 42, 'Doble Factor de Autenticación', '<p>Agregar el envío de un código por correo al ingresar al sistema</p>', 11, 7, 1, 8, NULL, NULL, '', 'T376pZppjfD5xdsN', 1),
(590, 42, 'Revisión reporte Follett', '<p>ntento hacer un reporte histórico pero en los enriched report no me carga la data</p><p>Anamaría Rivera</p>', 9, 5, 1, 42, NULL, '', '', 'TvS08dMs9iD1gn12', 1),
(591, 42, 'Listado Usuarios Follett', '<p>Listado de usuarios para revisión</p>', 9, 5, 2, 42, NULL, '', '', 'r6GTus1aBHnCSeT8', 1),
(592, 42, 'Listado Usuarios Colexia', '<p>Listado de usuarios para revisión</p>', 19, 7, 1, 42, NULL, NULL, '', 'bsin0oPOqE0d1MJP', 1),
(593, 42, 'Estructura de revisión de cuentas en Colexia', '<p>Establecer una estructura de revisión de usuarios en Colexia</p>', 19, 5, 1, 42, NULL, '', '', '7kEHzvtLKALwbIzc', 1),
(594, 42, 'Listado Usuarios FIN700', '<p>Listado Usuario FIN700 Para Revisión</p>', 10, 5, 2, 7, '', '', '', 'X9QkCNOIXvahW3AO', 1),
(595, 42, 'Listado Usuarios SIAE', '<p>Listado de usuarios SIAE para revisión</p>', 2, 2, NULL, 6, NULL, NULL, '', 'KbBWDBLigM0SCf6u', 1),
(596, 42, 'Listados de Asistencia', '<p>Agregar listados de asistencia en SIAE 2.0</p>', 2, 2, NULL, 6, NULL, NULL, '', 'LBiLFyWjjkwm6Wql', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_conversaciones`
--

CREATE TABLE `ticket_conversaciones` (
  `id` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `emisor` int(11) NOT NULL,
  `receptor` int(11) NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `adjunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `leido` int(11) DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT '0',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ticket_conversaciones`
--

INSERT INTO `ticket_conversaciones` (`id`, `id_ticket`, `emisor`, `receptor`, `mensaje`, `adjunto`, `fecha`, `hora`, `leido`, `eliminado`, `creado_en`) VALUES
(180, 385, 7, 31, 'enviare a la brevedad la info', NULL, '2025-07-08', '16:06:17', 1, 0, '2025-07-09 01:06:17'),
(181, 385, 31, 7, 'ideal si puedes hoy mismo', NULL, '2025-07-08', '16:07:20', 1, 0, '2025-07-09 01:07:20'),
(182, 385, 7, 31, '', 'archivos/adjuntosConversacionTicket/1752005369_5237774012.pdf', '2025-07-08', '16:09:29', 1, 0, '2025-07-09 01:09:29'),
(219, 394, 45, 7, 'Gracias!', NULL, '2025-07-15', '10:54:13', 1, 0, '2025-07-15 19:54:13'),
(218, 394, 7, 45, 'Hola Gerardo, lo comenzare, te aviso si necesito algo adicional', NULL, '2025-07-15', '10:52:41', 1, 0, '2025-07-15 19:52:41'),
(220, 394, 7, 45, 'Listo', NULL, '2025-07-15', '11:04:38', 1, 0, '2025-07-15 20:04:38'),
(217, 391, 7, 31, 'kkkkkk', NULL, '2025-07-11', '14:48:31', 1, 0, '2025-07-11 23:48:31'),
(216, 391, 31, 7, 'qq', NULL, '2025-07-11', '14:47:39', 1, 0, '2025-07-11 23:47:39'),
(215, 391, 7, 31, 'dime', NULL, '2025-07-11', '14:47:15', 1, 0, '2025-07-11 23:47:15'),
(214, 391, 31, 7, 'hola manuel', NULL, '2025-07-11', '14:47:09', 1, 0, '2025-07-11 23:47:09'),
(254, 588, 8, 8, 'que sucede ?', NULL, '2026-09-08', '10:30:20', 1, 0, '2026-09-08 18:30:20'),
(253, 588, 8, 8, 'hola', NULL, '2026-09-08', '10:29:55', 1, 0, '2026-09-08 18:29:55'),
(235, 434, 7, 45, 'Gerardo, ya esta creada la cuenta', NULL, '2025-09-04', '10:36:57', 1, 0, '2025-09-04 19:36:57'),
(236, 434, 7, 45, 'Nombre para mostrar: Ida Mege Nombre de usuario: imege@tabancura.cl ContraseÃ±a: Tabancura.2025', NULL, '2025-09-04', '10:37:02', 1, 0, '2025-09-04 19:37:02'),
(237, 434, 7, 45, 'Nombre de usuario: amuzard@tabancura.cl ContraseÃ±a: Tabancura.2025', NULL, '2025-09-04', '11:36:23', 1, 0, '2025-09-04 20:36:23'),
(238, 453, 33, 7, 'Hola Manuel, como va todo?', NULL, '2025-10-14', '12:29:15', 1, 0, '2025-10-14 20:29:15'),
(239, 453, 33, 7, 'si necesitas alguna info adicional me la pides nomas', NULL, '2025-10-14', '12:29:33', 1, 0, '2025-10-14 20:29:33'),
(240, 460, 7, 45, 'Gerardo, cual de estas se puede borrar ya que solo me permite tener 10 habilitadas', NULL, '2025-10-28', '14:01:14', 1, 0, '2025-10-28 22:01:14'),
(241, 460, 7, 45, '', 'archivos/adjuntosConversacionTicket/1761670897_reglas_tabancura.png', '2025-10-28', '14:01:37', 1, 0, '2025-10-28 22:01:37'),
(242, 469, 7, 42, 'Alejandro, se llamo a Soporte Entel y se valido el SLD', NULL, '2026-01-08', '09:48:57', 0, 0, '2026-01-08 18:48:57'),
(243, 469, 7, 42, 'existia unn tipo de bloqueo especifico que no era tecnico sino comercial segun informaron', NULL, '2026-01-08', '09:49:27', 0, 0, '2026-01-08 18:49:27'),
(250, 520, 8, 8, '', 'archivos/adjuntosConversacionTicket/1774440419_modulo_eliminar_ticket_02.png', '2026-03-25', '09:06:59', 1, 0, '2026-03-25 17:06:59'),
(249, 520, 8, 8, '', 'archivos/adjuntosConversacionTicket/1774440409_modulo_eliminar_ticket_01.png', '2026-03-25', '09:06:49', 1, 0, '2026-03-25 17:06:49'),
(248, 520, 8, 8, '', 'archivos/adjuntosConversacionTicket/1774440383_modulo_eliminar_ticket_00.png', '2026-03-25', '09:06:23', 1, 0, '2026-03-25 17:06:23'),
(251, 520, 8, 8, '', 'archivos/adjuntosConversacionTicket/1774440429_modulo_eliminar_ticket_03.png', '2026-03-25', '09:07:09', 1, 0, '2026-03-25 17:07:09'),
(252, 520, 8, 8, '', 'archivos/adjuntosConversacionTicket/1774440440_modulo_eliminar_ticket_04.png', '2026-03-25', '09:07:20', 1, 0, '2026-03-25 17:07:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_pc_catalogo`
--

CREATE TABLE `tipo_pc_catalogo` (
  `id_tipo_pc` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_pc_catalogo`
--

INSERT INTO `tipo_pc_catalogo` (`id_tipo_pc`, `nombre_tipo`, `activo`) VALUES
(1, 'Desktop', 1),
(2, 'Notebook', 1),
(3, 'Tablet', 1),
(4, 'Otro', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apellido_materno` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` int(11) DEFAULT NULL,
  `clave` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT '0',
  `id_area_trabajo` int(11) DEFAULT NULL,
  `foto` blob,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token_reinicio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identificador` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_admin_global` tinyint(1) DEFAULT '0' COMMENT '1 = es administrador global, 0 = no es admin global'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido_paterno`, `apellido_materno`, `fecha_nacimiento`, `email`, `telefono`, `sexo`, `clave`, `cargo`, `intentos_fallidos`, `id_area_trabajo`, `foto`, `estado`, `anexo`, `fecha_creacion`, `token_reinicio`, `identificador`, `es_admin_global`) VALUES
(1, 'Alex', 'Espinoza', '', '0000-00-00', 'aespinosa@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Analista de Recaudación', 0, 8, NULL, 'Activo', '304', '2024-04-10 13:36:23', '', 'xY8ziBWNWc1jmHg1', 0),
(2, 'Francisco', 'Chavez', '', '2024-07-03', 'fchavez@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Contador General', 0, 2, NULL, 'Activo', '306', '2024-04-10 14:01:47', '', 'dPjXIubfwJUb2t84', 0),
(3, 'Alvaro', 'Fuentes', '', '2024-04-26', 'afuentes@seduc.cl', '5551234567', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Analista Contable', 0, 2, NULL, 'Activo', '308', '2024-04-10 14:01:47', '', 'LtW8EHnCO4IeRsxa', 0),
(4, 'Rodrigo', 'Leon', '', '2024-12-11', 'rleon@seduc.cl', '5551234567', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Analista Contable', 0, 2, NULL, 'Activo', '319', '2024-04-10 14:01:47', '', '4ESjNPCq8eCfdb5H', 0),
(5, 'Victor', 'Perez', '', '2024-08-24', 'vperez@seduc.cl', '5551234567', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Analista Contable', 0, 2, NULL, 'Activo', '353', '2024-04-10 14:01:47', '', '6fO83HcKXoUgo0Jr', 0),
(6, 'Ramon', 'Oliva', 'Zenteno', '2024-05-18', 'roliva@seduc.cl', '5551234567', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Analista Programador', 0, 1, NULL, 'Activo', '359', '2024-04-10 14:01:47', '', 'TY26fLT3qMvZinOM', 0),
(7, 'Manuel', 'Gutierrez', '', '2024-01-09', 'mgutierrez@seduc.cl', '123456', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Jefe de Computación e Informatica', 0, 1, NULL, 'Activo', '322', '2024-04-10 14:01:47', '', 'iZTkQ4CKH6hTstQC', 0),
(8, 'Cristian', 'jorquera', 'Gonzalez', '1986-01-10', 'cjorquera@seduc.cl', '988302735', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Analista Programador', 0, 1, NULL, 'Activo', '358', '2024-04-10 14:01:47', '', 'q8ctAnYBSqmlvl20', 0),
(9, 'Gonzalo', 'Munoz', '', '2024-03-13', 'gmunoz@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Prevencionista de Riesgos', 0, 5, NULL, 'Activo', '302', '2024-04-10 14:01:47', '', 'JwfvBkHnCO3EVuy7', 0),
(10, 'Maria Jose', 'Sanchez', '', '2024-07-13', 'mjsanchez@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Director (a) de Comunicaciones y Marketing', 0, 9, NULL, 'Activo', '337', '2024-04-10 14:01:47', '', 'Lj8AnVmQZe4rTZ4f', 0),
(12, 'Francisco', 'Valenzuela', '', '2024-04-11', 'fvalenzuela@seduc.cl', '5551234567', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Administrador General de Remuneraciones', 0, 3, NULL, 'Activo', '314', '2024-04-10 14:01:47', '', 'TZ8x9X9I0Gf3lrYq', 0),
(14, 'Javier', 'Ureta', '', '2024-10-12', 'fjureta@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Director de Personas', 0, 7, NULL, 'Activo', '320', '2024-04-10 14:01:47', '', 'q8fElCWDd1koOHWr', 0),
(15, 'Beatriz', 'Herrera', '', '2024-04-28', 'bherrera@seduc.cl', '5551234567', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Coordinador General de Proyectos y Gestión', 0, 5, NULL, 'Activo', '354', '2024-04-10 14:01:47', '', 'cxVQhJDOZlCUuBmR', 0),
(16, 'Gisela', 'Friedl', '', '2024-09-19', 'gfriedl@seduc.cl', '5551234567', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Coordinador(a) de Sistemas de Personas', 0, 3, NULL, 'Activo', '356', '2024-04-10 14:01:47', '', '4zuzgo3T9TSyZ5j8', 0),
(17, 'Constanza', 'Nieto', '', '2024-07-23', 'cnieto@seduc.cl', '5551234567', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Coordinador(a) de Personas', 0, 3, NULL, 'Activo', '345', '2024-04-10 14:01:47', '', 'Bp6ajUrk8zhwDq2N', 0),
(18, 'Veronica', 'Alvarez', '', '2024-10-05', 'valvarez@seduc.cl', '5551234567', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Enfermera Coordinador(a)', 0, 4, NULL, 'Activo', '312', '2024-04-10 14:01:47', '', 'FQ0lAHCQa95QMdCe', 0),
(20, 'Magdalena', 'Ibanez', '', '2024-01-03', 'mibanez@seduc.cl', '5551234567', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Subdirector(a) de Formación', 0, 4, NULL, 'Activo', '350', '2024-04-10 14:01:47', '', 'DQ6RPpoBGpQPuNj4', 0),
(24, 'Oscar', 'Mercado', '', '2024-06-26', 'omercado@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Director de Administración y Finanzas', 0, 7, NULL, 'Activo', '334', '2024-04-10 14:01:47', '', '8DE9DzM2GbFFaHOP', 0),
(25, 'Yanelis', 'Reyes', '', '2024-08-16', 'yreyes@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Jefe(a) de Finanzas', 0, 8, NULL, 'Activo', '325', '2024-04-10 14:01:47', '', 'vRGGbKZzJKnvfAZ2', 0),
(26, 'Josefina', 'Mackenna', '', '2024-04-09', 'jmackenna@seduc.cl', '5551234567', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Jefe(a) de Administración ', 0, 8, NULL, 'Activo', '321', '2024-04-10 14:01:47', '', '30HjkyEoS3t3F344', 0),
(31, 'Javier ', 'Gonzalez ', 'COORDILLERA', '0000-00-00', 'jgonzalez@colegiocordillera.cl ', '24295900', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Secretaria', 0, 10, NULL, 'Activo', '24295900', '2024-05-10 16:27:17', '', 'JF0P1s1BO6RTIJkk', 0),
(33, 'Miguel', 'Ortuzar', 'HUINGANAL', '0000-00-00', 'mortuzar@colegiohuinganal.cl', '225921720', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Secretaria', 0, 10, NULL, 'Activo', '225921720', '2024-05-10 16:27:17', '', 'EIwgDbQz6FTgsnnx', 0),
(35, 'Catalina', 'Aspillaga', 'Finlay', '0000-00-00', 'caspillaga@seduc.cl', '32423423', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Dueña de casa y Secretaria', 0, 10, NULL, 'Activo', '301', '2024-06-10 19:15:56', NULL, NULL, 0),
(39, 'Francisco ', 'Gomez', 'Gomez', NULL, 'fgomez@seduc.cl', '5551234567', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Contador General', 0, 2, NULL, 'Activo', '328', '2024-04-10 14:01:47', NULL, 'TZ8x9X9I0Gf3lJgw', 0),
(41, 'Trinidad', 'Errazuriz', 'Errazuriz', '0000-00-00', 'terrazuriz@seduc.cl', '22157520', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Asesor(a) Equipo Técnico PEIS', 0, 4, NULL, 'Activo', '348', '2025-01-02 15:11:48', NULL, 'TZ8x9X9I0Gf3JHGr', 0),
(42, 'Alejandro ', 'Rojas', 'Schweitzer', '0000-00-00', 'arojas@seduc.cl', '', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', 'Jefe de Computación e Informatica', 0, 1, NULL, 'Activo', '332', '2025-02-26 15:02:00', NULL, 'q8ctAnYBSqmgSsQW', 0),
(43, 'Felipe', 'Guzman', '', '0000-00-00', 'fguzman@seduc.cl', '', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 7, NULL, 'Activo', '331', '2025-02-27 15:44:22', NULL, 'dPjXIubfwJUb2ew1', 0),
(44, 'Trinidad', 'Alamos', '', '0000-00-00', 'talamos@seduc.cl', '22157520', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 6, NULL, 'Activo', '', '2025-03-10 17:22:20', NULL, 'TZ8339I0Gf54rES', 0),
(45, 'Gerardo', 'Elgueta', '', '0000-00-00', 'gelgueta@tabancura.cl', '', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 11, NULL, 'Activo', '', '2025-05-05 16:36:49', NULL, '123x9X440Gf54rES', 0),
(46, 'Macarena', 'Urrutia', '', '0000-00-00', 'm.urrutia@huelen.cl', '', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 10, NULL, 'Activo', '', '2025-05-12 18:55:18', NULL, 'TZ8x9X12230Gf54r', 0),
(48, 'Samuel', 'Aranda', 'CORDILLERA', '2025-10-13', 'saranda@colegiocordillera.cl', '', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 11, NULL, 'Activo', '', '2025-10-13 19:07:35', '', 'xY8ziBWNWc12sHg1', 0),
(49, 'Leandro', 'Barrios', 'LOS ANDES', '2025-10-13', 'lbarrios@colegiolosandes.cl', '', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 1, NULL, 'Activo', '', '2025-10-13 19:45:49', '', NULL, 0),
(2476, 'Catalina', 'Irarrazaval', '', '0000-00-00', 'cirarrazaval@colegiolosandes.cl', '', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 10, NULL, 'Pendiente', '', '2026-03-12 14:02:56', '', NULL, 0),
(2488, 'Nicolas', 'Bernstein', '', '0000-00-00', 'nbernstein@colegiocordillera.cl', '', 0, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 4, NULL, 'Activo', '', '2026-04-08 20:04:07', '', NULL, 0),
(2492, 'Carlos', 'Gallardo', '', '0000-00-00', 'c.gallardo@huelen.cl', '', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 1, NULL, 'Activo', '', '2026-06-10 12:45:38', '', NULL, 0),
(2493, 'Elvira', 'Claro', '', '0000-00-00', 'eclaso@colegiolosandes.cl', '', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 1, NULL, 'Activo', '', '2026-06-10 18:50:39', '', NULL, 0),
(2495, 'Francisco', 'Salazar', '', '0000-00-00', 'fsalazar@tabancura.cl', '', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 4, NULL, 'Activo', '', '2026-06-11 20:17:39', '', NULL, 0),
(2496, 'katrin', 'Pendergrast', '', '0000-00-00', 'kprendergast@colegiolosalerces.cl', '', 2, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 11, NULL, 'Activo', '', '2026-06-18 19:02:21', '', NULL, 0),
(2497, 'Claudio', 'Ortega', '', '0000-00-00', 'cortega@colegiolosalerces.cl', '', 1, '$2y$10$KkRQRQ6rgG/r0.VEXEI5w.WAmm3squrTFuxQiFvZdg.J5I/6qFAKy', NULL, 0, 11, NULL, 'Activo', '', '2026-06-18 19:03:01', '', NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_colegio`
--

CREATE TABLE `usuario_colegio` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT '1' COMMENT '1 = activo | 0 = inactivo',
  `fecha_asignacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario_colegio`
--

INSERT INTO `usuario_colegio` (`id`, `id_usuario`, `id_colegio`, `id_perfil`, `estado`, `fecha_asignacion`) VALUES
(1, 6, 15, 3, 1, '2026-11-06 13:36:37'),
(2, 7, 15, 3, 1, '2026-11-06 13:36:37'),
(3, 8, 15, 3, 1, '2026-11-06 13:36:37'),
(4, 42, 15, 3, 1, '2026-11-06 13:36:37'),
(5, 49, 15, 3, 1, '2026-11-06 13:36:37'),
(6, 2492, 15, 3, 1, '2026-11-06 13:36:37'),
(7, 2493, 15, 3, 1, '2026-11-06 13:36:37'),
(8, 2, 15, 3, 1, '2026-11-06 13:36:37'),
(9, 3, 15, 3, 1, '2026-11-06 13:36:37'),
(10, 4, 15, 3, 1, '2026-11-06 13:36:37'),
(11, 5, 15, 3, 1, '2026-11-06 13:36:37'),
(12, 39, 15, 3, 1, '2026-11-06 13:36:37'),
(13, 12, 15, 3, 1, '2026-11-06 13:36:37'),
(14, 16, 15, 3, 1, '2026-11-06 13:36:37'),
(15, 17, 15, 3, 1, '2026-11-06 13:36:37'),
(16, 18, 15, 3, 1, '2026-11-06 13:36:37'),
(17, 20, 15, 3, 1, '2026-11-06 13:36:37'),
(18, 41, 15, 3, 1, '2026-11-06 13:36:37'),
(19, 2488, 15, 3, 1, '2026-11-06 13:36:37'),
(20, 2495, 15, 3, 1, '2026-11-06 13:36:37'),
(21, 9, 15, 3, 1, '2026-11-06 13:36:37'),
(22, 15, 15, 3, 1, '2026-11-06 13:36:37'),
(23, 44, 15, 3, 1, '2026-11-06 13:36:37'),
(24, 14, 15, 3, 1, '2026-11-06 13:36:37'),
(25, 24, 15, 3, 1, '2026-11-06 13:36:37'),
(26, 43, 15, 3, 1, '2026-11-06 13:36:37'),
(27, 1, 15, 3, 1, '2026-11-06 13:36:37'),
(28, 25, 15, 3, 1, '2026-11-06 13:36:37'),
(29, 26, 15, 3, 1, '2026-11-06 13:36:37'),
(30, 10, 15, 3, 1, '2026-11-06 13:36:37'),
(31, 31, 15, 3, 1, '2026-11-06 13:36:37'),
(32, 33, 15, 3, 1, '2026-11-06 13:36:37'),
(33, 35, 15, 3, 1, '2026-11-06 13:36:37'),
(34, 46, 15, 3, 1, '2026-11-06 13:36:37'),
(35, 2476, 15, 3, 1, '2026-11-06 13:36:37'),
(36, 45, 15, 3, 1, '2026-11-06 13:36:37'),
(37, 48, 15, 3, 1, '2026-11-06 13:36:37'),
(38, 2496, 15, 3, 1, '2026-11-06 13:36:37'),
(39, 2497, 15, 3, 1, '2026-11-06 13:36:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_perfil`
--

CREATE TABLE `usuario_perfil` (
  `id_usuario` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_perfil`
--

INSERT INTO `usuario_perfil` (`id_usuario`, `id_perfil`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(12, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(20, 1),
(24, 1),
(25, 1),
(26, 1),
(31, 1),
(33, 1),
(35, 1),
(39, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(48, 1),
(49, 1),
(2476, 1),
(2488, 1),
(2492, 1),
(2493, 1),
(2495, 1),
(2496, 1),
(2497, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(12, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(20, 2),
(24, 2),
(25, 2),
(26, 2),
(31, 2),
(33, 2),
(35, 2),
(39, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(48, 2),
(49, 2),
(2476, 2),
(2492, 2),
(2493, 2),
(2495, 2),
(2496, 2),
(2497, 2),
(7, 3),
(8, 3),
(42, 3),
(43, 3),
(48, 4),
(2476, 4),
(2496, 4),
(2, 5),
(10, 5),
(15, 5),
(18, 5),
(24, 5),
(25, 5),
(35, 5),
(43, 5),
(46, 5),
(2488, 5),
(2493, 5);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `almuerzo_cierre`
--
ALTER TABLE `almuerzo_cierre`
  ADD PRIMARY KEY (`id_cierre`),
  ADD UNIQUE KEY `ux_almuerzo_cierre_dia` (`id_dia_menu`);

--
-- Indices de la tabla `almuerzo_configuracion`
--
ALTER TABLE `almuerzo_configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `almuerzo_dia`
--
ALTER TABLE `almuerzo_dia`
  ADD PRIMARY KEY (`id_dia_menu`),
  ADD UNIQUE KEY `ux_almuerzo_dia_fecha` (`fecha`),
  ADD KEY `idx_almuerzo_dia_mes` (`id_mes`),
  ADD KEY `idx_almuerzo_dia_semana` (`id_dia`);

--
-- Indices de la tabla `almuerzo_dia_semana`
--
ALTER TABLE `almuerzo_dia_semana`
  ADD PRIMARY KEY (`id_dia`);

--
-- Indices de la tabla `almuerzo_eleccion`
--
ALTER TABLE `almuerzo_eleccion`
  ADD PRIMARY KEY (`id_eleccion`),
  ADD UNIQUE KEY `ux_almuerzo_eleccion` (`id_usuario`,`id_dia_menu`),
  ADD KEY `idx_almuerzo_eleccion_dia` (`id_dia_menu`,`id_menu`),
  ADD KEY `fk_almeleccion_menu` (`id_menu`);

--
-- Indices de la tabla `almuerzo_menu`
--
ALTER TABLE `almuerzo_menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD UNIQUE KEY `ux_almuerzo_menu_dia_tipo` (`id_dia_menu`,`id_tipo`),
  ADD KEY `idx_almuerzo_menu_tipo` (`id_tipo`);

--
-- Indices de la tabla `almuerzo_menu_dia`
--
ALTER TABLE `almuerzo_menu_dia`
  ADD PRIMARY KEY (`fecha`),
  ADD KEY `idx_almuerzo_dia_mes` (`id_menu_mes`);

--
-- Indices de la tabla `almuerzo_menu_mes`
--
ALTER TABLE `almuerzo_menu_mes`
  ADD PRIMARY KEY (`id_menu_mes`),
  ADD UNIQUE KEY `ux_almuerzo_menu_mes` (`anio`,`mes`);

--
-- Indices de la tabla `almuerzo_mes`
--
ALTER TABLE `almuerzo_mes`
  ADD PRIMARY KEY (`id_mes`),
  ADD UNIQUE KEY `ux_almuerzo_mes` (`anio`,`mes`);

--
-- Indices de la tabla `almuerzo_pedido`
--
ALTER TABLE `almuerzo_pedido`
  ADD PRIMARY KEY (`id_usuario`,`fecha`),
  ADD KEY `idx_almuerzo_pedido_fecha` (`fecha`,`opcion`);

--
-- Indices de la tabla `almuerzo_tipo`
--
ALTER TABLE `almuerzo_tipo`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `ux_almuerzo_tipo_codigo` (`codigo`);

--
-- Indices de la tabla `archivos_adjuntos_ticket`
--
ALTER TABLE `archivos_adjuntos_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ticket` (`id_ticket`);

--
-- Indices de la tabla `area_trabajo`
--
ALTER TABLE `area_trabajo`
  ADD PRIMARY KEY (`id_area`);

--
-- Indices de la tabla `aseo_categoria_catalogo`
--
ALTER TABLE `aseo_categoria_catalogo`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `ux_aseo_categoria_nombre` (`nombre_categoria`);

--
-- Indices de la tabla `aseo_historial`
--
ALTER TABLE `aseo_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_aseo_historial_producto` (`id_producto`),
  ADD KEY `idx_aseo_historial_usuario` (`id_usuario`);

--
-- Indices de la tabla `aseo_movimientos`
--
ALTER TABLE `aseo_movimientos`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_aseo_mov_producto` (`id_producto`),
  ADD KEY `idx_aseo_mov_fecha` (`fecha_movimiento`),
  ADD KEY `idx_aseo_mov_usuario` (`id_usuario`);

--
-- Indices de la tabla `aseo_productos`
--
ALTER TABLE `aseo_productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_aseo_producto_colegio` (`id_colegio`),
  ADD KEY `idx_aseo_producto_usuario` (`id_usuario`),
  ADD KEY `idx_aseo_producto_activo` (`activo`),
  ADD KEY `idx_aseo_producto_categoria` (`categoria`);

--
-- Indices de la tabla `aseo_unidad_catalogo`
--
ALTER TABLE `aseo_unidad_catalogo`
  ADD PRIMARY KEY (`id_unidad`),
  ADD UNIQUE KEY `ux_aseo_unidad_nombre` (`nombre_unidad`);

--
-- Indices de la tabla `calificacion_tickett`
--
ALTER TABLE `calificacion_tickett`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_calificacion_ticket` (`id_ticket`);

--
-- Indices de la tabla `categoria_de_ticket`
--
ALTER TABLE `categoria_de_ticket`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `idx_colegio_categoria` (`id_colegio`);

--
-- Indices de la tabla `categoria_herramienta_catalogo`
--
ALTER TABLE `categoria_herramienta_catalogo`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uq_categoria_herramienta_nombre` (`nombre_categoria`);

--
-- Indices de la tabla `categoria_tecnico`
--
ALTER TABLE `categoria_tecnico`
  ADD PRIMARY KEY (`id_categoria_tecnico`),
  ADD KEY `id_tecnico` (`id_tecnico`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_categoria_2` (`id_categoria`);

--
-- Indices de la tabla `colegio`
--
ALTER TABLE `colegio`
  ADD PRIMARY KEY (`id_colegio`);

--
-- Indices de la tabla `colegio_departamento`
--
ALTER TABLE `colegio_departamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_colegio` (`id_colegio`,`id_departamento`);

--
-- Indices de la tabla `contenedor`
--
ALTER TABLE `contenedor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`),
  ADD UNIQUE KEY `nombre_departamento` (`nombre_departamento`);

--
-- Indices de la tabla `departamentos_colegio`
--
ALTER TABLE `departamentos_colegio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_colegio` (`id_colegio`);

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
-- Indices de la tabla `equipos_compra`
--
ALTER TABLE `equipos_compra`
  ADD PRIMARY KEY (`id_equipo`);

--
-- Indices de la tabla `equipo_almacenamiento`
--
ALTER TABLE `equipo_almacenamiento`
  ADD PRIMARY KEY (`id_equipo`);

--
-- Indices de la tabla `equipo_asignacion_historial`
--
ALTER TABLE `equipo_asignacion_historial`
  ADD PRIMARY KEY (`id_asignacion`);

--
-- Indices de la tabla `equipo_estado_historial`
--
ALTER TABLE `equipo_estado_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_equipo_estado_equipo` (`id_equipo`),
  ADD KEY `idx_equipo_estado_anterior` (`id_estado_anterior`),
  ADD KEY `idx_equipo_estado_nuevo` (`id_estado_nuevo`);

--
-- Indices de la tabla `equipo_fotos`
--
ALTER TABLE `equipo_fotos`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `idx_equipo_fotos_equipo` (`id_equipo`);

--
-- Indices de la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  ADD PRIMARY KEY (`id_memoria`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `equipo_monitor`
--
ALTER TABLE `equipo_monitor`
  ADD PRIMARY KEY (`id_monitor`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `equipo_movimiento`
--
ALTER TABLE `equipo_movimiento`
  ADD PRIMARY KEY (`id_movimiento`);

--
-- Indices de la tabla `equipo_procesador`
--
ALTER TABLE `equipo_procesador`
  ADD PRIMARY KEY (`id_equipo`);

--
-- Indices de la tabla `equipo_software`
--
ALTER TABLE `equipo_software`
  ADD PRIMARY KEY (`id_equipo`);

--
-- Indices de la tabla `equipo_ubicacion`
--
ALTER TABLE `equipo_ubicacion`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD UNIQUE KEY `uq_ubicacion_colegio_nombre` (`id_colegio`,`nombre_ubicacion`);

--
-- Indices de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estado_equipo`
--
ALTER TABLE `estado_equipo`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `estado_herramienta`
--
ALTER TABLE `estado_herramienta`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_eventos_fecha_inicio` (`fecha_inicio`),
  ADD KEY `idx_eventos_fecha_fin` (`fecha_fin`),
  ADD KEY `idx_eventos_responsable` (`responsable_id`),
  ADD KEY `idx_eventos_estado` (`estado`),
  ADD KEY `idx_eventos_tipo` (`tipo_evento`),
  ADD KEY `idx_eventos_eliminado` (`eliminado`),
  ADD KEY `idx_eventos_colegio` (`id_colegio`);

--
-- Indices de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  ADD PRIMARY KEY (`id_herramienta`),
  ADD UNIQUE KEY `uq_herramientas_numero_serie` (`numero_serie`),
  ADD UNIQUE KEY `uq_herramientas_qr_code` (`qr_code`),
  ADD KEY `idx_herramientas_colegio` (`id_colegio`),
  ADD KEY `idx_herramientas_usuario` (`id_usuario`),
  ADD KEY `idx_herramientas_usuario_asignado` (`id_usuario_asignado`),
  ADD KEY `idx_herramientas_estado` (`id_estado`),
  ADD KEY `idx_herramientas_categoria` (`categoria`);

--
-- Indices de la tabla `herramienta_compra`
--
ALTER TABLE `herramienta_compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD UNIQUE KEY `uq_herramienta_compra_herramienta` (`id_herramienta`),
  ADD KEY `idx_herramienta_compra_fecha` (`fecha_compra`);

--
-- Indices de la tabla `herramienta_detalle`
--
ALTER TABLE `herramienta_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD UNIQUE KEY `uq_herramienta_detalle_herramienta` (`id_herramienta`);

--
-- Indices de la tabla `herramienta_fotos`
--
ALTER TABLE `herramienta_fotos`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `idx_herramienta_fotos_herramienta` (`id_herramienta`),
  ADD KEY `idx_herramienta_fotos_principal` (`id_herramienta`,`principal`);

--
-- Indices de la tabla `herramienta_historial`
--
ALTER TABLE `herramienta_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_herramienta_historial_herramienta` (`id_herramienta`),
  ADD KEY `idx_herramienta_historial_usuario` (`id_usuario`),
  ADD KEY `idx_herramienta_historial_fecha` (`fecha`);

--
-- Indices de la tabla `herramienta_mantenciones`
--
ALTER TABLE `herramienta_mantenciones`
  ADD PRIMARY KEY (`id_mantencion`),
  ADD KEY `idx_herramienta_mantenciones_herramienta` (`id_herramienta`),
  ADD KEY `idx_herramienta_mantenciones_fecha` (`fecha_mantencion`),
  ADD KEY `idx_herramienta_mantenciones_proxima` (`proxima_fecha`);

--
-- Indices de la tabla `herramienta_prestamos`
--
ALTER TABLE `herramienta_prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `idx_herramienta_prestamos_herramienta` (`id_herramienta`),
  ADD KEY `idx_herramienta_prestamos_salida` (`fecha_salida`),
  ADD KEY `idx_herramienta_prestamos_estado` (`estado_prestamo`);

--
-- Indices de la tabla `inventario_software_datos_sensibles`
--
ALTER TABLE `inventario_software_datos_sensibles`
  ADD PRIMARY KEY (`id_dato_sensible`),
  ADD UNIQUE KEY `uq_dato_sensible_nombre` (`nombre`);

--
-- Indices de la tabla `inventario_software_tipo_usuario`
--
ALTER TABLE `inventario_software_tipo_usuario`
  ADD PRIMARY KEY (`id_tipo_usuario`),
  ADD UNIQUE KEY `uq_tipo_usuario_nombre` (`nombre`);

--
-- Indices de la tabla `jefatura_departamento`
--
ALTER TABLE `jefatura_departamento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_departamento_colegio`,`id_colegio`);

--
-- Indices de la tabla `mensajes_chat`
--
ALTER TABLE `mensajes_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mensajes_para` (`para`,`leido`,`eliminado`),
  ADD KEY `idx_mensajes_de` (`de`),
  ADD KEY `idx_mensajes_conversacion` (`id_conversacion`);

--
-- Indices de la tabla `menu_1`
--
ALTER TABLE `menu_1`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indices de la tabla `menu_1_sub`
--
ALTER TABLE `menu_1_sub`
  ADD PRIMARY KEY (`id_submenu`),
  ADD KEY `idx_menu_1_sub_id_menu` (`id_menu`);

--
-- Indices de la tabla `monitores`
--
ALTER TABLE `monitores`
  ADD PRIMARY KEY (`id_monitor`),
  ADD UNIQUE KEY `uq_monitores_legacy` (`id_monitor_legacy`),
  ADD KEY `idx_monitor_colegio` (`id_colegio`),
  ADD KEY `idx_monitor_ubicacion` (`id_ubicacion`),
  ADD KEY `idx_monitor_usuario` (`id_usuario_asignado`),
  ADD KEY `idx_monitor_estado` (`id_estado`),
  ADD KEY `idx_monitor_eliminado` (`eliminado`);

--
-- Indices de la tabla `monitor_asignacion_historial`
--
ALTER TABLE `monitor_asignacion_historial`
  ADD PRIMARY KEY (`id_asignacion`);

--
-- Indices de la tabla `monitor_compra`
--
ALTER TABLE `monitor_compra`
  ADD PRIMARY KEY (`id_monitor`);

--
-- Indices de la tabla `monitor_fotos`
--
ALTER TABLE `monitor_fotos`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `idx_monitor_fotos_monitor` (`id_monitor`);

--
-- Indices de la tabla `monitor_movimiento`
--
ALTER TABLE `monitor_movimiento`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_monitor_mov_monitor` (`id_monitor`),
  ADD KEY `idx_monitor_mov_origen` (`id_ubicacion_origen`),
  ADD KEY `idx_monitor_mov_destino` (`id_ubicacion_destino`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`id_perfil`);

--
-- Indices de la tabla `permisos_menu_1`
--
ALTER TABLE `permisos_menu_1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_menu1` (`id_menu1`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_tipo_permiso` (`id_tipo_permiso`),
  ADD KEY `idx_permisos_menu_1_id_usuario` (`id_usuario`),
  ADD KEY `idx_permisos_menu_1_id_menu1` (`id_menu1`),
  ADD KEY `idx_submenu` (`id_submenu`),
  ADD KEY `idx_usuario_submenu` (`id_usuario`,`id_submenu`);

--
-- Indices de la tabla `permiso_sub_menu`
--
ALTER TABLE `permiso_sub_menu`
  ADD PRIMARY KEY (`id_permiso_sub`),
  ADD UNIQUE KEY `uk_usuario_submenu` (`id_usuario`,`id_submenu`),
  ADD KEY `idx_id_usuario` (`id_usuario`),
  ADD KEY `idx_id_submenu` (`id_submenu`);

--
-- Indices de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proceso_tickets`
--
ALTER TABLE `proceso_tickets`
  ADD PRIMARY KEY (`id_proceso`),
  ADD KEY `id_ticket` (`id_ticket`);

--
-- Indices de la tabla `sitios_web_catalogo`
--
ALTER TABLE `sitios_web_catalogo`
  ADD PRIMARY KEY (`id_sitio`),
  ADD KEY `idx_sitio_colegio` (`id_colegio`),
  ADD KEY `idx_sitio_responsable` (`id_usuario_responsable`),
  ADD KEY `idx_sitio_activo` (`activo`);

--
-- Indices de la tabla `software_catalogo`
--
ALTER TABLE `software_catalogo`
  ADD PRIMARY KEY (`id_software`),
  ADD KEY `idx_software_colegio` (`id_colegio`),
  ADD KEY `idx_software_responsable` (`id_usuario_responsable`),
  ADD KEY `idx_software_activo` (`activo`);

--
-- Indices de la tabla `software_datos_almacenamiento`
--
ALTER TABLE `software_datos_almacenamiento`
  ADD PRIMARY KEY (`id_dato`),
  ADD KEY `idx_dato_software` (`id_software`);

--
-- Indices de la tabla `software_datos_sensibles_rel`
--
ALTER TABLE `software_datos_sensibles_rel`
  ADD PRIMARY KEY (`id_relacion`),
  ADD UNIQUE KEY `uq_software_dato_sensible` (`id_software`,`id_dato_sensible`),
  ADD KEY `idx_rel_dato_sensible` (`id_dato_sensible`);

--
-- Indices de la tabla `software_historial`
--
ALTER TABLE `software_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_historial_software` (`id_software`);

--
-- Indices de la tabla `software_tipo_usuario_rel`
--
ALTER TABLE `software_tipo_usuario_rel`
  ADD PRIMARY KEY (`id_relacion`),
  ADD UNIQUE KEY `uq_software_tipo_usuario` (`id_software`,`id_tipo_usuario`),
  ADD KEY `idx_rel_tipo_usuario` (`id_tipo_usuario`),
  ADD KEY `idx_rel_tipo_software` (`id_software`);

--
-- Indices de la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD PRIMARY KEY (`id_tecnico`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_ticket`),
  ADD UNIQUE KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_estado` (`id_estado`),
  ADD KEY `id_prioridad` (`id_prioridad`),
  ADD KEY `id_asignacion` (`id_tecnico`);

--
-- Indices de la tabla `ticket_conversaciones`
--
ALTER TABLE `ticket_conversaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ticket` (`id_ticket`);

--
-- Indices de la tabla `tipo_pc_catalogo`
--
ALTER TABLE `tipo_pc_catalogo`
  ADD PRIMARY KEY (`id_tipo_pc`),
  ADD UNIQUE KEY `uq_tipo_pc_nombre` (`nombre_tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_id_area_trabajo` (`id_area_trabajo`);

--
-- Indices de la tabla `usuario_colegio`
--
ALTER TABLE `usuario_colegio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_usuario_colegio` (`id_usuario`,`id_colegio`,`id_perfil`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_colegio` (`id_colegio`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_usuario_colegio_id_usuario` (`id_usuario`),
  ADD KEY `idx_usuario_colegio_id_colegio` (`id_colegio`);

--
-- Indices de la tabla `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD PRIMARY KEY (`id_usuario`,`id_perfil`),
  ADD KEY `id_perfil` (`id_perfil`),
  ADD KEY `idx_usuario_perfil_id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `almuerzo_cierre`
--
ALTER TABLE `almuerzo_cierre`
  MODIFY `id_cierre` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `almuerzo_dia`
--
ALTER TABLE `almuerzo_dia`
  MODIFY `id_dia_menu` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `almuerzo_eleccion`
--
ALTER TABLE `almuerzo_eleccion`
  MODIFY `id_eleccion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `almuerzo_menu`
--
ALTER TABLE `almuerzo_menu`
  MODIFY `id_menu` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `almuerzo_menu_mes`
--
ALTER TABLE `almuerzo_menu_mes`
  MODIFY `id_menu_mes` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `almuerzo_mes`
--
ALTER TABLE `almuerzo_mes`
  MODIFY `id_mes` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `almuerzo_tipo`
--
ALTER TABLE `almuerzo_tipo`
  MODIFY `id_tipo` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `archivos_adjuntos_ticket`
--
ALTER TABLE `archivos_adjuntos_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT de la tabla `area_trabajo`
--
ALTER TABLE `area_trabajo`
  MODIFY `id_area` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `aseo_categoria_catalogo`
--
ALTER TABLE `aseo_categoria_catalogo`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `aseo_historial`
--
ALTER TABLE `aseo_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aseo_movimientos`
--
ALTER TABLE `aseo_movimientos`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aseo_productos`
--
ALTER TABLE `aseo_productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aseo_unidad_catalogo`
--
ALTER TABLE `aseo_unidad_catalogo`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `calificacion_tickett`
--
ALTER TABLE `calificacion_tickett`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `categoria_de_ticket`
--
ALTER TABLE `categoria_de_ticket`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `categoria_herramienta_catalogo`
--
ALTER TABLE `categoria_herramienta_catalogo`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `categoria_tecnico`
--
ALTER TABLE `categoria_tecnico`
  MODIFY `id_categoria_tecnico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT de la tabla `colegio`
--
ALTER TABLE `colegio`
  MODIFY `id_colegio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `colegio_departamento`
--
ALTER TABLE `colegio_departamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contenedor`
--
ALTER TABLE `contenedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=273;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `departamentos_colegio`
--
ALTER TABLE `departamentos_colegio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipos_compra`
--
ALTER TABLE `equipos_compra`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_almacenamiento`
--
ALTER TABLE `equipo_almacenamiento`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_asignacion_historial`
--
ALTER TABLE `equipo_asignacion_historial`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_estado_historial`
--
ALTER TABLE `equipo_estado_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_fotos`
--
ALTER TABLE `equipo_fotos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  MODIFY `id_memoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_monitor`
--
ALTER TABLE `equipo_monitor`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_movimiento`
--
ALTER TABLE `equipo_movimiento`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_procesador`
--
ALTER TABLE `equipo_procesador`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_software`
--
ALTER TABLE `equipo_software`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipo_ubicacion`
--
ALTER TABLE `equipo_ubicacion`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estado_equipo`
--
ALTER TABLE `estado_equipo`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  MODIFY `id_herramienta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramienta_compra`
--
ALTER TABLE `herramienta_compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramienta_detalle`
--
ALTER TABLE `herramienta_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramienta_fotos`
--
ALTER TABLE `herramienta_fotos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramienta_historial`
--
ALTER TABLE `herramienta_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramienta_mantenciones`
--
ALTER TABLE `herramienta_mantenciones`
  MODIFY `id_mantencion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `herramienta_prestamos`
--
ALTER TABLE `herramienta_prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario_software_datos_sensibles`
--
ALTER TABLE `inventario_software_datos_sensibles`
  MODIFY `id_dato_sensible` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `inventario_software_tipo_usuario`
--
ALTER TABLE `inventario_software_tipo_usuario`
  MODIFY `id_tipo_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `jefatura_departamento`
--
ALTER TABLE `jefatura_departamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `mensajes_chat`
--
ALTER TABLE `mensajes_chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menu_1`
--
ALTER TABLE `menu_1`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `menu_1_sub`
--
ALTER TABLE `menu_1_sub`
  MODIFY `id_submenu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `monitores`
--
ALTER TABLE `monitores`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `monitor_asignacion_historial`
--
ALTER TABLE `monitor_asignacion_historial`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `monitor_fotos`
--
ALTER TABLE `monitor_fotos`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `monitor_movimiento`
--
ALTER TABLE `monitor_movimiento`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos_menu_1`
--
ALTER TABLE `permisos_menu_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1177;

--
-- AUTO_INCREMENT de la tabla `permiso_sub_menu`
--
ALTER TABLE `permiso_sub_menu`
  MODIFY `id_permiso_sub` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proceso_tickets`
--
ALTER TABLE `proceso_tickets`
  MODIFY `id_proceso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=582;

--
-- AUTO_INCREMENT de la tabla `sitios_web_catalogo`
--
ALTER TABLE `sitios_web_catalogo`
  MODIFY `id_sitio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `software_catalogo`
--
ALTER TABLE `software_catalogo`
  MODIFY `id_software` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `software_datos_almacenamiento`
--
ALTER TABLE `software_datos_almacenamiento`
  MODIFY `id_dato` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `software_datos_sensibles_rel`
--
ALTER TABLE `software_datos_sensibles_rel`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `software_historial`
--
ALTER TABLE `software_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `software_tipo_usuario_rel`
--
ALTER TABLE `software_tipo_usuario_rel`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tecnicos`
--
ALTER TABLE `tecnicos`
  MODIFY `id_tecnico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=597;

--
-- AUTO_INCREMENT de la tabla `ticket_conversaciones`
--
ALTER TABLE `ticket_conversaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=255;

--
-- AUTO_INCREMENT de la tabla `tipo_pc_catalogo`
--
ALTER TABLE `tipo_pc_catalogo`
  MODIFY `id_tipo_pc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2498;

--
-- AUTO_INCREMENT de la tabla `usuario_colegio`
--
ALTER TABLE `usuario_colegio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `almuerzo_cierre`
--
ALTER TABLE `almuerzo_cierre`
  ADD CONSTRAINT `fk_almcierre_dia` FOREIGN KEY (`id_dia_menu`) REFERENCES `almuerzo_dia` (`id_dia_menu`) ON DELETE CASCADE;

--
-- Filtros para la tabla `almuerzo_dia`
--
ALTER TABLE `almuerzo_dia`
  ADD CONSTRAINT `fk_almdia_mes` FOREIGN KEY (`id_mes`) REFERENCES `almuerzo_mes` (`id_mes`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_almdia_semana` FOREIGN KEY (`id_dia`) REFERENCES `almuerzo_dia_semana` (`id_dia`);

--
-- Filtros para la tabla `almuerzo_eleccion`
--
ALTER TABLE `almuerzo_eleccion`
  ADD CONSTRAINT `fk_almeleccion_dia` FOREIGN KEY (`id_dia_menu`) REFERENCES `almuerzo_dia` (`id_dia_menu`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_almeleccion_menu` FOREIGN KEY (`id_menu`) REFERENCES `almuerzo_menu` (`id_menu`) ON DELETE SET NULL;

--
-- Filtros para la tabla `almuerzo_menu`
--
ALTER TABLE `almuerzo_menu`
  ADD CONSTRAINT `fk_almmenu_dia` FOREIGN KEY (`id_dia_menu`) REFERENCES `almuerzo_dia` (`id_dia_menu`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_almmenu_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `almuerzo_tipo` (`id_tipo`);

--
-- Filtros para la tabla `almuerzo_menu_dia`
--
ALTER TABLE `almuerzo_menu_dia`
  ADD CONSTRAINT `fk_almuerzo_dia_mes` FOREIGN KEY (`id_menu_mes`) REFERENCES `almuerzo_menu_mes` (`id_menu_mes`) ON DELETE CASCADE;

--
-- Filtros para la tabla `aseo_historial`
--
ALTER TABLE `aseo_historial`
  ADD CONSTRAINT `fk_aseo_historial_producto` FOREIGN KEY (`id_producto`) REFERENCES `aseo_productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `aseo_movimientos`
--
ALTER TABLE `aseo_movimientos`
  ADD CONSTRAINT `fk_aseo_movimiento_producto` FOREIGN KEY (`id_producto`) REFERENCES `aseo_productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `departamentos_colegio`
--
ALTER TABLE `departamentos_colegio`
  ADD CONSTRAINT `departamentos_colegio_ibfk_1` FOREIGN KEY (`id_colegio`) REFERENCES `colegio` (`id_colegio`);

--
-- Filtros para la tabla `equipos_compra`
--
ALTER TABLE `equipos_compra`
  ADD CONSTRAINT `equipos_compra_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  ADD CONSTRAINT `equipo_memoria_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `equipo_monitor`
--
ALTER TABLE `equipo_monitor`
  ADD CONSTRAINT `equipo_monitor_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_eventos_colegio` FOREIGN KEY (`id_colegio`) REFERENCES `colegio` (`id_colegio`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eventos_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `herramienta_compra`
--
ALTER TABLE `herramienta_compra`
  ADD CONSTRAINT `fk_herramienta_compra` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `herramienta_detalle`
--
ALTER TABLE `herramienta_detalle`
  ADD CONSTRAINT `fk_herramienta_detalle` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `herramienta_fotos`
--
ALTER TABLE `herramienta_fotos`
  ADD CONSTRAINT `fk_herramienta_fotos` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `herramienta_historial`
--
ALTER TABLE `herramienta_historial`
  ADD CONSTRAINT `fk_herramienta_historial` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `herramienta_mantenciones`
--
ALTER TABLE `herramienta_mantenciones`
  ADD CONSTRAINT `fk_herramienta_mantenciones` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `herramienta_prestamos`
--
ALTER TABLE `herramienta_prestamos`
  ADD CONSTRAINT `fk_herramienta_prestamos` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `menu_1_sub`
--
ALTER TABLE `menu_1_sub`
  ADD CONSTRAINT `fk_ms_m1_20260408_01` FOREIGN KEY (`id_menu`) REFERENCES `menu_1` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `permisos_menu_1`
--
ALTER TABLE `permisos_menu_1`
  ADD CONSTRAINT `fk_permisos_menu_1_menu1_final` FOREIGN KEY (`id_menu1`) REFERENCES `menu_1` (`id_menu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_permisos_menu_1_usuario_final` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_perfil`
--
ALTER TABLE `usuario_perfil`
  ADD CONSTRAINT `fk_usuario_perfil_usuario_final` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
