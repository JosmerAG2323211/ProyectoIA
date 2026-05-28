-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-05-2026 a las 09:14:47
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_mantenimiento_ia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `archivo_manual` varchar(255) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `nombre`, `modelo`, `marca`, `archivo_manual`, `creado_en`) VALUES
(1, 'Compresor Industrial de Aire', 'XLT-5000', 'Ingersoll Rand', 'manual_compresor_xlt5000.pdf', '2026-05-20 00:56:48'),
(2, 'Bomba Centrífuga de Alta Presión', 'HPX-200', 'Flowserve', '1779854693_222876_HPX_H_HPX_MP_Centrifugal_Pumps_User_Instructions_3.pdf', '2026-05-27 04:04:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_fallas`
--

CREATE TABLE `reportes_fallas` (
  `id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `descripcion_falla` text NOT NULL,
  `foto_evidencia` varchar(255) DEFAULT NULL,
  `diagnostico_ia` text DEFAULT NULL,
  `prioridad` enum('Rutinaria','Urgente','Crítica') DEFAULT 'Rutinaria',
  `estado` enum('Abierto','Resuelto') DEFAULT 'Abierto',
  `sugerencia_efectiva` tinyint(1) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes_fallas`
--

INSERT INTO `reportes_fallas` (`id`, `equipo_id`, `usuario_id`, `tecnico_id`, `descripcion_falla`, `foto_evidencia`, `diagnostico_ia`, `prioridad`, `estado`, `sugerencia_efectiva`, `creado_en`) VALUES
(1, 2, 1, NULL, 'La bomba presenta una fuerte vibración en el eje principal acoplado, acompañada de un incremento drástico de temperatura en los cojinetes a los 10 minutos de encendido.', NULL, 'La IA no pudo procesar una respuesta en este momento.', 'Rutinaria', 'Abierto', NULL, '2026-05-27 04:06:30'),
(2, 1, 1, 2, 'El aire industrial no enfría', NULL, 'Análisis Inteligente Preliminar: Se ha registrado la anomalía reportada. El sistema evaluará el historial del manual correspondiente para sugerir los pasos de aislamiento.', 'Rutinaria', 'Abierto', NULL, '2026-05-27 06:35:17'),
(3, 1, 1, NULL, 'Esta echando humo', NULL, 'Análisis Inteligente Preliminar: Se ha registrado la anomalía reportada.', 'Crítica', 'Abierto', NULL, '2026-05-27 07:07:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','tecnico') DEFAULT 'tecnico',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `rol`, `creado_en`) VALUES
(1, 'Wilber Ruiz', 'wilber@ugma.edu.ve', '123456', 'tecnico', '2026-05-20 00:56:47'),
(2, 'Wilson Junior', 'tecnico_superior@ugma.edu.ve', '123456', 'tecnico', '2026-05-27 06:25:37');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reportes_fallas`
--
ALTER TABLE `reportes_fallas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipo_id` (`equipo_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `tecnico_id` (`tecnico_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reportes_fallas`
--
ALTER TABLE `reportes_fallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reportes_fallas`
--
ALTER TABLE `reportes_fallas`
  ADD CONSTRAINT `fk_reportes_tecnico` FOREIGN KEY (`tecnico_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `reportes_fallas_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_fallas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
