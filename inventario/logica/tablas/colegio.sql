-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:58:50
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
-- Estructura de tabla para la tabla `colegio`
--

CREATE TABLE `colegio` (
  `id_colegio` int(11) NOT NULL,
  `nom_colegio` varchar(60) NOT NULL,
  `rza_colegio` varchar(50) NOT NULL,
  `nco_colegio` varchar(150) NOT NULL,
  `dir_colegio` varchar(50) NOT NULL,
  `rbd_colegio` varchar(10) NOT NULL,
  `id_dependencia` int(11) NOT NULL,
  `id_comuna` int(11) NOT NULL,
  `tel_colegio` varchar(10) NOT NULL,
  `web_colegio` varchar(60) NOT NULL,
  `bd` varchar(30) NOT NULL,
  `orden` int(11) NOT NULL,
  `email_entrevista` varchar(100) NOT NULL,
  `num_r_educacion` int(11) NOT NULL,
  `ano_r_educacion` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `email_comunicaciones` varchar(100) NOT NULL,
  `sexo` int(11) NOT NULL DEFAULT 0,
  `multi_cole` varchar(2) NOT NULL DEFAULT 'no',
  `identificador` varchar(20) NOT NULL,
  `rut_colegio` varchar(12) NOT NULL,
  `correo_contrato` varchar(50) NOT NULL,
  `url_pagina` varchar(255) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT 1 COMMENT '1 = activo |\r\n0 = inactivo\r\n\r\n',
  `color_principal` varchar(7) DEFAULT NULL COMMENT 'Color principal',
  `color_secundario` varchar(7) DEFAULT NULL COMMENT 'Color secundario',
  `color_terciario` varchar(7) DEFAULT NULL COMMENT 'Color terciario',
  `color_cuaternario` varchar(7) DEFAULT NULL COMMENT 'Color cuaternario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `colegio`
--
ALTER TABLE `colegio`
  ADD PRIMARY KEY (`id_colegio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `colegio`
--
ALTER TABLE `colegio`
  MODIFY `id_colegio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
