-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:57:36
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_memoria`
--

INSERT INTO `equipo_memoria` (`id_memoria`, `id_equipo`, `designacion_memoria`, `formato_memoria`, `tipo_memoria`, `tamano_memoria`, `frecuencia_memoria`, `marca_memoria`, `orden_memoria`) VALUES
(295, 55, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(296, 55, 'DIMM B', '', '', 0, 0, '', 2),
(297, 56, 'Bottom-slot 1(left)', 'SODIMM', 'DDR4', 8, 2400, 'Samsung', 1),
(298, 56, 'Bottom-slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(299, 57, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(300, 57, 'DIMM B', '', '', 0, 0, '', 2),
(301, 58, 'Bottom - Slot 1 (left)', 'SODIMM', '', 0, 0, '', 1),
(302, 58, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 2667, 'SK Hynix', 2),
(305, 60, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(306, 60, 'DIMM B', '', '', 0, 0, '', 2),
(307, 61, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(308, 61, 'DIMM B', '', '', 0, 0, '', 2),
(309, 62, 'DIMM 0', 'SODIMM', 'DDR4', 8, 2400, 'Hynix', 1),
(310, 62, 'DIMM 0', 'SODIMM', 'DDR4', 8, 2400, 'Samsung', 2),
(311, 63, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 8, 3200, 'Samsung', 1),
(312, 63, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 3200, 'Samsung 2', 2),
(315, 65, 'Bottom - Slot 1 (left) MG', 'SODIMM', 'DDR4', 8, 3200, 'Samsung', 1),
(316, 65, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 3200, 'Samsung', 2),
(317, 66, 'Bottom-Slot 1(left)', 'SODIMM', 'DDR4', 8, 2133, 'Ramaxel Technology', 1),
(318, 66, 'Bottom-Slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(321, 68, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 4, 3200, 'Micron Technology', 1),
(322, 68, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 4, 3200, 'Micron Technology', 2),
(325, 70, 'Controller0-ChannelA', 'DIMM', 'LPDDR4', 8, 5200, 'Samsung', 1),
(326, 70, 'Controller1-ChannelA', 'DIMM', 'LPDDR4', 8, 5200, 'Samsung', 2),
(327, 71, 'Controller0-ChannelA-DIMM0', 'SODIMM', 'DDR4', 4, 3200, 'SK Hynix', 1),
(328, 71, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 2),
(329, 72, 'Controller0-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'SK Hynix', 1),
(330, 72, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'SK Hynix', 2),
(331, 73, 'Bottom-slot 1(left)', 'SODIMM', 'DDR4', 8, 2400, 'Samsung', 1),
(332, 73, 'Bottom-slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(333, 74, 'Controller0-ChannelA-DIMM0', 'SODIMM', 'DDR4', 4, 3200, 'Micron Technology', 1),
(334, 74, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 2),
(343, 59, 'Bottom-slot 1(left)', 'SODIMM', 'DDR4', 4, 2667, 'SK Hynix', 1),
(344, 59, 'Bottom-slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(381, 54, 'ChannelA-DIMM0', '', '', 0, 0, '', 1),
(382, 54, 'ChannelA-DIMM1', '', '', 0, 0, '', 2),
(383, 54, 'ChannelB-DIMM0', 'DIMM', 'DDR4', 8, 2133, '859B', 3),
(384, 54, 'ChannelB-DIMM1', '', '', 0, 0, '', 4),
(390, 75, '1', 'LPDDR5', 'RAM', 4, 5500, '', 1),
(391, 75, '2', 'LPDDR5', 'RAM', 4, 5500, '', 2),
(394, 76, '1', 'LPDDR5', 'RAM', 4, 5500, '', 1),
(395, 76, '2', '', 'RAM', 4, 5500, '', 2),
(397, 78, '', '', '', 0, 0, '', 1),
(401, 77, 'DIMM1', 'DIMM', 'DDR4', 8, 2666, 'Kingston', 1),
(402, 80, '', '', '', 32, 0, '', 1),
(405, 64, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 8, 3200, 'Micron Technology', 1),
(406, 64, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 3200, 'Micron Technology', 2),
(407, 69, 'Controller0-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 1),
(408, 69, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 2),
(409, 67, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 4, 3200, 'SK Hynix', 1),
(410, 67, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 4, 3200, 'SK Hynix', 2),
(411, 81, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(412, 82, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(413, 83, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(414, 84, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(415, 85, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(416, 86, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(417, 87, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(418, 88, '', '', 'LPDDR5', 8, 6400, 'Apple', 1),
(419, 89, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(420, 90, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(421, 91, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(422, 92, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(423, 93, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(424, 94, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(425, 95, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(426, 96, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(427, 97, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(428, 98, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(429, 99, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(430, 100, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(431, 101, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(432, 102, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(433, 103, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(434, 104, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(435, 105, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(436, 106, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(437, 107, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(438, 108, '', '', 'LPDDR3', 2, 1333, 'Apple', 1),
(439, 109, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(440, 110, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(441, 111, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(442, 112, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(443, 113, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(444, 114, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(445, 115, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(446, 116, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(447, 117, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(448, 118, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(449, 119, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(450, 120, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(451, 121, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(452, 122, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(453, 123, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(454, 124, '', '', 'LPDDR3-1600', 1, 450, 'Apple', 1),
(455, 125, '', '', 'LPDDR3-1600', 1, 450, 'Apple', 1),
(456, 126, '', '', 'LPDDR3-1600', 1, 450, 'Apple', 1),
(457, 127, '', '', 'LPDDR3-1600', 1, 450, 'Apple', 1),
(458, 128, '', '', 'LPDDR3', 2, 1333, 'Apple', 1),
(459, 129, '', '', 'LPDDR4', 2, 3200, 'Apple', 1),
(460, 130, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(461, 131, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(462, 132, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(463, 133, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(464, 134, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(465, 135, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(466, 136, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(467, 137, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(468, 138, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(469, 139, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(470, 140, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(471, 141, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(472, 142, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(473, 143, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(474, 144, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(475, 145, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(476, 146, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(477, 147, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(478, 148, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(479, 149, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(480, 150, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(481, 151, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(482, 152, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(483, 153, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(484, 154, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(485, 155, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(486, 156, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(487, 157, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(488, 158, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(489, 159, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(490, 160, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(491, 161, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(492, 162, '', 'SODIMM', 'DDR3L', 4, 1600, 'Hynix', 1),
(493, 163, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(494, 164, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(495, 165, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(496, 166, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(497, 167, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(498, 168, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(499, 169, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(500, 170, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(501, 171, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(502, 172, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(503, 173, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(504, 174, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(505, 175, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(506, 176, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(507, 177, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(508, 178, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(509, 179, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(510, 180, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(511, 181, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(512, 182, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(513, 183, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(514, 184, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(515, 185, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(516, 186, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(517, 187, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(518, 188, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(519, 189, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(520, 190, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(521, 191, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(522, 192, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(523, 193, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(524, 194, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(525, 195, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(526, 196, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(527, 197, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(528, 198, '', '', 'LPDDR4X', 4, 3733, 'MediaTek', 1),
(529, 199, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(530, 200, '', '', 'DDR4', 4, 2400, '', 1),
(531, 201, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(532, 202, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(533, 203, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(534, 204, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(535, 205, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(536, 206, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(537, 207, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(538, 208, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(539, 209, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(540, 210, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(541, 211, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(542, 212, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(543, 213, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(544, 214, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(545, 215, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(546, 216, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(547, 217, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(548, 218, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(549, 219, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(550, 220, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(551, 221, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(552, 222, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(553, 223, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(554, 224, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(555, 225, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(556, 226, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(557, 227, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(558, 228, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(559, 229, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(560, 230, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(561, 231, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(562, 232, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(563, 233, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(564, 234, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(565, 235, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(566, 236, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(567, 237, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(568, 238, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(569, 239, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(570, 240, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(571, 241, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(572, 242, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(573, 243, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(574, 244, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(575, 245, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(576, 246, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(577, 247, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(578, 248, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(579, 249, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(580, 250, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(581, 251, '', '', 'DDR4', 4, 2400, '', 1),
(582, 252, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(583, 253, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(584, 254, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(585, 255, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(586, 256, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(587, 257, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(588, 258, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(589, 259, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(590, 260, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(591, 261, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(592, 262, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(593, 263, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(594, 264, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(595, 265, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(596, 266, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(597, 267, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(598, 268, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(599, 269, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(600, 270, '', 'SDRAM', 'DDR4', 4, 1866, '', 1),
(601, 271, '', 'SODIMM', 'DDR4', 8, 2666, '', 1),
(602, 272, '', 'Windows Defender', 'SODIMM1', 0, 0, '', 1),
(603, 273, '', 'Windows Defender', 'SODIMM1', 0, 0, '4GB', 1),
(604, 274, '', 'Windows Defender', 'SODIMM1', 0, 0, '8GB', 1),
(605, 275, '', 'Windows Defender', 'SODIMM1', 0, 0, '8GB', 1),
(606, 276, '', 'Windows Defender', 'DIMM1', 0, 0, '4GB', 1),
(607, 277, '', 'Windows Defender', 'DIMM1', 0, 0, '8GB', 1),
(608, 278, '', 'Windows Defender', 'SODIMM1', 0, 0, '4GB', 1),
(609, 279, '', 'Windows Defender', 'SODIMM1', 0, 0, '8GB', 1),
(610, 280, '', 'Windows Defender', 'SODIMM1', 0, 0, '4GB', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  ADD PRIMARY KEY (`id_memoria`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  MODIFY `id_memoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=611;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  ADD CONSTRAINT `equipo_memoria_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
