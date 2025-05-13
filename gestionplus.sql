-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 07-01-2025 a las 03:18:35
-- Versión del servidor: 10.11.10-MariaDB
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u814257589_gestionplus`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_acciones_ticket`
--

CREATE TABLE `tbl_acciones_ticket` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `area_id` int(11) DEFAULT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_areas`
--

CREATE TABLE `tbl_areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_eliminacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_configuracion`
--

CREATE TABLE `tbl_configuracion` (
  `id` int(11) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_user` varchar(255) NOT NULL,
  `smtp_pass` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_crypto` enum('tls','ssl','') DEFAULT 'tls',
  `bcc_addresses` text DEFAULT NULL,
  `mail_type` enum('text','html') DEFAULT 'html',
  `charset` varchar(50) DEFAULT 'UTF-8',
  `priority` tinyint(4) DEFAULT 3,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_configuracion`
--

INSERT INTO `tbl_configuracion` (`id`, `from_email`, `from_name`, `smtp_host`, `smtp_user`, `smtp_pass`, `smtp_port`, `smtp_crypto`, `bcc_addresses`, `mail_type`, `charset`, `priority`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'soporte@gestionplus.com.co', 'GestionPlus', 'smtp.hostinger.com', 'soporte@gestionplus.com.co', 'Renter19892308open@', 587, 'tls', NULL, 'html', 'UTF-8', 3, '2025-01-07 00:36:16', '2025-01-06 20:03:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_conversaciones`
--

CREATE TABLE `tbl_conversaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_documentos_tareas`
--

CREATE TABLE `tbl_documentos_tareas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_documentos_tickets`
--

CREATE TABLE `tbl_documentos_tickets` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `tamano` int(11) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_mensajes`
--

CREATE TABLE `tbl_mensajes` (
  `id` int(11) NOT NULL,
  `conversacion_id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_modulos`
--

CREATE TABLE `tbl_modulos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tbl_modulos`
--

INSERT INTO `tbl_modulos` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'Clientes', '2024-12-24 08:53:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_notificaciones`
--

CREATE TABLE `tbl_notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_permisos`
--

CREATE TABLE `tbl_permisos` (
  `id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `lectura` int(1) NOT NULL,
  `escritura` int(1) NOT NULL,
  `actualizacion` int(1) NOT NULL,
  `eliminacion` int(1) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_roles`
--

CREATE TABLE `tbl_roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_eliminacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tbl_roles`
--

INSERT INTO `tbl_roles` (`id`, `nombre`, `fecha_creacion`, `fecha_actualizacion`, `fecha_eliminacion`) VALUES
(1, 'Admin', '2024-12-21 18:56:51', '2024-12-21 18:56:51', NULL),
(2, 'Usuario', '2024-12-21 18:56:51', '2024-12-21 18:56:51', NULL),
(3, 'Cliente', '2024-12-21 18:56:51', '2024-12-21 18:56:51', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_tareas`
--

CREATE TABLE `tbl_tareas` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `emisor_id` int(11) NOT NULL,
  `receptor_id` int(11) DEFAULT NULL,
  `area_id` int(11) NOT NULL,
  `prioridad` enum('Baja','Media','Alta','Crítica') NOT NULL,
  `descripcion` text NOT NULL,
  `solucion` text DEFAULT NULL,
  `fecha_vencimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_solucion` datetime DEFAULT NULL,
  `estado` enum('Pendiente','Resuelto') NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_tickets`
--

CREATE TABLE `tbl_tickets` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `identificador` varchar(50) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `prioridad` enum('Baja','Media','Alta','Crítica') NOT NULL DEFAULT 'Media',
  `estado` enum('Abierto','En Proceso','Cerrado') DEFAULT 'Abierto',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `fecha_vencimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios`
--

CREATE TABLE `tbl_usuarios` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `area_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_eliminacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios`
--

INSERT INTO `tbl_usuarios` (`id`, `rol_id`, `area_id`, `nombre`, `correo`, `contrasena`, `fecha_registro`, `fecha_actualizacion`, `fecha_eliminacion`) VALUES
(4, 1, NULL, 'ADMIN SYSTEM', 'rodaheva@gmail.com', 'Re19892308@', '2024-12-21 18:49:41', '2025-01-07 03:16:03', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbl_acciones_ticket`
--
ALTER TABLE `tbl_acciones_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_areas`
--
ALTER TABLE `tbl_areas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_configuracion`
--
ALTER TABLE `tbl_configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_conversaciones`
--
ALTER TABLE `tbl_conversaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_documentos_tareas`
--
ALTER TABLE `tbl_documentos_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_documentos_tickets`
--
ALTER TABLE `tbl_documentos_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_mensajes`
--
ALTER TABLE `tbl_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversacion_id` (`conversacion_id`),
  ADD KEY `remitente_id` (`remitente_id`);

--
-- Indices de la tabla `tbl_modulos`
--
ALTER TABLE `tbl_modulos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_notificaciones`
--
ALTER TABLE `tbl_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modulo_id` (`modulo_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_roles`
--
ALTER TABLE `tbl_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_tareas`
--
ALTER TABLE `tbl_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `usuario_id` (`emisor_id`),
  ADD KEY `receptor_id` (`receptor_id`);

--
-- Indices de la tabla `tbl_tickets`
--
ALTER TABLE `tbl_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo_electronico` (`correo`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `area_id` (`area_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_acciones_ticket`
--
ALTER TABLE `tbl_acciones_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `tbl_areas`
--
ALTER TABLE `tbl_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_configuracion`
--
ALTER TABLE `tbl_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_conversaciones`
--
ALTER TABLE `tbl_conversaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_documentos_tareas`
--
ALTER TABLE `tbl_documentos_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tbl_documentos_tickets`
--
ALTER TABLE `tbl_documentos_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tbl_mensajes`
--
ALTER TABLE `tbl_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_modulos`
--
ALTER TABLE `tbl_modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_notificaciones`
--
ALTER TABLE `tbl_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tbl_roles`
--
ALTER TABLE `tbl_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbl_tareas`
--
ALTER TABLE `tbl_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `tbl_tickets`
--
ALTER TABLE `tbl_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tbl_acciones_ticket`
--
ALTER TABLE `tbl_acciones_ticket`
  ADD CONSTRAINT `tbl_acciones_ticket_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `tbl_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_acciones_ticket_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tbl_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_acciones_ticket_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_conversaciones`
--
ALTER TABLE `tbl_conversaciones`
  ADD CONSTRAINT `tbl_conversaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbl_documentos_tareas`
--
ALTER TABLE `tbl_documentos_tareas`
  ADD CONSTRAINT `tbl_documentos_tareas_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tbl_tareas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_documentos_tareas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_documentos_tickets`
--
ALTER TABLE `tbl_documentos_tickets`
  ADD CONSTRAINT `tbl_documentos_tickets_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tbl_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_documentos_tickets_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_mensajes`
--
ALTER TABLE `tbl_mensajes`
  ADD CONSTRAINT `tbl_mensajes_ibfk_1` FOREIGN KEY (`conversacion_id`) REFERENCES `tbl_conversaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_mensajes_ibfk_2` FOREIGN KEY (`remitente_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbl_notificaciones`
--
ALTER TABLE `tbl_notificaciones`
  ADD CONSTRAINT `tbl_notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_permisos`
--
ALTER TABLE `tbl_permisos`
  ADD CONSTRAINT `tbl_permisos_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `tbl_modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_permisos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_tareas`
--
ALTER TABLE `tbl_tareas`
  ADD CONSTRAINT `tbl_tareas_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tbl_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_tareas_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `tbl_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_tareas_ibfk_3` FOREIGN KEY (`emisor_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_tareas_ibfk_4` FOREIGN KEY (`receptor_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_tickets`
--
ALTER TABLE `tbl_tickets`
  ADD CONSTRAINT `tbl_tickets_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `tbl_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_tickets_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_tickets_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  ADD CONSTRAINT `tbl_usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `tbl_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_usuarios_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `tbl_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
