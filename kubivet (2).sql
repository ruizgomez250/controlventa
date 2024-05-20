-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-08-2023 a las 14:46:29
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `kubivet`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_id` bigint(20) UNSIGNED NOT NULL,
  `mascota_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `fecha`, `hora`, `estado_id`, `tipo_id`, `mascota_id`, `created_at`, `updated_at`) VALUES
(1, '2023-06-29', '12:03:09', 66, 65, 18, '2023-06-29 16:03:09', '2023-06-29 16:03:09'),
(2, '2023-06-29', '14:19:09', 66, 63, 17, '2023-06-29 18:19:09', '2023-06-29 18:19:09'),
(3, '2023-07-12', NULL, 66, 64, 19, '2023-07-06 00:20:36', '2023-07-06 00:20:36'),
(4, '2023-07-05', NULL, 66, 65, 14, '2023-07-06 00:21:43', '2023-07-06 00:21:43'),
(5, '2023-07-05', NULL, 66, 62, 18, '2023-07-06 00:23:34', '2023-07-06 00:23:34'),
(6, '2023-07-05', NULL, 66, 65, 22, '2023-07-06 00:45:46', '2023-07-06 00:45:46'),
(7, '2023-07-07', NULL, 66, 63, 23, '2023-07-06 16:23:55', '2023-07-06 16:23:55'),
(8, '2023-07-06', NULL, 66, 65, 24, '2023-07-06 16:35:29', '2023-07-06 16:35:29'),
(9, '2023-07-06', '14:01:00', 66, 63, 24, '2023-07-06 17:12:33', '2023-07-06 17:12:33'),
(10, '2023-07-07', '15:45:00', 66, 64, 25, '2023-07-06 18:45:42', '2023-07-06 18:45:42'),
(28, '2023-07-17', '05:26:00', 67, 63, 23, '2023-07-17 20:26:38', '2023-07-17 20:38:06'),
(30, '2023-07-17', '06:31:00', 67, 63, 22, '2023-07-17 20:31:35', '2023-07-17 20:38:11'),
(32, '2023-07-17', '04:36:00', 67, 63, 18, '2023-07-17 20:36:58', '2023-07-17 20:38:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `razonsocial` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_estado` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `razonsocial`, `ruc`, `direccion`, `correo`, `telefono`, `celular`, `observacion`, `id_estado`, `created_at`, `updated_at`) VALUES
(1, 'NARCISO RAMON AÑAZCO', '2535470-1', 'AUGUSTO ROA BASTO CASI PRESIDENTE HAYES', 'narcisoramon79@gmail.com', NULL, '0981828219', 'PRUEBA', 1, '2023-06-17 04:18:35', '2023-06-24 20:54:03'),
(3, 'ALEXANDRO SA editado 2', '4855744-8', 'TTE FARIÑA CASI GRAL AQUINO', 'alexandro@gmail.com', NULL, '0995987789', 'PRUEBA editado', 1, '2023-06-17 20:45:43', '2023-06-22 21:16:53'),
(4, 'MARIEL AÑAZCO', '3434433-5', 'san antonio', 'mariel@gmail.com', NULL, '0982234567', 'ninguna', 1, '2023-06-24 20:55:11', '2023-06-24 20:55:11'),
(5, 'EVA FRUTOS', '28097887', 'AUGUSTO ROA BASTO CASI PRESIDENTE HAYES', 'eva.frutos@gmail.com', NULL, '0983379079', 'ninguna', 1, '2023-06-24 20:55:47', '2023-06-24 20:55:47'),
(6, 'JORGE RUIZ GOMEZ', '1807552', 'LUQUE', NULL, NULL, '0981834387', 'NADA', 1, '2023-07-06 16:21:17', '2023-07-06 16:21:17'),
(7, 'GUILLERMO VAZQUEZ', '54665454', NULL, NULL, NULL, '0981258585', NULL, 1, '2023-07-06 18:43:54', '2023-07-06 18:43:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dominios`
--

CREATE TABLE `dominios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('activado','desactivado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dominios`
--

INSERT INTO `dominios` (`id`, `descripcion`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Estado de Clientes', 'activado', NULL, NULL),
(2, 'Estado de Proveedores', 'activado', NULL, NULL),
(3, 'Categoría de Productos', 'activado', '2023-06-17 17:04:30', '2023-06-17 17:04:30'),
(4, 'Estado de Productos', 'activado', '2023-06-17 21:36:57', '2023-06-17 21:36:57'),
(5, 'Unidad de Medida de Productos', 'activado', '2023-06-20 14:12:18', '2023-06-20 14:12:18'),
(6, 'Sexo Mascota', 'activado', '2023-06-23 20:14:05', '2023-06-23 20:14:05'),
(7, 'Estado Mascota', 'activado', '2023-06-23 20:14:05', '2023-06-23 20:14:05'),
(8, 'Clase de Animales', 'activado', '2023-06-23 20:14:05', '2023-06-23 20:14:05'),
(9, 'Estado de Animales', 'activado', '2023-06-23 20:29:37', '2023-06-23 20:29:37'),
(10, 'Estado de Cita', 'activado', '2023-06-29 14:08:05', '2023-06-29 14:08:05'),
(11, 'Tipo de Cita', 'activado', '2023-06-29 14:08:05', '2023-06-29 14:08:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo_id` bigint(20) UNSIGNED NOT NULL,
  `raza_id` bigint(20) UNSIGNED NOT NULL,
  `propietario_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`id`, `nombre`, `foto`, `edad`, `sexo_id`, `raza_id`, `propietario_id`, `estado_id`, `created_at`, `updated_at`) VALUES
(14, 'MICHICHU', 'images/d6Fz1OqvU7kC4ZhR8SiFkKLqROt1Zu7NrjgMQ4iK.jpg', '2 AÑOS', 50, 8, 4, 60, '2023-06-27 21:11:46', '2023-06-27 21:11:46'),
(17, 'MICHI', 'images/dRlCJznL2T8ITrq24RSTDpy255acJIPJDPfYo8vk.jpg', '1 AÑO', 50, 9, 5, 60, '2023-06-28 00:35:01', '2023-06-29 17:22:43'),
(18, 'DOKI', 'images/bXKBgb7QL15Cv8TRHN3xnI496txGwO2gVUPctb7K.jpg', '1 AÑO', 50, 3, 5, 60, '2023-06-29 17:22:27', '2023-06-29 17:22:27'),
(19, 'PICHI', 'images/11ezJNdEiaRVglebbqIiW4IBx3lgCN9cUDxWvuWj.jpg', '1 AÑO', 50, 13, 4, 60, '2023-06-29 23:53:19', '2023-06-29 23:53:19'),
(20, 'Pichichu', 'images/F0vCWOLSBEcM0LYFIb0Lav0AZJHXTH2vI6Sz1LRk.jpg', '2 años', 50, 3, 4, 60, '2023-06-29 23:55:55', '2023-06-29 23:55:55'),
(21, 'BUGI', 'images/AkfMzOfdSfodUWY3zCtBvjsiQ59HHnFyzUvO6EMD.png', '3 AÑOS', 50, 2, 1, 60, '2023-07-06 00:27:21', '2023-07-06 00:27:21'),
(22, 'MILOTE', 'images/2FpI6ST6H62jiZtROWwFyfS5nQ0CIBq66bRDSS3I.jpg', '1 AÑO', 50, 3, 3, 60, '2023-07-06 00:43:29', '2023-07-06 00:43:29'),
(23, 'PRINCESA MARIA', 'images/ZdOt9kDVKQ0CqRfyH8g4YVxAaq7gregmkvlqf40x.jpg', '1 AÑO', 51, 2, 6, 60, '2023-07-06 16:23:02', '2023-07-06 16:23:02'),
(24, 'KIRA MARIA DEL CARMEN', 'images/laYkLHfnfdVParFaRIrrsay6RMmox6hejtDWy5qU.png', '4 años', 51, 15, 6, 60, '2023-07-06 16:34:48', '2023-07-06 16:34:48'),
(25, 'PIPO', 'images/9sdlFPD43Awcq4vsBNf7uxObpgWCicCfhb2Uclab.png', '1 AÑO', 50, 4, 7, 60, '2023-07-06 18:44:48', '2023-07-06 18:44:48'),
(26, 'POLLUELO', 'images/UQDhlZ496mj6nw6A7HDjgVWTAWFZdkXhwzMiQchS.jpg', '1 AÑO', 50, 8, 4, 60, '2023-07-06 20:08:49', '2023-07-06 20:08:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2023_05_30_142657_create_eventos_table', 1),
(7, '2023_05_30_174422_create_dominios_table', 1),
(8, '2023_05_30_174432_create_opciones_table', 1),
(10, '2023_05_31_192659_create_clientes_table', 1),
(14, '2023_05_31_192807_create_compras_table', 1),
(15, '2023_05_31_192815_create_ventas_table', 1),
(16, '2023_05_31_192719_create_proveedores_table', 2),
(18, '2023_05_31_192647_create_productos_table', 3),
(21, '2023_06_23_202423_create_razas_table', 4),
(22, '2023_06_24_192733_create_mascotas_table', 4),
(27, '2023_06_29_141902_create_citas_table', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones`
--

CREATE TABLE `opciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_dominio` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `opciones`
--

INSERT INTO `opciones` (`id`, `descripcion`, `id_dominio`, `created_at`, `updated_at`) VALUES
(1, 'Activo', 1, NULL, NULL),
(2, 'Inactivo', 1, NULL, NULL),
(3, 'Activo', 2, '2023-06-17 01:06:04', '2023-06-17 01:06:04'),
(4, 'Inactivo', 2, '2023-06-17 01:06:04', '2023-06-17 01:06:04'),
(18, 'Activo', 4, '2023-06-17 21:37:26', '2023-06-17 21:37:26'),
(19, 'Inactivo', 4, '2023-06-17 21:37:26', '2023-06-17 21:37:26'),
(20, 'Unidad/es', 5, '2023-06-20 14:13:03', '2023-06-20 14:13:03'),
(21, 'Caja/s', 5, '2023-06-20 14:13:03', '2023-06-20 14:13:03'),
(22, 'Kilo/s', 5, '2023-06-20 14:13:03', '2023-06-20 14:13:03'),
(23, 'Paquete/s', 5, '2023-06-20 14:13:03', '2023-06-20 14:13:03'),
(24, 'Milímetro/s', 5, '2023-06-20 14:13:03', '2023-06-20 14:13:03'),
(25, 'Litro/s', 5, '2023-06-20 14:13:03', '2023-06-20 14:13:03'),
(26, 'Anestésicos | Analéptico | Tranquilizantes', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(27, 'Antibióticos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(28, 'Antiinflamatorios | Analgésicos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(29, 'Antimicóticos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(30, 'Antiparasitarios Externos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(32, 'Antisépticos Desinfectantes', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(33, 'Biomoduladores', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(34, 'Cardiológicos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(35, 'Dermatología', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(36, 'Especialidades Ornitológicas', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(37, 'Gastroenterológicos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(38, 'Geriátrico', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(39, 'Odontológicos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(40, 'Oftalmológicos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(41, 'Otológicos', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(42, 'Revitalizantes', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(43, 'Osteoarticular', 3, '2023-06-20 16:59:47', '2023-06-20 16:59:47'),
(45, 'Antiparasitarios internos', 3, '2023-06-20 17:25:29', '2023-06-20 17:25:29'),
(48, 'Frasco/s', 5, '2023-06-22 18:41:01', '2023-06-22 18:41:01'),
(49, 'Blíster/s', 5, '2023-06-23 15:04:04', '2023-06-23 15:04:04'),
(50, 'Macho', 6, '2023-06-23 20:15:42', '2023-06-23 20:15:42'),
(51, 'Hembra', 6, '2023-06-23 20:15:42', '2023-06-23 20:15:42'),
(52, 'Perros', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(53, 'Gatos', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(54, 'Conejos', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(55, 'Gallos y Gallinas', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(56, 'Caballos', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(57, 'Cerdos', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(58, 'Loros', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(59, 'Nutrias', 8, '2023-06-23 20:30:35', '2023-06-23 20:30:35'),
(60, 'Activo', 9, '2023-06-23 20:36:17', '2023-06-23 20:36:17'),
(61, 'Inactivo', 9, '2023-06-23 20:36:17', '2023-06-23 20:36:17'),
(62, 'CONTROL DE CIRUGIA Y TRATAMIENTO', 11, '2023-06-29 14:11:50', '2023-06-29 14:11:50'),
(63, 'CONSULTA A DOMICILIO', 11, '2023-06-29 14:11:50', '2023-06-29 14:11:50'),
(64, 'CONTROL DE VACUNAS', 11, '2023-06-29 14:11:50', '2023-06-29 14:11:50'),
(65, 'CONSULTA GENERAL', 11, '2023-06-29 14:11:50', '2023-06-29 14:11:50'),
(66, 'PROGRAMADA', 10, '2023-06-29 14:14:09', '2023-06-29 14:14:09'),
(67, 'EN ESPERA', 10, '2023-06-29 14:14:09', '2023-06-29 14:14:09'),
(68, 'ATENDIDA', 10, '2023-06-29 14:14:09', '2023-06-29 14:14:09'),
(69, 'CANCELADA', 10, '2023-06-29 14:14:09', '2023-06-29 14:14:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_categoria` bigint(20) UNSIGNED DEFAULT NULL,
  `stock` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_medida` bigint(20) UNSIGNED DEFAULT NULL,
  `pcosto` decimal(10,0) DEFAULT 0,
  `pventa` decimal(10,0) DEFAULT 0,
  `observacion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_estado` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `detalle`, `id_categoria`, `stock`, `id_medida`, `pcosto`, `pventa`, `observacion`, `id_estado`, `created_at`, `updated_at`) VALUES
(10, '132374077389', 'Total full LC suspension Gatos / perros', 'Total full LC suspension Gatos / perros', 45, '10', 21, '0', '0', NULL, 18, '2023-06-20 21:28:16', '2023-06-20 21:28:16'),
(11, '137150035574', 'ACEDAN GOTAS | Frasco gotero por 10 ml.', 'DESCRIPCIÓN\r\nTranquilizante neuroléptico oral en gotas.\r\n\r\nPRESENTACIÓN\r\nFrasco gotero por 10 ml.\r\n\r\nACCIÓN\r\nProduce estado de pasividad y calma.\r\nDisminuye la excitabilidad nerviosa sin provocar embotamiento de la conciencia.\r\nGenera indiferencia al medio, con disminución de la actividad motora.\r\n\r\nACCIONES SECUNDARIAS\r\nDescenso de la presión arterial por bloqueo de receptores alfa periféricos.\r\nAcción antiemética.\r\nAcción antihistamínica.\r\n\r\nINDICACIONES\r\nTranquilizar al animal ante estímulos excitantes del entorno.\r\nFacilitar el manejo del animal en maniobras clínico-quirúrgicas y diagnósticas.\r\nEvitar nauseas y vómitos en viajes por su efecto anticinetósico.\r\nAliviar la excitabilidad por prurito.\r\nPre medicación anestésica.\r\nRelajación uretral.\r\nReducir la tensión arterial.\r\n\r\nFÓRMULA\r\nCada 100 ml de la solución contiene:\r\nAcepromacina maleato 1 g.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros y gatos: 1 a 3 gotas por kilo.\r\nEn animales sensibles comenzar con 1 gota cada 2 kilos de peso. Esperar 45 minutos para incrementar la dosis (es el tiempo máximo de inicio del efecto).\r\n\r\nCONTRAINDICACIONES\r\nPacientes con hipersensibilidad a la acepromacina. Pacientes deshidratados o hipovolémicos. Miocardipatías hipertróficas por producir vasodilatación periférica.Animales con tétanos, intoxicación por organofosforados o estricnina. Cuadros convulsivos. En mielografías.\r\n\r\nEFECTOS COLATERALES\r\nVasodilatación periférica y como consecuencia hipotensión arterial.Bradicardia. Prolapso de la membrana nictitante. En algunos animales puede causar estimulación generalizada del SNC (reacción paradojal).\r\n\r\nRESTRICCIONES DE USO\r\nNo sería conveniente administrarlo a hembras con preñez avanzada.Cachorros menores de 2 meses.\r\n\r\nPRECAUCIÓN\r\nUsar con precaución, administrando la dosis menor, en cachorros menores de 6 meses, geriátricos,animales débiles, con insuficiencia cardíaca, hepática o renal.Reducir la dosis en un 50% en razas sensibles como bóxer, braquicefálicos y gigantes y lebreles. En caso de hipotensión aguda tratar al animal con fluidoterapia hasta su estabilización.', 26, '0.000', 21, '0', '0', NULL, 19, '2023-06-21 22:36:27', '2023-06-22 22:53:43'),
(12, '443260201246', 'CEFALEXINA 500  3 blíster x 10 comp. c/u', 'DESCRIPCIÓN\r\nAntibiótico oral de amplio espectro a base de cefalosporina.\r\n\r\nPRESENTACIÓN\r\nEnvases conteniendo 3 blisters de 10 comprimidos cada uno.\r\n\r\nACCIÓN\r\nBactericida.\r\nInhibe la síntesis y reparación de la pared celular bacteriana.\r\nLa cefalexina se une dentro de la membrana citoplasmática bacteriana a las enzimas encargadas de la síntesis de pared celular, lo que provoca la inhibición y muerte bacteriana.\r\n\r\nINDICACIONES\r\nPara el tratamiento de infecciones de piel, tejidos blandos, osteoarticulares, respiratorias y genitourinarias producidos por microorganismos sensibles a la Cefalexina.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nCefalexina monohidrato 500 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nAdministrar 20-30 mg/kg, equivalente a 1 comprimido/20 kilos, cada 8-12 horas durante 7-10 días.\r\nEn cuadros de piodermitis superficiales, continuar con el tratamiento por 7-10 días luego de la remisión de la dermatitis.\r\nEn cuadros de piodermitis profundas, continuar con el tratamiento por 14-21 días luego de la remisión de la dermatitis.\r\n\r\nCONTRAINDICACIONES\r\nOcasionalmente puede provocar náuseas, diarrea y malestar abdominal. En raras ocasiones se pueden presentar vómitos.\r\n\r\nEFECTOS COLATERALES\r\nOcasionalmente puede provocar náuseas, diarrea y malestar abdominal. En raras ocasiones se pueden presentar vómitos.\r\n\r\nPRECAUCIÓN\r\nUtilizar con precaución en casos de insuficiencia renal.', 27, '0.000', 21, '0', '0', NULL, 18, '2023-06-22 22:33:07', '2023-06-22 22:33:07'),
(13, '638321026575', 'FLOXADAY COMPRIMIDOS 1 blíster x 10 comp.', 'DESCRIPCIÓN\r\nAntibiotico oral de amplio espectro a base de fluoroquinolona.\r\n\r\nPRESENTACIÓN\r\nEnvase conteniendo 1 blister de 10 comprimidos.\r\n\r\nACCIÓN\r\nPotente acción antibiótica. Presenta una actividad superior sobre microorganismos Gram negativos, Gram positivos, anaerobios e intracelulares (E.Coli y otras enterobacterias, Pseudomona aeruginosa, Pasteurella spp. Bordetela bronchiseptica, Staphylococcus spp., Mycoplasma, Chlamydia/Chlamydophila spp. Interfiere en la síntesis del ADN bacteriano, inhibiendo dos importantes enzimas:\r\nTopoisomerasa II (ADN girasa) es responsable de la relajación del ADN super enrollado durante la transcripción.\r\nTopoisomerasa IV nterviene en la separación del ADN cromosonal durante la división celular.\r\n\r\nINDICACIONES\r\nEstá especialmente desarrollado para atacar infecciones de:\r\nPiel (piodermis).\r\nTejidos blandos.\r\nVías aéreas superiores e inferiores.\r\nVías urinarias.\r\nGlándulas mamarias.\r\nHueso (osteomielitis).\r\nPróstata. Septicemias.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nLevofloxacina 100 mg, 200 mg y 400 mg según presentación.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros: 10 mg de levofloxacina por kg de peso, equivalente a un comprimido cada 10,20 o 40 kilos de peso según presentación, via oral cada 24 hs. El pico de concentración se alcanza 1 hora post–ingestión. Su biodisponibilidad es del 60- 70% . La duración del tratamiento variará según la patología y la indicación del profesional.\r\n\r\nCONTRAINDICACIONES\r\nContraindicado en animales con hipersensibildad a las fluoroquinolonas. No administrar en hembras gestantes o en lactancia.\r\n\r\nRESTRICCIONES DE USO\r\nAnimales en fase de crecimiento. Insuficiencia renal.', 27, '0.000', 21, '0', '0', NULL, 18, '2023-06-22 22:34:42', '2023-06-22 22:34:42'),
(14, '368142115978', 'FLOXADAY INYECTABLE | Frasco ampolla por 50 ml.', 'DESCRIPCIÓN\r\nAntibiotico inyectable de amplio espectro a base de fluoroquinolona.\r\n\r\nPRESENTACIÓN\r\nFrasco ampolla por 50 ml.\r\n\r\nACCIÓN\r\nPotente acción antibiótica. Presenta una actividad superior sobre microorganismos Gram negativos, Gram positivos, anaerobios e intracelulares (E.Coli y otras enterobacterias, Pseudomona aeruginosa, Pasteurella spp. Bordetela bronchiseptica, Staphylococcus spp., Mycoplasma, Chlamydia/Chlamydophila spp. Inferfiere en la síntesis del ADN bacteriano, inhibiendo dos importantes enzimas:\r\nTopoisomerasa II (ADN girasa) es responsable de la relajación del ADN super enrollado durante la transcripción.\r\nTopoisomerasa IV nterviene en la separación del ADN cromosonal durante la división celular.\r\n\r\nINDICACIONES\r\nEstá especialmente desarrollado para atacar infecciones de:\r\nPiel (piodermis).\r\nTejidos blandos.\r\nVías aéreas superiores e inferiores.\r\nVías urinarias. Glándulas mamarias.\r\nHueso (osteomielitis).\r\nPróstata.\r\nSepticemias\r\n\r\nFÓRMULA\r\nCada 100 ml de la solución contiene:\r\nLevofloxacina 5 g.\r\nExcipientes c.s\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros: 7.5 mg de levofloxacina por kilo de peso, equivalente a 1.5 ml cada 10 kg de peso cada 24 horas. Su pico de concentración se alcanza a las 2 horas post aplicación. Biodisponibilidad 90% y amplia distribución tisular, debido a su liposolubilidad. La duración del tratamiento dependerá de la patología y la indicación del profesional.\r\n\r\nCONTRAINDICACIONES\r\nContraindicado en animales con hipersensibildad a las fluoroquinolonas. No administrar en hembras gestantes o en lactancia.\r\n\r\nRESTRICCIONES DE USO\r\nAnimales en fase de crecimiento. Insuficiencia renal.', 27, '0.000', 48, '0', '0', NULL, 18, '2023-06-22 22:36:48', '2023-06-22 22:42:07'),
(15, '318890478185', 'ATRIBEN | Frasco ampolla por 20 ml.', 'DESCRIPCIÓN\r\nSuspensión inyectable glucocorticoidea de depósito.\r\n\r\nPRESENTACIÓN\r\nFrasco ampolla por 20 ml.\r\n\r\nACCIÓN\r\nAntiinflamatorio esteroide.La triamcinolona, es un sintético fluorado con una potencia glucocorticoidea 5 veces mayor al cortisol, sin efecto mineralocorticoideo (retención de sodio) y con una duración de acción prolongada por la sal que la acompaña.\r\nAntiprurítico.\r\nAntialérgico\r\nInmunosupresión\r\n\r\nINDICACIONES\r\nProcesos inflamatorios: artritis traumática, tenosinovitis,miositis, etc\r\nDermatitis alérgicas\r\nPrurito inespecífico\r\nNeoplasias\r\nEnfermedades autoinmunes\r\n\r\nFÓRMULA\r\nCada 100 ml de la suspensión contiene:\r\nTriamcinolona acetonida 0,6 g\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros y gatos : como corticoide de depósito: 0,1 a 0.2 mg/kg SC ó IM ( 1 ml cada 30-60 kg) cada 15 días. Intraarticular: 1mg c/1cm lesión cada 15 días.\r\n\r\nCONTRAINDICACIONES\r\nEnfermedades infecciosas virales y micóticas\r\nDemodeccia\r\nGastritis y úlceras gastrointestinales\r\nColitis ulcerativa\r\nPancreatitis\r\nInsuficiencia renal\r\nAmiloidosis\r\nDiabetes mellitus\r\nOsteoporosis\r\nArtritis crónica erosiva\r\nInfección o fractura intrarticular (vía intraart)\r\nÚlcera corneana\r\nHiperadrenocorticismo\r\nInfección viral o bacteriana no controlada\r\nTuberculosis\r\nGestación (último tercio)\r\n\r\nEFECTOS COLATERALES\r\nPuede generar un aumento en la gluconeogénesis, catabolismo proteico y lipólisis. Puede producir hepatomegalia e hiperglucemia con poliuria/polidipsia/polifagia Inducción de la FAS en caninos. Supresión del sistema inmune e inflamatorio. Cambios de comportamiento.\r\n\r\nRESTRICCIONES DE USO\r\nUltimo tercio de la gestación. Animales inmunosuprimidos. Hiperadrenocorticismo.', 28, '0.000', 48, '0', '0', NULL, 18, '2023-06-22 22:45:25', '2023-06-22 22:45:46'),
(16, '114644138588', 'BUTORMIN | Frasco ampolla por 10 ml.', 'DESCRIPCIÓN\r\nAnalgésico inyectable a base de butorfanol.\r\n\r\nPRESENTACIÓN\r\nFrasco ampolla por 10 ml.\r\n\r\nACCIÓN\r\nAnalgésico, opioide sintético agonista-antagonista, con una potencia mayor a la de la morfina y mínimos efectos cardiovasculares.AGONISTA KAPPA: El estimulo de los receptores Kappa produce analgesia espinal y sedación con menor depresión respiratoria y miosis. ANTAGONISTA MU El estímulo de los receptores MU provoca analgesia supraespinal, depresión respiratoria y miosis, dependecia física y euforia.\r\n\r\nINDICACIONES\r\nAnalgesia pre, intra y postquirúrgico. Se puede administrar junto con la premedicación.\r\nPotenciar los efectos sedantes de los tranquilizantes : Midazolam - Diazepam- Acepromazina, con esta última, mejora la sedación sobre todo en razas caninas medianas y grandes.\r\nPuede combinarse durante la anestesia con Ketamina.\r\n\r\nFÓRMULA\r\nCada 100 ml de la solución contiene:\r\nButorfanol tartrato 1,02 g.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros: Preanestésico: 0.1 a 0.2 mg / kg . Analgesia: 0,2 a 0,4 mg/kg por vía endovenosa,intramuscular o subcutánea.\r\nGatos: Preanestesia: 0,1 a 0,2 mg/kg vía intramuscular. Analgesia: 0,1 a 0,2 mg/kg vía endovenosa o intramuscular ó 0,4 mg/kg vía subcutánea.\r\nAplicado intramuscular, la analgesia se logra a los 30 minutos, alcanzando el pico a la hora. En administración endovenosa el efecto inmediato. Se sugiere administrar lentamente por esta vía. No produce depresión respiratoria.\r\n\r\nCONTRAINDICACIONES\r\nNo debe usarse en pacientes con hepatopatía severa, insuficiencia renal grave, insuficiencia cardíaca o enfermedades respiratorias obstructivas. No usar en hembras preñadas ya que no hay estudios sobre sus efectos durante esta condición.\r\n\r\nEFECTOS COLATERALES\r\nPodría generar ataxia y anorexia. En raras ocasiones diarrea o disminución de la motilidad intestinal. En gatos, el butorfanol puede causar excepcionalmente excitación y midriasis.\r\n\r\nRESTRICCIONES DE USO\r\nEn animales con hipotiroidismo, insuficiencia renal grave y en gerontes.\r\n\r\nPRECAUCIÓN\r\nEn animales sensibles al butorfanol. Utilizar con precaución en animales cachorros.', 28, '0.000', 48, '0', '0', NULL, 18, '2023-06-22 22:47:06', '2023-06-23 20:45:21'),
(17, '792342408980', 'PREDNISOLONA 20 MG. | 1 blíster con 10 comprimidos.', 'DESCRIPCIÓN\r\nGlucocorticoide sintético en comprimidos.\r\n\r\nPRESENTACIÓN\r\nEnvase conteniendo 1 blister con 10 comprimidos.\r\nDisplay con 30 blísters con 10 comprimidos cada uno.\r\n\r\nACCIÓN\r\nGlucocorticoide sintético no fluorado con una potencia glucocorticoidea 4 veces mayor al Cortisol, pero con un efecto mineralocorticoide casi nulo:\r\nAntiinflamatorio.\r\nAntipruriginoso.\r\nAntialérgico.\r\nInmunosupresor.\r\n\r\nINDICACIONES\r\nProcesos inflamatorios y alérgicos (prurito) en donde esté indicado el uso de Glucocorticoides.\r\nEnfermedadesautoinmunes.\r\nComo coadyuvante en la terapéutica antineoplásica.\r\nTerapia de sustitución.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nPrednisolona 20 mg\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros & Gatos: Terapia de sustitución: 0,25 mg/kg/día. En estados de estrés, aumentar la dosis 2-5 veces. Enfermedades autoinmunes y como coadyuvante de la terapéutica antineoplásica: 2-4 mg/kg/día. En procesos inflamatorios: 0,5-1 mg/kg/día.Para un tratamiento prolongado se debe realizar la terapia a días alternos. Este esquema evita la supresión del eje Hipotálamo-hipofiso-adrenal permitiendo la recuperación del mismo en los días que no se administra laPrednisolona.\r\nEsquema terapéutico sugerido:\r\n1) Dosis de inducción 0,5-1 mg/kg/12 hs por 5-7 días. 2) Continuar con 1-2 mg/kg 1 vez al día cada 48 hs durante 7 días. 3) Seguir con el mismo régimen de administración (cada 48 hs) pero bajar la dosis un 50% por 7 días. 4) Reducir semanalmente la cantidad de Prednisolona (pero siempre a días alternos) hasta llegar a una dosis mínima demantenimiento pero de efecto terapéutico buscado. 5) No interrumpir en forma súbita la administración sino gradualmente.\r\n\r\nCONTRAINDICACIONES\r\nEnfermedades infecciosas bacterianas, virales y micóticas. Demodeccia. Úlceras gastrointestinales. Colitis ulcerativa. Pancreatitis. Insuficiencia renal. Amiloidosis. Diabetes mellitus.Osteoporosis. Artritis crónica erosiva. Gestación.\r\n\r\nEFECTOS COLATERALES\r\nPoliuria. Polidipsia. Polifagia. Euforia.\r\n\r\nRESTRICCIONES DE USO\r\nPreñez y lactancia.\r\n\r\nPRECAUCIÓN\r\nLa terapia a largo plazo, a dosis altas y en forma indiscriminada da como resultado el síndrome de Cushing (hiperadrenocorticismo iatrogénico) concomitante con una insuficiencia adrenal secundaria. La interrupción súbita de la administración de glucocorticoides puede provocar un síndrome de Addison(hipoadrenocorticismo) por hipofunción del eje hipotálamo-hipófiso-adrenal.', 28, '0.000', 21, '0', '0', NULL, 18, '2023-06-22 22:48:52', '2023-06-22 22:48:52'),
(18, '380778508696', 'GRISEOFULVINA 250 MG. | 2 blisters de 10 comprimidos cada uno', 'DESCRIPCIÓN\r\nFungistático de administración oral a base de Griseofulvina micronizada\r\n\r\nPRESENTACIÓN\r\nEnvase con 2 blisters de 10 comprimidos cada uno.\r\n\r\nACCIÓN\r\nFungistático. Interfiere en la síntesis de las proteínas y los ácidos nucleicos de la pared celular de hongos en crecimiento activo. Después de su administración por vía oral se absorbe a nivel gastrointestinal, depositándose selectivamente en la queratina neoformada del pelo, uñas y piel, pasando luego de estas capas profundas a la queratina superficial. Por lo tanto la concentración de Griseofulvina en las nuevas células de la epidermis, le proporciona actividad micostática contra los dermatofitos.\r\n\r\nINDICACIONES\r\nTiña. Dermatomicosis causadas por Microsporum canis,Microsporum gypseum y Tricophyton mentagrophytes.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\n\r\nGriseofulvina micronizada 250 mg./500 mg.\r\n\r\nExcipientes c.s. según presentación\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nAdministrar 20-50 mg./kg. dividida en 2 tomas diarias por un período no menor de 4-6 semanas, salvo en caso de “onicomicosis” cuyo tratamiento debe realizarse por 6-12 meses. La absorción de la griseofulvina micronizada puede ser mejorada con la ingesta de una alimentación rica en grasa.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar durante la preñez por los riesgos de producir efectos teratogénicos.\r\n\r\nEFECTOS COLATERALES\r\nAlteraciones gastrointestinales: nauseas, vómitos, diarrea, flatulencia,polidipsia (más frecuentes en felinos).Estos efectos ceden rápidamente al suspender la medicación.\r\n\r\nRESTRICCIONES DE USO\r\nEnfermedad hepática.', 29, '0.000', 21, '0', '0', NULL, 18, '2023-06-22 22:51:21', '2023-06-22 22:51:21'),
(19, '379865226383', 'IVERMECTINA 250 MCG. | 1 blíster con 6 comprimidos.', 'DESCRIPCIÓN\r\nAntiparasitario interno oral para el control de filariasis.\r\n\r\nPRESENTACIÓN\r\nEnvase conteniendo 1 blister con 6 comprimidos.\r\n\r\nACCIÓN\r\nAntiparasitario interno. Microfilaricida. Preventivo de filaria. La ivermectina provoca la inmovilización de los parásitos induciendo una parálisis tónica de la musculatura, mediada por la potencialización y/o activación directa de los canales de cloro sensibles a la ivermectina controlados por el glutamato.\r\n\r\nINDICACIONES\r\nComo microfilaricida y preventivo de la filariasis en perros, cuyo agente etiológico es la Dirofilaria immitis.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nIvermectina 250 mcg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nTratamiento preventivo: 6 mcg/kg (1 comprimido de Ivermectina cada 40 kg). Una sola toma cada 30 días (no excederse de este intervalo posológico) de por vida todo el año. Se puede administrar con el alimento. Si se sospecha que el animal consumió sólo una parte del producto, se recomienda volver a administrar la dosis.\r\nTratamiento: microfilaricida:50 mcg/kg (1 comprimido de Ivermectina cada 5 kg ) en 1 sola toma. Realizar el Test de Knott modificado a las 2-3 semanas post tratamiento. Si da positivo, repetir la toma; si es negativo realizar el tratamiento preventivo.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar en dosis superiores a las microfilaricidas a perros que portadores de mutación en el gen MDR-1 , que aumenta permeabilidad de la barrera hematoencefálica a las lactonas macrocíclicas ( collie, border collie, viejo pastor inglés o sus cruzas).\r\n\r\nEFECTOS COLATERALES\r\nHipersensibilidad a las microfilarias muertas Luego de la administración se puede presentar:\r\n1) Reacción aguda: Shock, generalmente a las 5 horas de su administración. Realizar terapéutica para el shock.\r\n2) Reacción leve: Anorexia, letargia, midriasis, ataxia, vómitos, tos y disnea. Estos signos son transitorios y aparecen 24-48 horas post tratamiento.\r\n\r\nRESTRICCIONES DE USO\r\nNo utilizar en cachorros menores de 12 semanas.', 45, '0.000', 49, '0', '0', NULL, 18, '2023-06-23 19:05:03', '2023-06-23 19:05:19'),
(20, '597243937017', 'TOTAL FULL CG PERROS Y GATOS | Frasco por 15 ml. con jeringa dosificadora.', 'DESCRIPCIÓN\r\nAntiparasitario interno en suspensión oral.\r\n\r\nPRESENTACIÓN\r\nFrasco por 15 y 150 ml. con jeringa dosificadora.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nToltrazuril: presenta acción de amplio espectro anticoccidiósico y actividad antiprotozoaria. Como coccidicida,actúa en los distintos estadíos, tanto en la fase asexuada como sexual, impidiendo la replicación. Produce una disminución de la actividad enzimática de la mitocondria con compromiso del metabolismo respiratorio y de la síntesis de ácidos nucleicos que se traduce en la destrucción del parásito.\r\nFenbendazol: Antihelmíntico. vermicida, larvicida y ovicida Actúa por unión a las proteínas que componen los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria, lo cuál provoca un disminución de la energía y muerte del parásito.\r\n\r\nINDICACIONES\r\nPara tratar gatos y perros parasitados con coccidios (Isospora spp); protozoos (Giardia spp) y Nematodos (Toxocara spp - Toxascaris spp - Ancylostoma spp - Trichuris spp).\r\n\r\nFÓRMULA\r\nCada 100 ml de la solución contiene:\r\nFenbendazol 5 g.\r\nToltrazuril 2 g.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros & Gatos: Coccidios: en base al toltrazuril, 20 mg/kg de peso (equivalentes a 1 ml de la suspensión oral por kgde peso). Dos tomas con un intervalo de 7 días.\r\nGiardias y nematodos: en base al fenbendazol, 50 mg/kg de peso (equivalentes a 1 ml de la suspensión oral por kg de peso).Una toma cada 24 hs durante tres días consecutivos.\r\nPara control de Ascaris y Ancylostoma, repetir entre 14 y 21 días, para Trichuris repetir a los 30 y 60 días. Agitar bien antes de usar. La presencia de alimentos no altera su absorción.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a cachorros menores de 20 días de vida.\r\n\r\nEFECTOS COLATERALES\r\nEn pocos casos puede registrarse algún vómito y/ o diarrea.\r\n\r\nRESTRICCIONES DE USO\r\nSe sugiere no administrar a hembras preñadas debido a que no hay estudios realizados que avalen su uso en ese estadio.', 45, '0.000', 48, '0', '0', NULL, 18, '2023-06-23 19:09:12', '2023-06-23 19:09:12'),
(21, '261716053611', 'TOTAL FULL CG PERROS Y GATOS | Frasco por 150 ml. con jeringa dosificadora.', 'DESCRIPCIÓN\r\nAntiparasitario interno en suspensión oral.\r\n\r\nPRESENTACIÓN\r\nFrasco por 15 y 150 ml. con jeringa dosificadora.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nToltrazuril: presenta acción de amplio espectro anticoccidiósico y actividad antiprotozoaria. Como coccidicida,actúa en los distintos estadíos, tanto en la fase asexuada como sexual, impidiendo la replicación. Produce una disminución de la actividad enzimática de la mitocondria con compromiso del metabolismo respiratorio y de la síntesis de ácidos nucleicos que se traduce en la destrucción del parásito.\r\nFenbendazol: Antihelmíntico. vermicida, larvicida y ovicida Actúa por unión a las proteínas que componen los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria, lo cuál provoca un disminución de la energía y muerte del parásito.\r\n\r\nINDICACIONES\r\nPara tratar gatos y perros parasitados con coccidios (Isospora spp); protozoos (Giardia spp) y Nematodos (Toxocara spp - Toxascaris spp - Ancylostoma spp - Trichuris spp).\r\n\r\nFÓRMULA\r\nCada 100 ml de la solución contiene:\r\nFenbendazol 5 g.\r\nToltrazuril 2 g.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros & Gatos: Coccidios: en base al toltrazuril, 20 mg/kg de peso (equivalentes a 1 ml de la suspensión oral por kgde peso). Dos tomas con un intervalo de 7 días.\r\nGiardias y nematodos: en base al fenbendazol, 50 mg/kg de peso (equivalentes a 1 ml de la suspensión oral por kg de peso).Una toma cada 24 hs durante tres días consecutivos.\r\nPara control de Ascaris y Ancylostoma, repetir entre 14 y 21 días, para Trichuris repetir a los 30 y 60 días. Agitar bien antes de usar. La presencia de alimentos no altera su absorción.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a cachorros menores de 20 días de vida.\r\n\r\nEFECTOS COLATERALES\r\nEn pocos casos puede registrarse algún vómito y/ o diarrea.\r\n\r\nRESTRICCIONES DE USO\r\nSe sugiere no administrar a hembras preñadas debido a que no hay estudios realizados que avalen su uso en ese estadio.', 45, '0.000', 48, '0', '0', NULL, 18, '2023-06-23 19:09:40', '2023-06-23 19:09:40'),
(22, '878138753193', 'TOTAL FULL LC PERROS | 1 blíster por 2 comprimidos p/ perros chicos hasta 10 kg', 'DESCRIPCIÓN\r\nAntiparasitario interno oral de liberación controlada en comprimidos birranurados palatables.\r\n\r\nPRESENTACIÓN\r\nTotal Full LC perros chicos y medianos: Envase conteniendo 1 blister por 2 comprimidos birranurados palatables para perros hasta 10 kg y 20 kg respectivamente.\r\nTotal Full LC perros grandes: Envase conteniendo 1 blister por 3 comprimidos birranurados palatables para perros hasta 60 kg.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nFenbendazol: Actúa por unión a la tubulina, proteína que compone los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria, lo que genera disminución de energía y muerte.\r\nPirantel: Estimula la liberación de la acetilcolina, inhibiendo la colintesterasa y provocando un bloqueo neuromuscular en los helmintos. En consecuencia provoca parálisis y desprendimiento de los parásitos.\r\nPrazicuantel: Incrementa la permeabilidad del parásito al calcio, produciendo parálisis espástica y daño tegumental.\r\n\r\nINDICACIONES\r\nPrevención y tratamiento de cestodes, nematodes y giardias (formas quísticas). Cestodes:Dipylidium caninum,Taenia sp. Nematodes: Toxocara canis, Toxascaris leonina, Ancylostoma caninum Trichuris vulpis.\r\n\r\nFÓRMULA\r\nCada comprimido según presentación contiene:\r\nFenbendazol 250 mg/500mg/1000mg.\r\nPirantel base (como pamoato) 25 mg/50mg /100 mg.\r\nPraziquantel 25 mg/ 50mg/100mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nCestodes y Nematodes: 1 comprimido cada 5 kg, 10kg ó 20 kg de peso vivo ( según presentación). Única toma. Ante infecciones severas repetir la dosis a las 24 hs.\r\nGiardias: Suministrar durante 3 días. En todos los casos,repetir la desparasitación a los 15 – 21 días.\r\n\r\nCONTRAINDICACIONES\r\nAnimales sensibles a alguno de los principios activos.\r\n\r\nEFECTOS COLATERALES\r\nOcasionalmente se puede presentar hipersalivación, náusea, vómito o diarrea, que remiten espontáneamente.\r\n\r\nRESTRICCIONES DE USO\r\nPreñez y lactancia: puede administrarse a hembras preñadas a partir del día 41 de gestación. También puede utilizarse durante la lactancia, en machos reproductores y en cachorros a partir de los 20 días de vida.', 45, '0.000', 49, '0', '0', NULL, 18, '2023-06-23 19:13:08', '2023-06-23 19:23:23'),
(23, '503951887831', 'TOTAL FULL LC PERROS | 1 blíster por 2 comprimidos p/ perros medianos hasta 20 kg', 'DESCRIPCIÓN\r\nAntiparasitario interno oral de liberación controlada en comprimidos birranurados palatables.\r\n\r\nPRESENTACIÓN\r\nTotal Full LC perros chicos y medianos: Envase conteniendo 1 blister por 2 comprimidos birranurados palatables para perros hasta 10 kg y 20 kg respectivamente.\r\nTotal Full LC perros grandes: Envase conteniendo 1 blister por 3 comprimidos birranurados palatables para perros hasta 60 kg.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nFenbendazol: Actúa por unión a la tubulina, proteína que compone los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria, lo que genera disminución de energía y muerte.\r\nPirantel: Estimula la liberación de la acetilcolina, inhibiendo la colintesterasa y provocando un bloqueo neuromuscular en los helmintos. En consecuencia provoca parálisis y desprendimiento de los parásitos.\r\nPrazicuantel: Incrementa la permeabilidad del parásito al calcio, produciendo parálisis espástica y daño tegumental.\r\n\r\nINDICACIONES\r\nPrevención y tratamiento de cestodes, nematodes y giardias (formas quísticas). Cestodes:Dipylidium caninum,Taenia sp. Nematodes: Toxocara canis, Toxascaris leonina, Ancylostoma caninum Trichuris vulpis.\r\n\r\nFÓRMULA\r\nCada comprimido según presentación contiene:\r\nFenbendazol 250 mg/500mg/1000mg.\r\nPirantel base (como pamoato) 25 mg/50mg /100 mg.\r\nPraziquantel 25 mg/ 50mg/100mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nCestodes y Nematodes: 1 comprimido cada 5 kg, 10kg ó 20 kg de peso vivo ( según presentación). Única toma. Ante infecciones severas repetir la dosis a las 24 hs.\r\nGiardias: Suministrar durante 3 días. En todos los casos,repetir la desparasitación a los 15 – 21 días.\r\n\r\nCONTRAINDICACIONES\r\nAnimales sensibles a alguno de los principios activos.\r\n\r\nEFECTOS COLATERALES\r\nOcasionalmente se puede presentar hipersalivación, náusea, vómito o diarrea, que remiten espontáneamente.\r\n\r\nRESTRICCIONES DE USO\r\nPreñez y lactancia: puede administrarse a hembras preñadas a partir del día 41 de gestación. También puede utilizarse durante la lactancia, en machos reproductores y en cachorros a partir de los 20 días de vida.', 45, '0.000', 49, '0', '0', NULL, 18, '2023-06-23 19:19:21', '2023-06-23 19:22:50'),
(24, '509570889686', 'TOTAL FULL LC PERROS | 1 blíster por 3 comprimidos p/ perros grandes hasta 60 kg', 'DESCRIPCIÓN\r\nAntiparasitario interno oral de liberación controlada en comprimidos birranurados palatables.\r\n\r\nPRESENTACIÓN\r\nTotal Full LC perros chicos y medianos: Envase conteniendo 1 blister por 2 comprimidos birranurados palatables para perros hasta 10 kg y 20 kg respectivamente.\r\nTotal Full LC perros grandes: Envase conteniendo 1 blister por 3 comprimidos birranurados palatables para perros hasta 60 kg.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nFenbendazol: Actúa por unión a la tubulina, proteína que compone los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria, lo que genera disminución de energía y muerte.\r\nPirantel: Estimula la liberación de la acetilcolina, inhibiendo la colintesterasa y provocando un bloqueo neuromuscular en los helmintos. En consecuencia provoca parálisis y desprendimiento de los parásitos.\r\nPrazicuantel: Incrementa la permeabilidad del parásito al calcio, produciendo parálisis espástica y daño tegumental.\r\n\r\nINDICACIONES\r\nPrevención y tratamiento de cestodes, nematodes y giardias (formas quísticas). Cestodes:Dipylidium caninum,Taenia sp. Nematodes: Toxocara canis, Toxascaris leonina, Ancylostoma caninum Trichuris vulpis.\r\n\r\nFÓRMULA\r\nCada comprimido según presentación contiene:\r\nFenbendazol 250 mg/500mg/1000mg.\r\nPirantel base (como pamoato) 25 mg/50mg /100 mg.\r\nPraziquantel 25 mg/ 50mg/100mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nCestodes y Nematodes: 1 comprimido cada 5 kg, 10kg ó 20 kg de peso vivo ( según presentación). Única toma. Ante infecciones severas repetir la dosis a las 24 hs.\r\nGiardias: Suministrar durante 3 días. En todos los casos,repetir la desparasitación a los 15 – 21 días.\r\n\r\nCONTRAINDICACIONES\r\nAnimales sensibles a alguno de los principios activos.\r\n\r\nEFECTOS COLATERALES\r\nOcasionalmente se puede presentar hipersalivación, náusea, vómito o diarrea, que remiten espontáneamente.\r\n\r\nRESTRICCIONES DE USO\r\nPreñez y lactancia: puede administrarse a hembras preñadas a partir del día 41 de gestación. También puede utilizarse durante la lactancia, en machos reproductores y en cachorros a partir de los 20 días de vida.', 45, '0.000', 49, '0', '0', NULL, 18, '2023-06-23 19:22:02', '2023-06-23 19:22:02'),
(25, '555863692534', 'TOTAL FULL LC GATOS | 1 blíster con 2 comprimidos.', 'DESCRIPCIÓN\r\nAntiparasitario interno oral de liberación controlada en comprimidos birranurados palatables.\r\n\r\nPRESENTACIÓN\r\nEnvase conteniendo 1 blister con 2 comprimidos.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nFenbendazol: Actúa por unión a la tubulina, proteína que compone los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria lo que genera disminución de energía y muerte.\r\nPirantel: Estimula la liberación de la acetilcolina, inhibiendo la colintesterasa y provocando un bloqueo neuromuscular en los helmintos. En consecuencia actúa como un bloqueando neuromuscular en los helmintos, produciendo un aumento de la tensión muscular, que provoca parálisis y desprendimiento de los parásitos.\r\nPrazicuantel: Incrementa la permeabilidad del parásito al calcio, produciendo parálisis espástica y daño tegumental.\r\n\r\nINDICACIONES\r\nPrevención y tratamiento de cestodes, nematodes. Cestodes: Dipylidium caninum, Taenia spp. Nematodes: Toxocara cati, Toxascarias leonina, Ancylostoma spp.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nFenbendazol 200 mg.\r\nPirantel base (como pamoato) 80 mg.\r\nPraziquantel 20 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nUn comprimido cada 4 kilos de peso vivo, equivalente a: Praziquantel: 5mg/kg de peso vivo Pirantel base (como pamoato): 20mg/kg de peso vivo Fenbendazol: 50 mg/kg de peso vivo. Una toma única.\r\nEn infestaciones severas repetir la dosis a las 24 horas Repetir la desparasitación a los 15-21 días.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a animales con sensibilidad conocida a alguno de los principios activos.\r\n\r\nEFECTOS COLATERALES\r\nOcasionalmente se puede presentar hipersalivación, náusea, vómito o diarrea, que remiten espontáneamente.En raras ocasiones en gatos sensibles pueden presantarse cuadros de letargia y ataxia.\r\n\r\nRESTRICCIONES DE USO\r\nGatitos de menos de 20 días. Gatas preñadas hasta los 41 días.\r\n\r\nPRECAUCIÓN\r\nUtilizar con precaución en animales con insuficiencia hepática y renal. Animales debilitados y convalecientes.', 45, '0.000', 49, '0', '0', NULL, 18, '2023-06-23 19:24:59', '2023-06-23 19:24:59'),
(26, '725188162994', 'TOTAL FULL SUSPENSIÓN GATOS | suspensión oral', 'DESCRIPCIÓN\r\nAntiparasitario interno en suspensión oral.\r\n\r\nPRESENTACIÓN\r\nAntiparasitario interno en suspensión oral.\r\n\r\nACCIÓN\r\nAntiparasitario interno de amplio espectro.\r\nFenbendazol: Actúa por unión a la tubulina, proteína que compone los microtúbulos del parásito, inhibiendo la captación de glucosa parasitaria lo que genera disminución de energía y muerte.\r\nPirantel: Estimula la liberación de la acetilcolina, inhibiendo la colintesterasa y provocando un bloqueo neuromuscular en los helmintos. En consecuencia,provoca parálisis y desprendimiento de los parásitos.\r\nPrazicuantel: Incrementa la permeabilidad del parásito al calcio, produciendo parálisis espástica y daño tegumental.\r\n\r\nINDICACIONES\r\nInfestaciones por cestodes (Dipylidium caninum), nematodes (Toxacara cati, Toxascaris leonina y Ancylostoma sp.). Se puede administrar en hembras gestantes a partir del día 41 de preñez, también durante la lactancia.\r\n\r\nFÓRMULA\r\nCada 100 ml de la suspensión contiene:\r\nFenbendazol 5 g.\r\nPirantel base (como pamoato) 2 g.\r\nPraziquantel 0,5 g.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nGatos cachorros y adultos: 1 ml/kg de peso vivo en una sola toma. En infestaciones severas repetir la dosis a las 24 hs. Desparasitar nuevamente entre los 15 y 21 días. En caso de giardias, suministrar durante 3 días.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a animales con sensibilidad conocida a alguno de los principios activos.\r\n\r\nEFECTOS COLATERALES\r\nOcasionalmente se puede presentar hipersalivación, náusea, vómito o diarrea, que remiten espontáneamente.\r\n\r\nRESTRICCIONES DE USO\r\nGatitos de menos de 20 días. Gatas preñadas hasta los 41 días.\r\n\r\nPRECAUCIÓN\r\nUtilizar con precaución en animales con insuficiencia hepática y renal. Animales debilitados y convalecientes.', 45, '0.000', 48, '0', '0', NULL, 18, '2023-06-23 19:29:17', '2023-06-23 19:29:17'),
(27, '251828573801', 'ANAVIMIN COAT COMPRIMIDOS | 3 blisters de 7 comprimidos', 'DESCRIPCIÓN\r\nBiomodulador dermacosmético en comprimidos palatables, que ayuda a mantener la salud de la dermis y del manto.\r\n\r\nPRESENTACIÓN\r\nCaja con 3 blisters de 7 comprimidos cada uno.\r\n\r\nACCIÓN\r\nMantiene la salud de la piel.Realza la belleza del pelo brindándole más brillo y suavidad. Recuperación de la estructura y del metabolismo cutáneo.Restablece la barrera epidérmica, normalizando producción de sebo y mejorando la cicatrización .\r\nVitamina A: queratinización, humectación, elasticidad, producción de sebo, antioxidante.\r\nVitamina C: Antioxidante, síntesis y reparación del colágeno.\r\nVitamina E: antioxidante. mejora metabolismo de vit A\r\nZinc: Desarrollo del pelo, estimula glándulas sebáceas\r\nCistina: queratinización\r\nVit B2 y B5 : ceramidas Biotina: crecimiento piloso\r\nProlina: componente del colágeno, junto con ácido pantoténico forma barrera cutánea ( ceramidas).\r\n\r\nINDICACIONES\r\nPérdida de pelo y falta de brillo por recambio estacional, stress, estados carenciales, alergias.\r\n\r\nPuede utilizarse como complemento en los tratamientos de dermopatías.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\n\r\nVitamina A 5000 UI\r\nÁcido Ascórbico 150 mg.\r\nL-cistina 150 mg.\r\nProlina 75 mg.\r\nVitamina E 25 mg.\r\nPantotenato de Calcio 15 mg.\r\nVitamina B2 2mg.\r\nBiotina 1 mg.\r\nGluconato de Zinc 30 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros hasta 10 kg: 1/2 comp. por día.\r\nPerros de 10 a 30 kg: 1 comp. por día.\r\nPerros de más de 30 kg: 2 comp. por día.\r\nGatos: 1/4 a 1/2 comp. por día.\r\nEn casos de animales resistentes a recibir medicación por vía oral, el comprimido puede molerse hasta obtener un polvo, que puede mezclarse en la comida.\r\n\r\nCONTRAINDICACIONES\r\nHipersensibilidad a algunos de los componentes.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 19:31:07', '2023-06-23 19:32:56'),
(28, '538582298927', 'ENZIMAX | 2 blíster de 10 comprimidos birranurados', 'DESCRIPCIÓN\r\nBiomodulador formulado con enzimas proteolíticas de origen vegetal en comprimidos de administración oral.\r\n\r\nPRESENTACIÓN\r\nEnvase conteniendo 2 blisters de 10 comprimidos birranurados.\r\n\r\nACCIÓN\r\nSuplemento dietario que colabora en el proceso digestivo, y ayuda a las dietas indicadas para trastornos gastrointestinales (tales como insuficiencias pancreáticas, síndrome de mala digestión mala absorción, flatulencias). Las enzimas ayudan a metabolizar nutrientes complejos en simples , favoreciendo de esta manera su absorción.Además colaboran en mantener la salud general de perros y gatos.\r\n\r\nINDICACIONES\r\nIndicado para situaciones en donde se quiera mejorar la digestión de dietas caseras o comerciales, puede contribuir mejorando la absorción de nutrientes y disminuyendo hinchazón y flatulencias.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nBromelina 32 mg (Equivalente a 80 unidades FIP)\r\nPapaina 1,6 mg (Equivalente a 48000 unidades USP)\r\nBetaina HCl 5 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros y gatos: Perros hasta 5 kilos: ½ comprimido por día.\r\nPerros hasta 10 kilos: 1 comprimido por día.\r\nPerros más de 10 kilos: 2 comprimidos por día.\r\nGatos: ½ comprimido por día.\r\nAdministrar antes de la comida principal. Idealmente 20 minutos previos a la ingesta.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a animales hipersensibles a alguno de sus componentes.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.\r\n\r\nRESTRICCIONES DE USO\r\nNo presenta.\r\n\r\nPRECAUCIÓN\r\nNo presenta.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 19:32:23', '2023-06-23 19:32:23'),
(29, '540142266687', 'IQ 180 | 3 blisters de 7 comprimidos cada uno', 'DESCRIPCIÓN\r\nBiomodulador activador de la función neuronal en comprimidos palatables.\r\n\r\nPRESENTACIÓN\r\nCaja con 3 blisters de 7 comprimidos cada uno.\r\n\r\nACCIÓN\r\nMejora la conducta social, la memoria y la atención. Antioxidante. Regenerador celular. Disminuye el stress por ansiedad. Ácido alfa lipoico antioxidante que otorga protección neuronal, ante la potencial toxicidad Beta amiloide y de peróxido de hidrogeno. Disminuye los niveles de lipofucsina. L Glutamina: antioxidante endógeno que protege las neuronas. Nicotinamida: Regula fluidez de membranas neuronales, protege las neuronas colinérgicas y células piramidales, aumenta síntesis y liberación del factor neurotrófico. Ac Ascorbico: antioxidante, regula la sintesis de vit E ATP: Reduce inflamación neuronal, disminuye muerte neuronal, mejora la transmisión colinérgica y mejora la función cognitiva.\r\n\r\nINDICACIONES\r\nSíndrome senil, hipoacusia senil, desorientación, cambios de personalidad. Ayuda a prevenir la degeneración de los procesos cognitivos.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nÁcido Ascórbico 150 mg.\r\nL-Glutamina 100 mg.\r\nNicotinamida 30 mg.\r\nÁcido alfa lipoico 10 mg.\r\nATP 5 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros hasta 10 kg: 1/2 comp. por día.\r\nPerros de 10 a 30 kg: 1 comp. por día.\r\nPerros de más de 30 kg: 2 comp. por día.\r\nGatos: 1/4 a 1/2 comp. por día.\r\nEn casos de animales resistentes a recibir medicación por vía oral, el comprimido puede molerse hasta obtener un polvo, que puede mezclarse en la comida.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a animales hipersensibles a alguno de sus componentes.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.\r\n\r\nPRECAUCIÓN\r\nNo presenta.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 19:34:13', '2023-06-23 19:53:15'),
(30, '241660194401', 'OHM | 3 blíster de 7 comprimidos cada uno', 'DESCRIPCIÓN\r\nBiomodulador de la ansiedad de administración oral en comprimidos palatables.\r\n\r\nPRESENTACIÓN\r\nEnvase conteniendo 3 blisters de 7 comprimidos cada uno.\r\n\r\nACCIÓN\r\nSuplemento dietario modulador de la ansiedad, indicado en aquellas ocasiones en las que el comportamiento del animal se vea afectado por situaciones de estrés o miedo.\r\nEl triptófano induce un aumento en los niveles de serotonina, de esta forma colabora disminuyendo la ansiedad y agresividad en forma natural.\r\nLa valeriana es un relajante natural compuesto por agentes sedantes que actúan sobre el sistema nervioso, colaborando en el tratamiento de la ansiedad.\r\n\r\nINDICACIONES\r\nIndicado para situaciones que generen un aumento en los niveles de estrés y ansiedad tales como:\r\n\r\nViajes\r\nEntornos ruidosos\r\nManejo de animales indóciles en el consultorio\r\nPost-operatorios\r\nEstrés por separación\r\nMudanzas\r\nLlegada de un nuevo habitante al hogar\r\nCambios en el estilo de vida\r\nEstadías en pensionados\r\n\r\nFÓRMULA\r\nCada comprimido de 1500 mg.contiene:\r\nTriptófano 200 mg.\r\nExtracto de Valeriana 100 mg.\r\nExcipientes c.s\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nGatos o perros de 5 kilos o menos: ½ comprimido.\r\nPerros de 5 a 10 kilos: 1 comprimido.\r\nPerros de más de 10 kilos: 2 comprimidos.\r\nEn casos de animales resistentes a recibir medicación por vía oral, el comprimido puede molerse hasta obtener un polvo, que puede mezclarse en la comida. Una vez por día.\r\nPara eventos puntuales: Iniciar la administración por lo menos 2 a 5 días antes del evento planificado o cambio de ambiente. Algunos animales pueden requerir una administración previa (6-10 días). Como modulador de conducta: Suministrarlo diariamente. En algunos casos implementar una terapia de ataque la primer semana suministrándolo cada 12 hs y luego espaciarlo a 24 hs, durante por lo menos 1 mes, para evaluar resultados. Asociarlo con un programa de modificación del comportamiento.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar en animales menores a 8 semanas o en animales con hipersensibilidad a alguno de los componentes.\r\n\r\nEFECTOS COLATERALES\r\nEn algunos casos puede presentarse un grado leve de somnolencia.\r\n\r\nRESTRICCIONES DE USO\r\nSi es posible evitar su uso en animales gestantes o en lactancia.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 20:08:13', '2023-06-23 20:08:13'),
(31, '936322993837', 'OHM GATOS | 1 jeringa dosificadora conteniendo 7 g.', 'DESCRIPCIÓN\r\nBiomodulador coadyuvante en el manejo de la ansiedad en pasta palatable.\r\n\r\nPRESENTACIÓN\r\n1 jeringa dosificadora conteniendo 7 g.\r\n\r\nINDICACIONES\r\nComplemento dietario que colabora modulando la conducta de felinos en situaciones tales como:\r\nViajes\r\nEntornos ruidosos\r\nManejo de animales indóciles en el consultorio\r\nMudanzas\r\nLlegada de un nuevo habitante al hogar\r\nCambios en el estilo de vida\r\nEstadías en pensionados\r\n\r\nFÓRMULA\r\nCada 1 g de pasta palatable:\r\nTriptofano 100 mg\r\nExtracto de raíz de Valeriana 50 mg\r\nTeanina 7,5 mg\r\nExcipientes c.s.\r\nSaborizado con Aceite de Hígado de Bacalao, fuente de Omega 3 \r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\n1 dosis diaria por animal.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar en animales menores a 8 semanas o en animales con hipersensibilidad a alguno de los componentes.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.\r\n\r\nRESTRICCIONES DE USO\r\nNo presenta.\r\n\r\nPRECAUCIÓN\r\nNo se recomienda su uso en animales gestantes o en lactancia.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 20:11:24', '2023-06-23 20:11:24'),
(32, '364404809370', 'OL TRANS FLEX | 3 blíster por 7 comprimidos cada uno.', 'DESCRIPCIÓN\r\nCondroprotector en comprimidos palatables para perros.\r\n\r\nPRESENTACIÓN\r\nDos presentaciones: Envase conteniendo 3 y 10 blisters por 7 comprimidos cada uno.\r\n\r\nACCIÓN\r\nCondroprotector. Nutracéutico. Conserva la salud articular y previene procesos degenerativos osteoarticulares, limita la progresión de la lesión. Reduce el dolor y favorece la biomecánica articular Aumenta la movilidad y mejora la lubricación de la articulación. Recupera la funcionalidad articular y evita la pérdida de masa muscular por desuso MSM: colabora en la síntesis de condroitin sulfato, antiinflamtorio, analgésico, mejora el flujo sanguíneo y reduce el espasmo muscular Glucosamina: forma parta de los glucosaminoglucanos del cartílago.Estimula a los condrocitos en la síntesis de colágeno y proteoglicanos Acido ascórbico: Función antioxidante y estimulante de la síntesis de colágeno Manganeso: participa de la síntesis de condroitin sulfato,de ácido hialurónico y de colágeno.\r\n\r\nINDICACIONES\r\nIndicado en el tratamiento de la osteoartritis crónica, displasia de cadera, espondiloartrosis, reparación de ligamentos y tendones, fracturas Preventivo de procesos osteoarticulares en animales senior. Durante la fase de crecimiento de cachorros, para fortalecer la salud osteoarticular.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nGlucosamina Sulfato 440 mg.\r\nMetilsulfonilmetano (MSM) 400 mg.\r\nÁcido Ascórbico 66 mg.\r\nManganeso Gluconato 10 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros: 1 comprimido palatable cada 20 kg de peso cada 24 horas. No menos de 4-6 semanas. El tratamiento se prolongará dependiendo de la evolución del cuadro clínico y el criterio del médico veterinario actuante.\r\n\r\nCONTRAINDICACIONES\r\nNo presenta contraindicaciones, posee alto margen de seguridad.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.\r\n\r\nRESTRICCIONES DE USO\r\nNo utilizar en animales con hipersensibilidad a los principios activos.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 20:23:41', '2023-06-23 20:23:41'),
(33, '434522680307', 'OL TRANS FLEX |  10 blíster por 7 comprimidos cada uno.', 'DESCRIPCIÓN\r\nCondroprotector en comprimidos palatables para perros.\r\n\r\nPRESENTACIÓN\r\nDos presentaciones: Envase conteniendo 3 y 10 blisters por 7 comprimidos cada uno.\r\n\r\nACCIÓN\r\nCondroprotector. Nutracéutico. Conserva la salud articular y previene procesos degenerativos osteoarticulares, limita la progresión de la lesión. Reduce el dolor y favorece la biomecánica articular Aumenta la movilidad y mejora la lubricación de la articulación. Recupera la funcionalidad articular y evita la pérdida de masa muscular por desuso MSM: colabora en la síntesis de condroitin sulfato, antiinflamtorio, analgésico, mejora el flujo sanguíneo y reduce el espasmo muscular Glucosamina: forma parta de los glucosaminoglucanos del cartílago.Estimula a los condrocitos en la síntesis de colágeno y proteoglicanos Acido ascórbico: Función antioxidante y estimulante de la síntesis de colágeno Manganeso: participa de la síntesis de condroitin sulfato,de ácido hialurónico y de colágeno.\r\n\r\nINDICACIONES\r\nIndicado en el tratamiento de la osteoartritis crónica, displasia de cadera, espondiloartrosis, reparación de ligamentos y tendones, fracturas Preventivo de procesos osteoarticulares en animales senior. Durante la fase de crecimiento de cachorros, para fortalecer la salud osteoarticular.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nGlucosamina Sulfato 440 mg.\r\nMetilsulfonilmetano (MSM) 400 mg.\r\nÁcido Ascórbico 66 mg.\r\nManganeso Gluconato 10 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros: 1 comprimido palatable cada 20 kg de peso cada 24 horas. No menos de 4-6 semanas. El tratamiento se prolongará dependiendo de la evolución del cuadro clínico y el criterio del médico veterinario actuante.\r\n\r\nCONTRAINDICACIONES\r\nNo presenta contraindicaciones, posee alto margen de seguridad.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.\r\n\r\nRESTRICCIONES DE USO\r\nNo utilizar en animales con hipersensibilidad a los principios activos.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 20:24:33', '2023-06-23 20:24:33');
INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `detalle`, `id_categoria`, `stock`, `id_medida`, `pcosto`, `pventa`, `observacion`, `id_estado`, `created_at`, `updated_at`) VALUES
(34, '810709705518', 'POTEN PET | 3 blísters de 7 comprimidos cada uno', 'DESCRIPCIÓN\r\nBiomodulador revitalizante-energizante en comprimidos palatables.\r\n\r\nPRESENTACIÓN\r\nCaja con 3 blisters de 7 comprimidos cada uno.\r\n\r\nACCIÓN\r\nAntioxidante y regenerador celular. Potenciador metabólico Metionina: Colabora en el metabolismo de grasas a nivel hepático, protección renal ( quelante de metales pesados, interviene en la formación de amoníaco), y de las vías urinarias bajas, colabora en la síntesis de queratina. Vitamina B1: Favorece el apetito, protege el corazón, mejora el funcionamiento del sistema nervioso, colabora en la función digestiva, y en el metabolismo de lipidos, proteínas y ácidos nucleicos. Vitamina B6: Absorción y metabolismo de aminoacidos, colabora en la eritropoyesis y en el metabolismo de los lipidos. Vitamina B12:colabora en la sintesis de ADN , ARN, proteínas, neurotransmisores, globulos rojos. Energiza la fibra muscular y colabora en el mantenimiento de la vaina de mielina de las células nerviosas. Vitamina E: Antioxidante, junto con el selenio protege la membrana celular, colabora con el mantenimiento del sisteminmune Ayuda a combatir los efectos nocivos del stress. Ácido Fólico: colabora en la sintesis de ADN, ARN, proteínas y glóbulos rojos. Evita daños fetales. Gluconato de Zinc: Favorece la queratinización del pelo y la piel, colabora con el mantenimiento de la inmunidad y en la síntesis de proteínas, ácidos nucleicos, vitaminas. Ácido Ascórbico: Antioxidante, combate los efectos del stress. Nicotinamida: Colabora en el mantenimiento de la salud gastrointestinal, dérmica y del sistema nervioso.\r\n\r\nINDICACIONES\r\nRecuperación de animales convalecientes y en estados carenciales. Aumento de la vitalidad en casos de adinamia, anorexia y atrofia muscular.\r\n\r\nFÓRMULA\r\nCada comprimido contiene:\r\nMetionina 200 mg.\r\nÁcido Ascórbico 150 mg.\r\nNicotinamida 50 mg.\r\nGluconato de zinc 30 mg.\r\nVitamina E 25 mg.\r\nVitamina B1 10 mg.\r\nVitamina B6 10 mg.\r\nVitamina B12 6 mg.\r\nÁcido Fólico 0,10 mg.\r\nExcipientes c.s.\r\n\r\nPOSOLOGÍA Y ADMINISTRACIÓN\r\nPerros hasta 10 kg: 1/2 comp. por día.\r\nPerros de 10 a 30 kg: 1 comp. por día.\r\nPerros de más de 30 kg: 2 comp. por día.\r\nGatos: 1/4 a 1/2 comp. por día.En casos de animales resistentes a recibir medicación por vía oral, el comprimido puede molerse hasta obtener un polvo, que puede mezclarse en la comida.\r\n\r\nCONTRAINDICACIONES\r\nNo administrar a animales hipersensibles a alguno de sus componentes.\r\n\r\nEFECTOS COLATERALES\r\nNo presenta.\r\n\r\nRESTRICCIONES DE USO\r\nHipersensibilidad a algunos de los componentes.\r\n\r\nPRECAUCIÓN\r\nNo presenta.', 33, '0.000', 21, '0', '0', NULL, 18, '2023-06-23 20:29:41', '2023-06-23 20:29:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `razonsocial` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_estado` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `razonsocial`, `ruc`, `direccion`, `correo`, `telefono`, `celular`, `observacion`, `id_estado`, `created_at`, `updated_at`) VALUES
(1, 'PEPITO SA editado', '5658544-8', 'peterete', 'pepitos@pepitoer.com', '34333222', '0991000000', 'CHUPETE EDIT', 4, '2023-06-17 01:00:06', '2023-06-17 20:52:29'),
(4, 'CAMI SA', '45854522', 'CLORINDA', 'camilanesa@tutorial.com', NULL, '55554455', 'PRUEBA', 4, '2023-06-17 20:00:53', '2023-06-17 20:55:58'),
(6, 'ÑA EVA', '28097887', 'AUGUSTO ROA BASTO CASI PRESIDENTE HAYES', 'eva.frutos@gmail.com', NULL, '0983379079', 'prueba', 3, '2023-06-17 20:55:29', '2023-06-17 20:55:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `razas`
--

CREATE TABLE `razas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `claseanimal_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `razas`
--

INSERT INTO `razas` (`id`, `nombre`, `claseanimal_id`, `estado_id`, `created_at`, `updated_at`) VALUES
(1, 'Perro esquimal canadiense o Canadian Eskimo Dog', 52, 60, '2023-06-23 20:36:45', '2023-06-23 20:36:45'),
(2, 'Bulldog francés', 52, 60, '2023-06-23 20:36:45', '2023-06-23 20:36:45'),
(3, 'Bulldog continental', 52, 60, '2023-06-23 20:36:45', '2023-06-23 20:36:45'),
(4, 'Alaskan klee kai', 52, 60, '2023-06-23 20:36:45', '2023-06-23 20:36:45'),
(5, 'Perro esquimal americano o American Eskimo', 52, 60, '2023-06-23 20:36:45', '2023-06-23 20:36:45'),
(6, 'Pastor alemán', 52, 60, '2023-06-23 20:36:45', '2023-06-23 20:36:45'),
(7, 'Siamés', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(8, 'Maine coon', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(9, 'Gato persa', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(10, 'Gato kohana', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(11, 'British shorthair - Gato británico de pelo corto', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(12, 'Gato elfo', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(13, 'Gato bambino', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(14, 'Gato bambino', 53, 60, '2023-06-24 23:02:31', '2023-06-24 23:02:31'),
(15, 'Mestizo', 52, 60, '2023-07-06 12:33:34', '2023-07-06 12:33:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Ramón', 'narcisoramon79@gmail.com', NULL, '$2y$10$5Xu6aEpZirC89YXMwjQrFuzwwOpZc9vTil9UzNQf/R0N.BJSMM0Gi', NULL, '2023-06-17 04:17:06', '2023-06-17 04:17:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citas_estado_id_foreign` (`estado_id`),
  ADD KEY `citas_tipo_id_foreign` (`tipo_id`),
  ADD KEY `citas_mascota_id_foreign` (`mascota_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clientes_ruc_unique` (`ruc`),
  ADD KEY `clientes_id_estado_foreign` (`id_estado`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `dominios`
--
ALTER TABLE `dominios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mascotas_sexo_id_foreign` (`sexo_id`),
  ADD KEY `mascotas_raza_id_foreign` (`raza_id`),
  ADD KEY `mascotas_propietario_id_foreign` (`propietario_id`),
  ADD KEY `mascotas_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `opciones`
--
ALTER TABLE `opciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opciones_id_dominio_foreign` (`id_dominio`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `productos_codigo_unique` (`codigo`),
  ADD KEY `productos_id_categoria_foreign` (`id_categoria`),
  ADD KEY `productos_id_medida_foreign` (`id_medida`),
  ADD KEY `productos_id_estado_foreign` (`id_estado`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedores_id_estado_foreign` (`id_estado`);

--
-- Indices de la tabla `razas`
--
ALTER TABLE `razas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `razas_claseanimal_id_foreign` (`claseanimal_id`),
  ADD KEY `razas_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dominios`
--
ALTER TABLE `dominios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `opciones`
--
ALTER TABLE `opciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `razas`
--
ALTER TABLE `razas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `opciones` (`id`),
  ADD CONSTRAINT `citas_mascota_id_foreign` FOREIGN KEY (`mascota_id`) REFERENCES `mascotas` (`id`),
  ADD CONSTRAINT `citas_tipo_id_foreign` FOREIGN KEY (`tipo_id`) REFERENCES `opciones` (`id`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_id_estado_foreign` FOREIGN KEY (`id_estado`) REFERENCES `opciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `mascotas_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `opciones` (`id`),
  ADD CONSTRAINT `mascotas_propietario_id_foreign` FOREIGN KEY (`propietario_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `mascotas_raza_id_foreign` FOREIGN KEY (`raza_id`) REFERENCES `razas` (`id`),
  ADD CONSTRAINT `mascotas_sexo_id_foreign` FOREIGN KEY (`sexo_id`) REFERENCES `opciones` (`id`);

--
-- Filtros para la tabla `opciones`
--
ALTER TABLE `opciones`
  ADD CONSTRAINT `opciones_id_dominio_foreign` FOREIGN KEY (`id_dominio`) REFERENCES `dominios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_id_categoria_foreign` FOREIGN KEY (`id_categoria`) REFERENCES `opciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `productos_id_estado_foreign` FOREIGN KEY (`id_estado`) REFERENCES `opciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `productos_id_medida_foreign` FOREIGN KEY (`id_medida`) REFERENCES `opciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_id_estado_foreign` FOREIGN KEY (`id_estado`) REFERENCES `opciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `razas`
--
ALTER TABLE `razas`
  ADD CONSTRAINT `razas_claseanimal_id_foreign` FOREIGN KEY (`claseanimal_id`) REFERENCES `opciones` (`id`),
  ADD CONSTRAINT `razas_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `opciones` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
