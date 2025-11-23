-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 08:01 AM
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
-- Database: `furniture_orders`
--
CREATE DATABASE IF NOT EXISTS `furniture_orders` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `furniture_orders`;

-- --------------------------------------------------------

--
-- Table structure for table `furniture`
--

CREATE TABLE `furniture` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `default_measurements` text DEFAULT NULL,
  `price_range` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `furniture`
--

INSERT INTO `furniture` (`id`, `name`, `category`, `description`, `default_measurements`, `price_range`, `image_url`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Modern Sofa Set', 'Living Room', 'Comfortable 3-seater sofa with matching armchairs. Available in various fabrics and colors.', 'Sofa: 220cm x 95cm x 85cm, Armchair: 90cm x 95cm x 85cm', '$800 - $1,500', 'images/furniture/modern_sofa_set.jpg', 'Removable covers, High-density foam, Wooden legs, Multiple color options', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:20'),
(2, 'Dining Table Set', 'Dining Room', 'Elegant dining table with 6 matching chairs. Extendable design for larger gatherings.', 'Table: 180cm x 90cm x 75cm (extends to 240cm), Chairs: 45cm x 45cm x 95cm', '$600 - $1,200', 'images/furniture/dining_table_set.jpg', 'Extendable leaves, Padded seats, Durable hardwood, Easy to clean', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:22'),
(3, 'Bookshelf Unit', 'Storage', '5-shelf bookshelf with adjustable shelves. Perfect for home office or living room.', 'Width: 120cm, Height: 200cm, Depth: 35cm', '$300 - $600', 'images/furniture/bookshelf_unit.jpg', 'Adjustable shelves, Cable management, Multiple finishes, Wall-mountable', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:25'),
(5, 'Coffee Table', 'Living Room', 'Modern coffee table with storage compartment. Glass top with wooden base.', 'Length: 120cm, Width: 60cm, Height: 40cm', '$200 - $500', 'images/furniture/coffee_table.jpg', 'Tempered glass top, Storage compartment, Soft-close mechanism, Multiple finishes', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:29'),
(6, 'Wardrobe System', 'Bedroom', '3-door wardrobe with hanging space, shelves, and drawers. Full-length mirror included.', 'Width: 180cm, Height: 220cm, Depth: 60cm', '$1,000 - $2,000', 'https://images.pexels.com/photos/1648774/pexels-photo-1648774.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop', 'LED lighting, Full-length mirror, Adjustable shelves, Soft-close doors', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:32'),
(7, 'Office Desk', 'Office', 'L-shaped office desk with cable management and filing cabinet. Ergonomic design.', 'Main: 150cm x 75cm, Side: 120cm x 60cm, Height: 75cm', '$500 - $1,000', 'images/furniture/office_desk.jpg', 'Cable management, Filing cabinet, Ergonomic height, Multiple finishes', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:36'),
(8, 'TV Stand', 'Living Room', 'Floating TV stand with media storage. Accommodates up to 65\" TV.', 'Length: 200cm, Height: 50cm, Depth: 40cm', '$300 - $700', 'images/furniture/tv_stand.jpg', 'Cable management, Ventilation, Media storage, Wall-mountable', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:38'),
(9, 'Kitchen Cabinet Set', 'Kitchen', 'Custom kitchen cabinets with soft-close hinges. Available in various styles.', 'Custom sizes available', '$2,000 - $5,000', 'images/furniture/kitchen_cabinet_set.jpg', 'Soft-close hinges, Adjustable shelves, Multiple finishes, Custom sizing', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:42'),
(10, 'Dresser with Mirror', 'Bedroom', '6-drawer dresser with attached mirror. Ample storage space.', 'Width: 120cm, Height: 150cm, Depth: 50cm', '$400 - $800', 'images/furniture/dresser_with_mirror.jpg', '6 drawers, Attached mirror, Soft-close drawers, Multiple finishes', 1, '2025-11-22 17:56:54', '2025-11-23 06:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `furniture_id` int(11) DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `design` text NOT NULL,
  `measurements` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `status` enum('pending','in-progress','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `furniture_id`, `client_name`, `client_email`, `client_phone`, `design`, `measurements`, `instructions`, `status`, `payment_status`, `amount`, `payment_date`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 4, 8, 'EUNICE MURIITHI', 'eunice@gmail.com', '', 'Floating TV stand with media storage. Accommodates up to 65\" TV.', 'Length: 200cm, Height: 50cm, Depth: 40cm', '', 'completed', 'pending', NULL, NULL, NULL, '2025-11-22 19:05:52', '2025-11-23 06:14:56'),
(2, 5, 10, 'john', 'jon2@furniture.com', '', '6-drawer dresser with attached mirror. Ample storage space.', 'Width: 120cm, Height: 150cm, Depth: 50cm', '', 'pending', 'pending', 626.00, NULL, NULL, '2025-11-23 06:49:29', '2025-11-23 06:51:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@furniture.com', '1234567890', '$2y$10$A9/WxIZIYbOGV6qBF7N5DuUGMsAVMRHVchGh.z6K6wqmC1OTkak0i', 'admin', '2025-11-22 17:13:07', '2025-11-22 17:56:51'),
(2, 'john muriithi', 'jon@furniture.com', '0707155748', '$2y$10$aVXfM3sYmWyBKMFt54Cj..2ifrLJiMv3iD/SW7QNwm4WsGj3meOwe', 'client', '2025-11-22 17:38:31', '2025-11-22 17:38:31'),
(3, 'JOHN MURIITHI GATHIGIA', 'johngathigia@gmail.com', '', '$2y$10$GEvc/90YmrB43XQ8ulyrFeczxbRuQKnrnuQGN.Bq7SUiirUIvZ6wG', 'client', '2025-11-22 18:49:04', '2025-11-22 18:49:04'),
(4, 'EUNICE MURIITHI', 'eunice@gmail.com', '', '$2y$10$MDGecDSySdNdLGmuc016vu7SabxdaIDFm82m0TFKRPmZfr1.RqMki', 'client', '2025-11-22 19:03:23', '2025-11-22 19:03:23'),
(5, 'john', 'jon2@furniture.com', '', '$2y$10$QZlpTFWW6LokjbeAqZ5RkuR.cbILzp3BjUb2WIpsWiKKDTgR1WQSi', 'client', '2025-11-23 06:44:50', '2025-11-23 06:44:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `furniture`
--
ALTER TABLE `furniture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_furniture_id` (`furniture_id`);

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
-- AUTO_INCREMENT for table `furniture`
--
ALTER TABLE `furniture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`furniture_id`) REFERENCES `furniture` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
