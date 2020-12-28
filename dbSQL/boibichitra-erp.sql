-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 28, 2020 at 04:40 PM
-- Server version: 5.7.24
-- PHP Version: 7.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `boibichitra-erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(4, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(5, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(6, '2016_06_01_000004_create_oauth_clients_table', 1),
(7, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(8, '2019_08_19_000000_create_failed_jobs_table', 1),
(9, '2020_12_23_060623_create_permission_tables', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(5, 'App\\User', 30),
(6, 'App\\User', 31);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('01c8691410349e0bbe6cea6f948666650c0a6e859fa6ad97df862b5092252fd87cf02e7a5313aae9', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 04:49:59', '2020-12-23 04:49:59', '2021-12-23 10:49:59'),
('096dc3fd12b2b99aa99577e8ae9a41b623f50866b30e56af5091fc84b25db97e58a95faf487bdd64', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-27 06:51:17', '2020-12-27 06:51:17', '2021-12-27 06:51:17'),
('0c20539cb8783d043b3ea53ab37976805ec1d752914fb0dcaca7e1c67caf5b601e6de7f3516c2163', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:10:24', '2020-12-24 08:10:24', '2021-12-24 08:10:24'),
('0d056f830004bbccba2887b5790bc9fb7cf3cda93c6576e33f2cac633cfd93fc0218301a4c6f0717', 28, 1, 'BoiBichitra', '[]', 0, '2020-12-28 12:27:41', '2020-12-28 12:27:41', '2021-12-28 12:27:41'),
('15f36868ae02c2989b10561827b927e3ff9e408df81e9846bcab0bd6fc7b783142cc6d143c452a07', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-27 08:44:37', '2020-12-27 08:44:37', '2021-12-27 08:44:37'),
('167fa69910ee9fb7b716fa4fb10e92cc61d91e949757fd34779117477d0ecd3677ec354aa3b55089', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 05:01:00', '2020-12-23 05:01:00', '2021-12-23 11:01:00'),
('173e569287e986d17f491b6bcdffe6b5de7453dd7ff3c281b270d611b400b719a75be0a8222b97fb', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:55:37', '2020-12-24 08:55:37', '2021-12-24 08:55:37'),
('182f90916481d5d5dac77598ddf8c1f48cb84e3e4cd3dd0af08ae5b81f1a43aeb2ebe59fa31fd22c', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-27 09:17:34', '2020-12-27 09:17:34', '2021-12-27 09:17:34'),
('1c8baebfc741d2599a8c3212e62d688d48265977d6cef58d3706aa771ad7400804d575c6f68c8dd8', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:33:59', '2020-12-24 09:33:59', '2021-12-24 09:33:59'),
('1eed09c30f02764c7a26a86db7d2b21a56d99b1b9c4f2ad4331f46ff96002e5c8ae3580a29d9d890', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:49:21', '2020-12-24 09:49:21', '2021-12-24 09:49:21'),
('25af16b6c40d88087dcb3726483beb88b219936999a636c5b49bf6b1d58a7ab1117c2eff6f5a5f5c', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:47:15', '2020-12-24 09:47:15', '2021-12-24 09:47:15'),
('27ed4af8057bd277ac66bad7c95a3c39aabef6026270cb783f1e9a39018cee10b2f4cc14bc9a33a9', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 10:20:30', '2020-12-24 10:20:30', '2021-12-24 10:20:30'),
('2969da4dce75c80bf016994cc56d5c457086f0313bb97a33f0a4e04376d01e1dd467bb199a519d08', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:45:20', '2020-12-24 09:45:20', '2021-12-24 09:45:20'),
('29e62a0bdece9ed5f0e7a862eebf38aec41a9f63b89f74e41889aa83986362f69f600798bbf92ca6', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:11:17', '2020-12-24 09:11:17', '2021-12-24 09:11:17'),
('2bee5844c0318d0c2c2a39fb60bde33eea60875557b40aa935ef85996ef99f973014f4200fff5d99', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 11:52:43', '2020-12-24 11:52:43', '2021-12-24 11:52:43'),
('48eacf0b4f203ff31a1f6350d584f488d1517f83247fbcfe74a44376fc7ae2b1eb09358eeaa06292', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:58:13', '2020-12-24 08:58:13', '2021-12-24 08:58:13'),
('4b52ec926076fda1ca70fb6e4d18d49000f205de8dbc19d34e320eb26561ec8b022f5d15c977c8c1', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 11:26:07', '2020-12-24 11:26:07', '2021-12-24 11:26:07'),
('5ab412a93ff76c010a9058f48c91d9fb934946f3f6f6196255fb856bb37dbca2771c89f2c8106fb5', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:45:57', '2020-12-24 09:45:57', '2021-12-24 09:45:57'),
('6639989080f7411d37d78a3f6254c13b69c4f6e146283b6954e1e125b9348a6dc34b5aecfccc8eee', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 06:27:51', '2020-12-24 06:27:51', '2021-12-24 06:27:51'),
('6b7927f334f91d07160e504f84bb5f2fd10c8319414e596933cc9ccd763842325dd4a25105c01dce', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:48:05', '2020-12-24 09:48:05', '2021-12-24 09:48:05'),
('6fa113a07d708f21de9f178abbbf61256cceb1851e30ae27068fb9a85e9160d9acc5f19f36d3b7d0', 28, 1, 'BoiBichitra', '[]', 0, '2020-12-28 12:32:11', '2020-12-28 12:32:11', '2021-12-28 12:32:11'),
('70b1b079edbb538d6b6f2ce835e82478d670639e25e85ef1ec7b5e00783451c3e28cfb0a56289af0', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 04:52:01', '2020-12-23 04:52:01', '2021-12-23 10:52:01'),
('82faf8cfd724f5a86941c1229d8672ceb3648c304467c9516e3b84949673fba74dd3173b004946f4', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:50:37', '2020-12-24 09:50:37', '2021-12-24 09:50:37'),
('85f8d8362fbbf599936b1ebadc97bd52036f04812b08335b8f85967a5f82214411e278c2c78b7900', 28, 1, 'BoiBichitra', '[]', 0, '2020-12-28 12:32:21', '2020-12-28 12:32:21', '2021-12-28 12:32:21'),
('896f51b1284461a31b71d3ca76adceb28815ee085c2cc546efb46af77d67bd1585fee89e7f897259', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-27 11:52:27', '2020-12-27 11:52:27', '2021-12-27 11:52:27'),
('8f7fda711604c13edaae0750ea8d32d7b64c3949f76f3a280be3620dac7de07c9d775e44d84fa717', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:22:10', '2020-12-24 09:22:10', '2021-12-24 09:22:10'),
('91db2eebe2ec52967a2caa5f1c3fe8e894fff3d92125bd3bde8628d19d4fab8f5090de4e78d74551', 25, 1, NULL, '[]', 0, '2020-12-23 04:39:16', '2020-12-23 04:39:16', '2021-12-23 10:39:16'),
('93aa344639d6f71aee2894876def66e4d43a4e2e84f71b47a2ccc674e52cd66ecb1efc1381daed91', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:46:41', '2020-12-24 08:46:41', '2021-12-24 08:46:41'),
('a2d111a2d430c78b3bd1a7885cf30c62c1fefc14bdcc439b7e1312f300be65a4c97458fbe262f705', 23, 1, NULL, '[]', 0, '2020-12-23 04:23:30', '2020-12-23 04:23:30', '2021-12-23 10:23:30'),
('a2dd3a6124a941f13a243feb3c5f89a1f0574463023b38f828a41862bceeae4f82e8d4be9daedb35', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 06:14:21', '2020-12-24 06:14:21', '2021-12-24 06:14:21'),
('a7161f6626cd50abf8403f86cb00ecf36342a5878868934b6936d76a63979e9f7b4c0108fd558712', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-27 08:46:37', '2020-12-27 08:46:37', '2021-12-27 08:46:37'),
('a920e3ca541d68f9f1d33853d8dd01be32ce3ccb109330190a21a7058453a225fb940ebed5252d4c', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:46:41', '2020-12-24 09:46:41', '2021-12-24 09:46:41'),
('a9e575098babdcc1f3a04f17fbdb98bdb10638e153d1bd9b5199da866cb11fa36ce96baa8bf8bdf7', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:44:38', '2020-12-24 09:44:38', '2021-12-24 09:44:38'),
('ae4c2efce330d8c850983209644c7a7fe4acfd9f7a32738c8652d80f1fe390131b185f1cf3cafa28', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:09:18', '2020-12-24 09:09:18', '2021-12-24 09:09:18'),
('b9a090b226cf76a3cda3badb2f3b39d071115d2eec46de0ae01b4034411710de544820e51cb8b3cf', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:46:04', '2020-12-24 08:46:04', '2021-12-24 08:46:04'),
('ba34fe5251682fcc10164f5a05e133f4aeaa2a6dff69e5e130d8bc7d1fa6ec16b42881a44c0e14c1', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:16:49', '2020-12-24 08:16:49', '2021-12-24 08:16:49'),
('bed2e61fe6e8ea1121e3a450a7b6727b40691634b3cee88dd2cc4f568a5d72548a51476e8200815a', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-27 08:34:42', '2020-12-27 08:34:42', '2021-12-27 08:34:42'),
('c59d01cfcd8c321b92c462d9d87159293f61fd7626a63975b6f9ad0c36e7f6a4ef1ab94f7c9d1c71', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:02:12', '2020-12-24 09:02:12', '2021-12-24 09:02:12'),
('c9c55727fd8699ef40805dd94b56bdd40fa8ae48966a4597189705a6b8fe294c8bf736016e54532b', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:59:08', '2020-12-24 08:59:08', '2021-12-24 08:59:08'),
('cf4ee89113b53dbd13a8ff47963f27815e1dda8de10d7c10fc9ecda36a0237438d8edc42037d35d1', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 06:02:56', '2020-12-23 06:02:56', '2021-12-23 12:02:56'),
('d11f5cdd50ed1226d851708cb015abc884c32bffcd115eaecb2bbfdfa22e8f99eaffe9d26021e437', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 05:18:44', '2020-12-23 05:18:44', '2021-12-23 11:18:44'),
('d5abaf0edaedcb874311d4109a981beb303cc32754cee666ae392cfda749e08287f24fb8bdd5d569', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-27 08:37:33', '2020-12-27 08:37:33', '2021-12-27 08:37:33'),
('d8cbde83e8072665bb811d826ddf0fef21b114b06d5b528ab1acaf006b8012869a012d817000a3c0', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 09:32:57', '2020-12-24 09:32:57', '2021-12-24 09:32:57'),
('df5ffc82a19dedbd982c60ff9e9058987d81ab031be2a6dba095a4c040ed842e422f7f0ff006d5c2', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 11:56:26', '2020-12-24 11:56:26', '2021-12-24 11:56:26'),
('e48a36af2b2a666aa89ae1b554b510fa61cc0d8d346364962bd2e3bc1b25926963780d10f9f50c30', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:55:23', '2020-12-24 08:55:23', '2021-12-24 08:55:23'),
('e5305ffd05cf122cb410f4c0fc02e011117b5a643ce66659fde3b8b4d5fab995cd14db6089f3f0ea', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-28 10:20:49', '2020-12-28 10:20:49', '2021-12-28 10:20:49'),
('e59c9e1bf7b5607d37193d1c3a9a1c4df88108a9e27baf31ed07e2332a0afbeabf44fd1561dbe2fa', 22, 1, NULL, '[]', 0, '2020-12-23 04:16:18', '2020-12-23 04:16:18', '2021-12-23 10:16:18'),
('e983ecf83c8ef966bb096095c0e71b5eb7aa59837356dcd168eed06e0858e7844c8d085ff29714a8', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 08:57:06', '2020-12-24 08:57:06', '2021-12-24 08:57:06'),
('ec58461f05c327f2ddcf399e5af21ffb05f857fbd71d26d60192235c26a524177d08ba3caa102ac9', 28, 1, 'BoiBichitra', '[]', 0, '2020-12-28 12:31:51', '2020-12-28 12:31:51', '2021-12-28 12:31:51'),
('efe28beebea5263d5f8890ec82b42f1bdda4e435cf8e19018008763d6c7c624db85dba51d32e9034', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 06:03:12', '2020-12-23 06:03:12', '2021-12-23 12:03:12'),
('f0620d5097469ade555d80d9acf6019842cbe3538e1d4a4dfb88f7c5dd9aaba6aaaf257d5e0a2c3e', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-27 08:36:36', '2020-12-27 08:36:36', '2021-12-27 08:36:36'),
('f12d516fe6a54cb667d875e3e3b5e9a2172a5b9eabefef9665ea7cf78200a89964eccad0617a3c3e', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-26 04:36:14', '2020-12-26 04:36:14', '2021-12-26 04:36:14'),
('f2cfa0572ee3aa83768c049a57afc7dd653e0190601f6a0852f23a38882c1c8e6d8a9df8b3f65501', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-23 05:16:16', '2020-12-23 05:16:16', '2021-12-23 11:16:16'),
('f3c9e1f51353e50de0f3cda97e50d7ac1cf7fd82db1d49e229a23ed1e6cdc0ad5503e3ac92ef2634', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 11:21:11', '2020-12-24 11:21:11', '2021-12-24 11:21:11'),
('f5185385f7307c700d5e8c6e05058a8e73b167daf82681610e2d875db5d04ce9e623a17a14a0c7ac', 27, 1, 'BoiBichitra', '[]', 0, '2020-12-24 06:26:18', '2020-12-24 06:26:18', '2021-12-24 06:26:18'),
('f6665d4d2020266726d6a3a1eacbc6ea8f1d41cd4f108979545e7db03fd5d4d7e4a62b4b84b9705d', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-28 10:17:41', '2020-12-28 10:17:41', '2021-12-28 10:17:41'),
('f8a0329ae666766c8d086e551bbc09ea68002324845e0daa20af51bedc827451f77a217dfdfd05eb', 24, 1, NULL, '[]', 0, '2020-12-23 04:34:45', '2020-12-23 04:34:45', '2021-12-23 10:34:45'),
('f8d0cb59f3d84fe98d486eb9d1f81e242d884ca0a47486b44b00350a6a9230a22332ed46fd20204e', 9, 1, 'BoiBichitra', '[]', 0, '2020-12-28 10:13:04', '2020-12-28 10:13:04', '2021-12-28 10:13:04');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Laravel Personal Access Client', 'nrK0Ci9cydGztP7LH2bFNLCxZaitMpcU2gOt28n3', NULL, 'http://localhost', 1, 0, 0, '2020-12-23 04:15:43', '2020-12-23 04:15:43'),
(2, NULL, 'Laravel Password Grant Client', '4mfHZiuIdT7ItNNKKViC2ubmX2nyQGfp6hZOLxTj', 'users', 'http://localhost', 0, 1, 0, '2020-12-23 04:15:43', '2020-12-23 04:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2020-12-23 04:15:43', '2020-12-23 04:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

CREATE TABLE `parties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('supplier','customer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci,
  `status` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parties`
--

INSERT INTO `parties` (`id`, `type`, `name`, `slug`, `phone`, `email`, `address`, `status`, `created_at`, `updated_at`) VALUES
(6, 'customer', 'bhuban', 'bhuban', '01771502073', 'admin@starit.com', 'as', 0, '2020-12-28 10:42:18', '2020-12-28 10:42:18'),
(9, 'supplier', 'bhuabn', 'bhuabn', '01771502073', 'admin@starit.com', 'asdazsda', 1, '2020-12-28 10:51:15', '2020-12-28 10:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'role-list', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(2, 'role-create', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(3, 'role-edit', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(4, 'role-delete', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(5, 'user-list', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(6, 'user-create', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(7, 'user-edit', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24'),
(8, 'user-delete', 'web', '2020-12-28 09:55:24', '2020-12-28 09:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(5, 'Super Admin', 'web', '2020-12-28 10:02:17', '2020-12-28 10:02:17'),
(6, 'Admin', 'web', '2020-12-28 10:11:14', '2020-12-28 10:11:14');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 5),
(2, 5),
(3, 5),
(4, 5),
(5, 5),
(6, 5),
(7, 5),
(8, 5),
(1, 6),
(2, 6),
(3, 6),
(4, 6),
(5, 6),
(6, 6),
(7, 6),
(8, 6);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_type` varchar(191) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'super_admin',
  `name` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `name`, `slug`, `phone`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(30, 'super_admin', 'Super Admin', 'super-admin', '01723144515', 'superadmin@gmail.com', '$2y$10$aWQWAw36yTmK4toQvkJRVeuVAYnlB4b9bw1YTvpLXGKmKXPd8WVEi', 1, '2020-12-28 10:02:17', '2020-12-28 10:02:17'),
(31, 'super_admin', 'Admin', 'admin', '01725930131', 'admin@gmail.com', '$2y$10$oZbdFDxEn6Pe.GAsIcr6gej6ao.ujrglAKMeUPV3ef2l5jR./mTPK', 1, '2020-12-28 10:14:25', '2020-12-28 10:14:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parties`
--
ALTER TABLE `parties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
