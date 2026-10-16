-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 15-10-2026 a las 19:35:25
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_pacientes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dietas_pacientes`
--

DROP TABLE IF EXISTS `dietas_pacientes`;
CREATE TABLE IF NOT EXISTS `dietas_pacientes` (
  `paciente_id` int NOT NULL,
  `dieta` varchar(255) NOT NULL,
  `suministrada` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`paciente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `dietas_pacientes`
--

INSERT INTO `dietas_pacientes` (`paciente_id`, `dieta`, `suministrada`, `fecha_actualizacion`) VALUES
(33, 'Requiere dieta', 0, '2026-10-14 19:48:46'),
(35, 'Requiere dieta', 1, '2026-10-14 19:51:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` enum('Sala de preparación','Cirugía','Sala de recuperación','Suministrar dieta') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_sistema`
--

DROP TABLE IF EXISTS `usuarios_sistema`;
CREATE TABLE IF NOT EXISTS `usuarios_sistema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(40) NOT NULL,
  `clave_hash` varchar(255) NOT NULL,
  `rol` enum('master','usuario') NOT NULL DEFAULT 'usuario',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios_sistema`
--

INSERT INTO `usuarios_sistema` (`id`, `nombre`, `usuario`, `clave_hash`, `rol`, `creado_en`) VALUES
(1, 'HACMC', 'HACMC', '$2y$10$zqdXfiag1qAeadm70z3hnu.H3lKI2mLv6KrWVD/jN0xns/xmymJd6', 'master', '2026-10-14 14:33:01'),
(2, 'JULIANA', 'juliana', '$2y$10$2lzcldrWXpNo1VGW21y1le0APPEHs0mwKzCjlCYSv9IdRfx5pIgBS', 'usuario', '2026-10-14 14:34:35');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
