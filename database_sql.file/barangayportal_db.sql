-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2026 at 11:02 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barangayportal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `barangay_updates`
--

CREATE TABLE `barangay_updates` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_updates`
--

INSERT INTO `barangay_updates` (`id`, `title`, `category`, `event_date`, `description`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'ARAW NANG BARANGAY', '2ND BATCH GYM', '2016-03-26', 'ARAW NANG BARANGAY SA STO.NINO ATBANG VISTAMA', 'uploads/1786767035_newbarangay.png', '2026-08-15 04:10:35', '2026-09-17 12:24:22'),
(4, 'Zumba', '1ST BATCH GYM', '2026-09-16', 'naay zumba sa 1st batch gym.', 'uploads/1789565675_1.jpg', '2026-09-16 13:34:35', '2026-09-17 12:24:22'),
(5, 'DRIVER LICENCE UPDATED', '3RD BATCH GYM', '2026-09-17', 'GOODS KAAYO NI PARA SA MGA WLAAY LICENCYA', 'uploads/1789647868_output.jpg', '2026-09-17 04:24:28', '2026-09-17 04:24:28'),
(6, 'LIBRE TULI LODS', '1ST BATCH GYM', '2026-09-20', 'Frameless cards — dropped the visible border: 1px solid box entirely; cards now float purely on soft shadow with a barely-there inset hairline, so there\'s no hard boxed line on the left/right — just clean floating shape, and more generous inner padding on the content side.\r\nCategory color coding — a small PHP helper (updateCategoryClass) reads each update\'s category text and assigns a theme (green/gold/navy/terracotta/plum) automatically, no DB changes. That color drives the category pill, the dot icon, the hover title color, and a soft glowing border on hover — every card visually differentiates itself.\r\nGiant faint index numbers (01, 02, 03…) in the corner of each card — a magazine/editorial touch that\'s a lot more \"designed\" than a plain list.\r\nBetter animation — fade+rise reveal uses a proper eased curve (cubic-bezier(0.22, 1, 0.36, 1)) instead of linear, cards lift higher on hover with a much softer/wider shadow, image zooms slower and smoother, and the accent border glows gold on hover.\r\nModal is now a real \"ID card\" — full-bleed image fills the entire left half (dark green fallback background + icon if no image), scrollable content panel on the right themed in that update\'s accent color, a divider line, bigger serif title, and the whole box scales+slides in from center. On mobile it becomes a bottom sheet that slides up instead of a tiny centered popup.', 'uploads/1789799961_streetlight.jpg', '2026-09-18 22:39:21', '2026-09-18 22:39:21');

-- --------------------------------------------------------

--
-- Table structure for table `contact_inquiries`
--

CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `image_path` varchar(255) DEFAULT '',
  `resolved_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_inquiries`
--

INSERT INTO `contact_inquiries` (`id`, `name`, `email`, `phone`, `location`, `message`, `created_at`, `updated_at`, `status`, `image_path`, `resolved_image_path`) VALUES
(24, 'Darwin Albarido', 'darwin@gmail.com', '09123456789', 'Purok 1', 'Issue Type: Illegal Parking\nDescription: illegal', '2026-09-18 07:11:35', '2026-09-18 07:12:30', 'Resolved / Accomplished', 'uploads/1789744295_1.jpg', 'uploads/resolved_1789744350_output.jpg'),
(25, 'clarissa', 'cla@gmail.com', '09123456789', 'Purok 8', 'Issue Type: Road Damage / Potholes\nDescription: naay guba ang salog', '2026-09-18 07:18:03', '2026-09-18 07:19:08', 'Resolved / Accomplished', 'uploads/1789744683_pangtesting.jpg', 'uploads/resolved_1789744748_pangayo.jpg'),
(26, 'clarissa', 'cla@gmail.com', '', 'Purok 1', 'Issue Type: Water Supply Issue\nDescription: tubig', '2026-09-18 07:19:47', '2026-09-18 07:20:12', 'Resolved / Accomplished', 'uploads/1789744787_pangtesting.jpg', 'uploads/resolved_1789744812_pangayo.jpg'),
(27, 'Ronith Jade S. Elag', 'jade@gmail.com', '09123456789', 'Purok 5', 'Issue Type: Stray Animals\nDescription: Naay iro', '2026-09-18 07:26:33', '2026-09-18 07:28:54', 'Resolved / Accomplished', 'uploads/1789745193_1000006106.jpg', 'uploads/resolved_1789745265_1000006110.jpg'),
(28, 'chico segovia', 'chico@gmail.com', '09123456789', 'Purok 18', 'Issue Type: Broken Streetlights\nDescription: guba ang suga bruh', '2026-09-18 21:57:28', '2026-09-18 21:58:18', 'Resolved / Accomplished', 'uploads/1789797448_broken-light.jpg', 'uploads/resolved_1789797498_streetlight.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `security_question` varchar(255) NOT NULL,
  `security_answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `created_at`, `updated_at`, `reset_token`, `reset_expires`, `security_question`, `security_answer`) VALUES
(1, 'Ronith Jade S. Elag', 'jade@gmail.com', '09381014329', '$2y$10$CZTlOaRQ3D03olTWCY.bJuWj5CWLFJCa8Ka5BbmsUn18Z9C03jGEm', '2026-08-22 04:18:57', '2026-09-17 11:52:57', '1edcf961d5f109d1d3811c773517873f2c763840465515579d8218c238453a0e', '2026-09-07 13:47:18', '', ''),
(2, 'janlee gualdajara', 'jan@gmail.com', '09123456789', '$2y$10$n528OF0jMRWiY2.m19.nu.cHxrEpzYh2fhW2ogymEXv8PporD4ry.', '2026-08-22 07:06:21', '2026-09-17 11:52:57', NULL, NULL, '', ''),
(3, 'Clarissa Familar', 'clar@gmail.com', '09123456789', '$2y$10$x7BvGeRbNkYweahCKrPndu6iE8XIQvcCMZD41SJVGLUH1ft3rcWSe', '2026-08-22 12:18:33', '2026-09-17 11:52:57', NULL, NULL, '', ''),
(4, 'prince bayona', 'princeashbayona@gmail.com', '09123213132', '$2y$10$HWhNkF0sxGXRaNnr52Fl4.SUQxNx8zMwJt1YttAnPcfGFPqpjEYkW', '2026-08-22 15:12:36', '2026-09-17 11:52:57', NULL, NULL, '', ''),
(5, 'Francis Louie Ceniza Barro', 'louie@gmail.com', '09123456789', '$2y$10$HsCcW4TJLcQaqigKVHFhAOFG5ApV9PXu931lyoor.Z/w4EI9PLdCu', '2026-08-24 05:25:33', '2026-09-17 11:52:57', '34bb04a3755bfefe20465df1cef2fd9502d821ac1e1b5756508236d09b675226', '2026-08-24 07:57:12', '', ''),
(6, 'Darwin Albarido', 'darwin@gmail.com', '09123456789', '$2y$10$zrMaCkcsMLFcpeNazmMMAOWeyu9SDa1YcSXSRFQlbZDG3X79Va1ti', '2026-08-24 05:28:53', '2026-09-17 11:52:57', NULL, NULL, '', ''),
(7, 'Juan Dela Cruz', 'juan@gmail.com', '09296736293', '$2y$10$ynp/wXxdxvfArmUuC20Dh.MkRzHn9i3YnsI5ijVSKhqlbPD23y6Hu', '2026-09-07 11:56:20', '2026-09-17 11:52:57', NULL, NULL, 'What was the name of your first pet?', '$2y$10$abuUpGigM3Q9A4vQJlXhbOkOtnWuf8zz5wX/3I57gHX0AFJBeN.D2'),
(8, 'clarissa', 'cla@gmail.com', '09296736293', '$2y$12$5nMX2fJk369NIXHX/RWwg.dHdllTm4KVjVrJ3VhBDB4J01x7OMejm', '2026-09-17 03:53:06', '2026-09-18 07:14:59', NULL, NULL, 'What is your mother\'s maiden name?', '$2y$12$I9t39ks6PI4q82F4iBAI3e/KKk9hysBhITCeFnhjSKoue3qFe.7CC'),
(9, 'chico segovia', 'chico@gmail.com', '09296736293', '$2y$12$JxMp.zoNV5y4AvAMz/1C3.3WLJE1bbiyNr69oOKn7Ezn/znu3gWF6', '2026-09-18 21:54:54', '2026-09-18 21:54:54', NULL, NULL, 'What was the name of your first pet?', '$2y$12$oq2SDvQgTgNn17EqcK771OtyFsBqPUgXO.vVqdVA..aqaWR8zdlbO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barangay_updates`
--
ALTER TABLE `barangay_updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barangay_updates`
--
ALTER TABLE `barangay_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
