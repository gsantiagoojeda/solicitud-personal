-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 08-04-2026 a las 16:02:19
-- Versión del servidor: 10.6.24-MariaDB-cll-lve
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ti_solicitud_personal`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sp_solicitud`
--

CREATE TABLE `sp_solicitud` (
  `solicitud_id` int(11) NOT NULL,
  `solicitud_id_format` varchar(10) GENERATED ALWAYS AS (concat('SP',lpad(`solicitud_id`,5,'0'))) VIRTUAL,
  `solicitud_puesto_id` int(11) NOT NULL,
  `solicitud_espacio_trabajo` varchar(10) DEFAULT NULL,
  `solicitud_espacio_trabajo_com` varchar(100) DEFAULT NULL,
  `solicitud_mobiliario` varchar(10) DEFAULT NULL,
  `solicitud_mobiliario_com` varchar(100) DEFAULT NULL,
  `solicitud_equipo_computo` varchar(10) DEFAULT NULL,
  `solicitud_equipo_computo_com` varchar(100) DEFAULT NULL,
  `solicitud_herramientas` varchar(10) DEFAULT NULL,
  `solicitud_herramientas_com` varchar(100) DEFAULT NULL,
  `solicitud_compras_necesarias` varchar(100) DEFAULT NULL,
  `solicitud_fecha_tentativa` date DEFAULT NULL,
  `solicitud_responsable` varchar(50) DEFAULT NULL,
  `solicitud_num_vacantes` int(11) DEFAULT NULL,
  `solicitud_sexo` varchar(15) DEFAULT NULL,
  `solicitud_estado_civil` varchar(100) DEFAULT NULL,
  `solicitud_escolaridad` varchar(100) DEFAULT NULL,
  `solicitud_edad_min` int(11) DEFAULT NULL,
  `solicitud_edad_max` int(11) DEFAULT NULL,
  `solicitud_experiencia` int(11) DEFAULT NULL,
  `solicitud_conocimientos` varchar(250) DEFAULT NULL,
  `solicitud_habilidades` varchar(250) DEFAULT NULL,
  `solicitud_tools` varchar(250) DEFAULT NULL,
  `solicitud_sueldo_id` int(11) DEFAULT NULL,
  `solicitud_horario_id` int(11) DEFAULT NULL,
  `solicitud_rolar` tinyint(1) DEFAULT NULL,
  `solicitud_solicitante_id` varchar(10) DEFAULT NULL,
  `solicitud_empresa` varchar(50) DEFAULT NULL,
  `solicitud_autorizador1_id` int(11) DEFAULT NULL,
  `solicitud_autorizacion1` varchar(10) DEFAULT NULL,
  `solicitud_date_autorizacion1` date DEFAULT NULL,
  `solicitud_autorizador2_id` int(11) DEFAULT NULL,
  `solicitud_autorizacion2` varchar(10) DEFAULT NULL,
  `solicitud_date_autorizacion2` date DEFAULT NULL,
  `solicitud_date_create` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `solicitud_autorizador3` int(11) DEFAULT NULL,
  `solicitud_date_autorizacion3` date DEFAULT NULL,
  `solciitud_autorizador_dirgral` varchar(10) DEFAULT NULL,
  `solicitud_autorizacion_dirgral` varchar(10) DEFAULT NULL,
  `solicitud_date_autorizacion_dirgral` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `sp_solicitud`
--

INSERT INTO `sp_solicitud` (`solicitud_id`, `solicitud_puesto_id`, `solicitud_espacio_trabajo`, `solicitud_espacio_trabajo_com`, `solicitud_mobiliario`, `solicitud_mobiliario_com`, `solicitud_equipo_computo`, `solicitud_equipo_computo_com`, `solicitud_herramientas`, `solicitud_herramientas_com`, `solicitud_compras_necesarias`, `solicitud_fecha_tentativa`, `solicitud_responsable`, `solicitud_num_vacantes`, `solicitud_sexo`, `solicitud_estado_civil`, `solicitud_escolaridad`, `solicitud_edad_min`, `solicitud_edad_max`, `solicitud_experiencia`, `solicitud_conocimientos`, `solicitud_habilidades`, `solicitud_tools`, `solicitud_sueldo_id`, `solicitud_horario_id`, `solicitud_rolar`, `solicitud_solicitante_id`, `solicitud_empresa`, `solicitud_autorizador1_id`, `solicitud_autorizacion1`, `solicitud_date_autorizacion1`, `solicitud_autorizador2_id`, `solicitud_autorizacion2`, `solicitud_date_autorizacion2`, `solicitud_date_create`, `solicitud_autorizador3`, `solicitud_date_autorizacion3`, `solciitud_autorizador_dirgral`, `solicitud_autorizacion_dirgral`, `solicitud_date_autorizacion_dirgral`) VALUES
(3, 48, 'si', 'editandovf', 'n/a', 'vr3gr', 'no', 'rgr3g', 'si', 'rg3rgr3', '3rgr3', '2026-01-22', 'r3gr', 1, 'indistinto', 'soltero', 'media superior', 33, 45, 4, 'rgrg', 'rgrg', 'rgr', 1, 1, 0, '264', 'Aramluz', 3636, 'Autorizada', '2026-01-07', 3009, 'Autorizada', '2026-01-15', '2026-04-08 23:02:06', 3530, '2026-04-08', NULL, NULL, NULL),
(4, 59, 'si', 'pregunta1', 'no', 'ninguno', 'n/a', 'pregunta3', 'si', 'pregunta4', 'comopras necesarias', '2026-01-27', 'responsbale', 2, 'indistinto', 'indistinto', 'superior', 20, 43, 3, 'conocimientos', 'habilidades', 'herramientas', 1, 4, 1, '393', NULL, 296, 'Autorizada', '2026-01-13', 3009, 'Autorizada', '2026-01-13', '2026-03-04 16:28:31', NULL, NULL, NULL, NULL, NULL),
(5, 60, 'n/a', 'na', 'si', 'nb', 'no', 'nc', 'si', 'nd', 'comoras', '2026-01-14', 'efe', 2, 'hombre', 'casado', 'secundaria', 2, 3, 1, 'comnio', '2g4ht4', 'thyh', 1, 2, 1, '393', NULL, 296, 'Autorizada', '2026-01-15', NULL, NULL, NULL, '2026-01-15 14:53:28', NULL, NULL, NULL, NULL, NULL),
(6, 51, 'si', 'gbg', 'n/a', 'gngn', 'no', 'gngr', 'no', 'gngftng', 'vfve', '2026-03-25', 'kiki', 1, 'hombre', 'casado', 'secundaria', 77, 77, 8, 'ukuk', 'ku', 'ii', 1, 4, 0, '264', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-05 00:01:54', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sp_sueldos`
--

CREATE TABLE `sp_sueldos` (
  `sueldo_id` varchar(11) NOT NULL,
  `sueldo_nombre` varchar(20) DEFAULT NULL,
  `sueldo_cantidad` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `sp_sueldos`
--

INSERT INTO `sp_sueldos` (`sueldo_id`, `sueldo_nombre`, `sueldo_cantidad`) VALUES
('1', 'Operativo', 8364.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `sp_solicitud`
--
ALTER TABLE `sp_solicitud`
  ADD PRIMARY KEY (`solicitud_id`);

--
-- Indices de la tabla `sp_sueldos`
--
ALTER TABLE `sp_sueldos`
  ADD PRIMARY KEY (`sueldo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `sp_solicitud`
--
ALTER TABLE `sp_solicitud`
  MODIFY `solicitud_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
