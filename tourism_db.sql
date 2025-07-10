-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2025 at 07:41 PM
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
-- Database: `tourism_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Gee K', 'gee@mail.com', '$2y$10$xpddXaxeVvY1ZFFtvoeYSu.yHL1LBV0qv7/TD57j1CnsUfS34BtjS', 'admin', '2025-07-03 11:23:42', '2025-07-03 11:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_type` enum('flights','hotels','cars','tours') NOT NULL,
  `item_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `travel_class` varchar(50) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `payment_status` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `booking_type`, `item_id`, `booking_date`, `check_in_date`, `check_out_date`, `total_price`, `status`, `start_date`, `end_date`, `quantity`, `travel_class`, `special_requests`, `payment_status`) VALUES
(9, 1, 'hotels', 3, '2025-07-03 05:24:34', '2025-07-10', '2025-07-17', 1400.00, 'cancelled', NULL, NULL, 1, NULL, 'sdfg', 'paid'),
(12, 2, 'tours', 4, '2025-07-03 06:49:26', NULL, NULL, 500.00, 'confirmed', '2025-07-12', '2025-07-26', 1, NULL, 'Best services', ''),
(13, 2, 'flights', 5, '2025-07-03 06:55:45', NULL, NULL, 400.00, 'confirmed', '2025-08-01', NULL, 1, 'economy', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `rental_company` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `available` tinyint(1) DEFAULT 1,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `available_from` date DEFAULT NULL,
  `available_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `model`, `type`, `rental_company`, `location`, `price_per_day`, `available`, `features`, `created_at`, `image_path`, `available_from`, `available_to`) VALUES
(5, 'BMW', 'luxury', 'TripSure', 'Kileleshwa, Nairobi', 200.00, 1, 'Automatic, Bluetooth, Leather Seats, Heated Seats', '2025-07-01 10:38:57', 'assets/images/uploads/generic/generic_6863bac1e19ef.jpg', NULL, NULL),
(6, 'Mercedes', 'luxury', 'TripSure', 'Kileleshwa, Nairobi', 200.00, 1, 'Automatic, Bluetooth, Leather Seats, Heated Seats', '2025-07-01 10:39:52', 'assets/images/uploads/generic/generic_6863baf87a2c7.jpg', NULL, NULL),
(7, 'Range Rover', 'suv', 'TripSure', 'Karen, Nairobi', 300.00, 1, 'Automatic, Bluetooth, Leather Seats, Heated Seats', '2025-07-01 10:41:18', 'assets/images/uploads/generic/generic_6863bb4e7edc0.jpg', NULL, NULL),
(8, 'Rolls Royce', 'luxury', 'TripSure', 'Nairobi, Kenya', 300.00, 1, 'Automatic, Bluetooth, Leather Seats, Heated Seats, Luxury', '2025-07-01 11:32:17', 'assets/images/uploads/generic/generic_6863c7414c943.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `responded` tinyint(1) DEFAULT 0,
  `response` text DEFAULT NULL,
  `response_date` datetime DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_read`, `responded`, `response`, `response_date`, `responded_by`) VALUES
(1, 'Nagawa Yen', 'nagawa@mail.com', 'Test', 'Testing testing', '2025-07-03 10:27:02', 0, 0, NULL, NULL, NULL),
(2, 'Nagawa Yen', 'nagawa@mail.com', 'Test', 'Testing testing', '2025-07-03 10:28:03', 0, 0, NULL, NULL, NULL),
(3, 'Meshack B', 'meshackb@mail.com', 'Test', 'bvnmfghjfghjkl', '2025-07-03 10:29:56', 0, 0, NULL, NULL, NULL),
(4, 'Mindset', 'mindset@mail.com', 'Boxing', 'fdgbjhnmk,lgfbjhn', '2025-07-03 10:32:12', 1, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `airline` varchar(100) NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `arrival_city` varchar(100) NOT NULL,
  `departure_date` datetime NOT NULL,
  `arrival_date` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `business_price` decimal(10,2) NOT NULL,
  `first_class_price` decimal(10,2) NOT NULL,
  `seats_available` int(11) NOT NULL,
  `class` varchar(50) NOT NULL,
  `baggage_allowance` varchar(50) NOT NULL,
  `meal_included` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `airline`, `flight_number`, `departure_city`, `arrival_city`, `departure_date`, `arrival_date`, `price`, `business_price`, `first_class_price`, `seats_available`, `class`, `baggage_allowance`, `meal_included`, `created_at`, `image_path`) VALUES
(3, 'KK Airways', '1002', 'Nairobi', 'Cairo', '2025-07-05 12:00:00', '2025-07-05 16:00:00', 1200.00, 0.00, 0.00, 50, '', '', 1, '2025-07-01 10:05:54', 'assets/images/uploads/flight/flight_6863b302871de.jpg'),
(4, 'Velora Jet', '1003', 'Nairobi', 'Cairo', '2025-07-20 18:30:00', '2025-07-21 00:30:00', 1000.00, 0.00, 0.00, 300, '', '', 1, '2025-07-01 11:37:47', 'assets/images/uploads/flight/flight_6863c88bc1f76.jpg'),
(5, 'Hyperis Air', '1001', 'Nairobi', 'Cape Town', '2025-08-01 14:40:00', '2025-08-01 18:40:00', 400.00, 0.00, 0.00, 24, '', '', 1, '2025-07-01 11:43:30', 'assets/images/uploads/flight/flight_6863c9e2a2b05.jpg'),
(6, 'Orion X', '1002', 'Nairobi', 'Rabat', '2025-08-02 14:45:00', '2025-08-02 19:45:00', 300.00, 0.00, 0.00, 50, '', '', 1, '2025-07-01 11:45:15', 'assets/images/uploads/flight/flight_6863ca4bc6d5a.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `rooms_available` int(11) NOT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `location`, `description`, `price_per_night`, `rooms_available`, `rating`, `amenities`, `created_at`, `image_path`) VALUES
(3, 'Shamix Hotel', 'Uganda', 'The best hotel in all over Uganda.', 200.00, 99, 5.0, 'Free WiFi, Pool, Gym, Restaurant, Spa, Parking', '2025-07-01 06:29:25', 'assets/images/uploads/hotel_68638045da9aa7.79493367.jpg'),
(6, 'The Opulora', 'Kileleshwa, Nairobi', 'The best luxury hotel in all of Africa', 100.00, 96, 5.0, 'Free WiFi, Pool, Gym, Restaurant, Spa, Parking', '2025-07-01 10:49:20', 'assets/images/uploads/generic/generic_6863bd30b0e6b.jpg'),
(7, 'Hotel Lumora', 'Hurringham, Nairobi', 'We are the best.', 100.00, 100, 5.0, 'Free WiFi, Pool, Gym, Restaurant, Spa, Parking', '2025-07-01 10:53:41', 'assets/images/uploads/generic/generic_6863c6bc696a9.jpg'),
(8, 'Sultana Bay Resort', 'Mombasa, Kenya', 'Welcome to the ultimate luxury.', 200.00, 100, 5.0, 'Free WiFi, Pool, Gym, Restaurant, Spa, Parking', '2025-07-01 10:58:13', 'assets/images/uploads/generic/generic_6863bf45d4546.jpg'),
(9, 'Nobliq Suites', 'Kilimani, Nakuru', 'The ultimate best suites in the country', 100.00, 97, 5.0, 'Free WiFi, Pool, Gym, Restaurant, Spa, Parking', '2025-07-01 17:36:03', 'assets/images/uploads/generic/generic_68641c8366119.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `site_pages`
--

CREATE TABLE `site_pages` (
  `id` int(11) NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_pages`
--

INSERT INTO `site_pages` (`id`, `page_name`, `title`, `content`, `last_updated`) VALUES
(1, 'terms', 'Terms and Conditions', '<h2>Terms and Conditions</h2>\r\n    <p>Last updated: January 1, 2023</p>\r\n    \r\n    <h3>1. Introduction</h3>\r\n    <p>Welcome to TravelEase! These terms and conditions outline the rules and regulations for the use of our website and services.</p>\r\n    \r\n    <h3>2. Bookings and Payments</h3>\r\n    <p>All bookings are subject to availability. Prices are subject to change without notice until a booking is confirmed.</p>\r\n    \r\n    <h3>3. Cancellations and Refunds</h3>\r\n    <p>Cancellation policies vary by service provider. Please review the specific cancellation policy for your booking.</p>\r\n    \r\n    <h3>4. Privacy</h3>\r\n    <p>Your privacy is important to us. Please review our <a href=\"privacy.php\">Privacy Policy</a>.</p>\r\n    \r\n    <h3>5. Limitation of Liability</h3>\r\n    <p>TravelEase acts as an intermediary between you and service providers. We are not liable for any damages resulting from services provided by \r\n           third parties.</p>\r\n    \r\n    <h3>6. Changes to Terms</h3>\r\n    <p>We reserve the right to modify these terms at any time. Your continued use of the site constitutes acceptance of the modified terms.</p>', '2025-07-03 11:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_participants` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `title`, `location`, `description`, `duration_days`, `price`, `max_participants`, `start_date`, `end_date`, `created_at`, `image_path`) VALUES
(2, 'Pinnacle Vale', 'Mt. Kenya', 'The best tourist attraction', 7, 300.00, 50, '2025-07-05', '2025-07-12', '2025-07-01 11:13:35', 'assets/images/uploads/tour/tour_6863c2dfc96e6.jpg'),
(3, 'Canopyon Experience', 'Kenya', 'Come enjoy the beautiful nature', 7, 300.00, 30, '2025-07-05', '2025-07-12', '2025-07-01 11:15:06', 'assets/images/uploads/tour/tour_6863c33ac750a.jpg'),
(4, 'Ecosora Wilds ', 'Mombasa, Kenya', 'Welcome to the beautiful oceans', 14, 500.00, 50, '2025-07-12', '2025-07-26', '2025-07-01 11:17:25', 'assets/images/uploads/tour/tour_6863c3c5bccd0.jpg'),
(5, 'Greenova Eco Parks', 'Nyeri, Kenya', 'The beautiful nature', 7, 100.00, 20, '2025-07-19', '2025-07-26', '2025-07-01 11:27:41', 'assets/images/uploads/tour/tour_6863c62d0272c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'Winnie Muga1', 'winnie@mail.com', '$2y$10$dkgKDZ8Jyeyzj0vUcg8kbuuxaa4PvMwb90Kk2opHh6zz2fixfg8B6', '07223344556', '2025-06-30 13:41:07'),
(2, 'Odongo Juma', 'adongo@mail.com', '$2y$10$MO5VerexnRU4X61UUSnh7.MGF8O4ulfOqMGcVYhITEkm0x9HnVP8i', '07223344556', '2025-07-03 06:35:36'),
(3, 'Nymoh Bash', 'nb@mail.com', '$2y$10$LJtOZ8iWaJwKQoBGuCDrauZRmIfKilutxcnbnYyjUY5kXJDya5S22', '07223344556', '2025-07-03 11:00:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_responded_by` (`responded_by`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_pages`
--
ALTER TABLE `site_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_name` (`page_name`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
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
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `site_pages`
--
ALTER TABLE `site_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `fk_responded_by` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
