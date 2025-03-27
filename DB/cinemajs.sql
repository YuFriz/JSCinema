-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 12:57 PM
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
-- Database: `cinemajs`
--

-- --------------------------------------------------------

--
-- Table structure for table `auditoriums`
--

CREATE TABLE `auditoriums` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditoriums`
--

INSERT INTO `auditoriums` (`id`, `name`, `total_seats`, `created_at`) VALUES
(1, 'Auditorium 1', 100, '2025-02-28 16:47:33'),
(2, 'Auditorium 2', 100, '2025-02-28 16:47:33'),
(3, 'Auditorium 3', 100, '2025-02-28 16:47:33'),
(4, 'Auditorium 4', 100, '2025-02-28 16:47:33'),
(5, 'Auditorium 5', 100, '2025-02-28 16:47:33');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `image_path`, `created_at`, `is_active`) VALUES
(1, 'banners/banner1.jpg', '2025-03-27 10:31:35', 1),
(2, 'banners/banner2.jpg', '2025-03-27 10:31:35', 1),
(3, 'banners/banner3.jpg', '2025-03-27 10:31:35', 1),
(4, 'banners/banner4.jpg', '2025-03-27 10:31:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(2, 'Action'),
(3, 'Adventure'),
(4, 'Animation'),
(10, 'Biography'),
(5, 'Comedy'),
(6, 'Fantasy'),
(11, 'History'),
(7, 'Mystery'),
(1, 'Sci-Fi'),
(9, 'Sport'),
(8, 'Thriller');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `stars` decimal(3,1) DEFAULT NULL CHECK (`stars` between 0 and 10),
  `author` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `movie_duration` int(11) DEFAULT NULL CHECK (`movie_duration` > 0),
  `plays` text DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `coming_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `name`, `description`, `stars`, `author`, `video`, `movie_duration`, `plays`, `status`, `coming_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'Inception', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O., but his tragic past may doom the project and his team to disaster.', 5.0, 'Christopher Nolan', 'Movies/1/vid.mp4', 148, 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page', 'already showing', '2025-03-23', '2025-04-13', '2025-01-16 15:45:50', '2025-03-24 10:52:02'),
(2, 'Matrix', 'When a beautiful stranger leads computer hacker Neo to a forbidding underworld, he discovers the shocking truth--the life he knows is the elaborate deception of an evil cyber-intelligence.', 0.0, 'Lana Wachowski, Lilly Wachowski', 'Movies/2/vid.mp4', 136, 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss', 'already showing', '2025-03-09', '2025-04-06', '2025-01-16 16:29:16', '2025-03-24 11:18:39'),
(3, 'The Pursuit of Happyness', 'Based on a true story about a man named Christopher Gardner. Gardner has invested heavily in a device known as a \"bone density scanner\". He feels like he has it made selling these devices. However, they do not sell well as they are marginally better than x-ray at a much higher price. As Gardner works to make ends meet, his wife leaves him and he loses his apartment. Forced to live out in the streets with his son, Gardner continues to sell bone density scanners while concurrently taking on an unpaid internship as a stockbroker, with slim chances for advancement to a paid position. Before he can receive pay, he needs to outshine the competition through 6 months of training, and to sell his devices to stay afloat.', 0.0, 'Gabriele Muccino', 'Movies/3/vid.mp4', 117, 'Will Smith, Jaden Smith, Thandie Newton', 'soon in cinema', '2025-03-09', '2025-03-23', '2025-01-19 12:43:15', '2025-03-24 11:33:26'),
(11, 'The Northman', 'The Viking Age. With a mind aflame with hate and revenge, Prince Amleth, the wronged son of King Aurvandill War-Raven, heads to cold, windswept Iceland to retrieve what was stolen from him: a father, a mother, and a kingdom. And like a war dog picking up the enemy\'s scent, brutal Amleth embarks on a murderous quest to find the hateful adversary, whose life is forever woven together with his by the threads of fate. Now, in the name of Valhalla, no one can stop the Northman, not even God.—Nick Riganas', 2.0, ' Robert Eggers', 'Movies/11/11_video.mp4', 137, 'Alexander Skarsgård, Nicole Kidman, Claes Bang, Ethan Hawke, Anya Taylor-Joy, Gustav Lindh, Willem Dafoe, Björk, Ralph Ineson, Hafþór Júlíus Björnsson', 'already showing', '2025-03-23', '2025-04-13', '2025-02-28 18:38:23', '2025-03-24 10:52:02'),
(12, 'Spider-Man', 'Based on Marvel Comics\' superhero character, this is a story of Peter Parker who is a nerdy high-schooler. He was orphaned as a child, bullied by jocks, and can\'t confess his crush for his stunning neighborhood girl Mary Jane Watson. To say his life is \"miserable\" is an understatement. But one day while on an excursion to a laboratory a runaway radioactive spider bites him... and his life changes in a way no one could have imagined. Peter acquires a muscle-bound physique, clear vision, ability to cling to surfaces and crawl over walls, shooting webs from his wrist ... but the fun isn\'t going to last. An eccentric millionaire Norman Osborn administers a performance enhancing drug on himself and his maniacal alter ego Green Goblin emerges. Now Peter Parker has to become Spider-Man and take Green Goblin to the task... or else Goblin will kill him. They come face to face and the war begins in which only one of them will survive at the end.', 0.0, 'Sam Raimi', 'Movies/12/12_video.mp4', 121, 'Tobey Maguire, Willem Dafoe, Kirsten Dunst, James Franco, Cliff Robertson, Rosemary Harris, J.K. Simmons, Joe Manganiello, Gerry Becker, Bill Nunn', 'already showing', '2025-03-23', '2025-04-13', '2025-02-28 18:43:02', '2025-03-24 10:52:02'),
(16, 'Brave', 'Set in Scotland in a rugged and mythical time, this movie features Princess Merida (Kelly Macdonald), an aspiring archer and impetuous daughter of Queen Elinor (Dame Emma Thompson). Merida makes a reckless choice that unleashes unintended peril and forces her to spring into action to set things right.', 0.0, 'Mark Andrews, Brenda Chapman, Steve Purcell', 'Movies/16/16_video.mp4', 93, 'Kelly Macdonald, Billy Connolly, Emma Thompson', 'already showing', '2025-03-23', '2025-04-13', '2025-03-10 09:56:33', '2025-03-24 11:19:44'),
(18, 'Wreck-It Ralph', 'Ralph is a video game villain who longs to be a hero. He embarks on a journey across the arcade world to prove himself, encountering new friends and enemies along the way.', 0.0, 'Rich Moore', 'Movies/18/18_video.mp4', 101, 'John C. Reilly, Sarah Silverman, Jack McBrayer', 'soon in cinema', '2025-03-09', '2025-03-23', '0000-00-00 00:00:00', '2025-03-24 10:52:02'),
(19, 'Venom', 'Journalist Eddie Brock gains superpowers after bonding with an alien symbiote. He struggles to control his newfound powers while facing dark forces that seek to exploit them.', 0.0, 'Ruben Fleischer', 'Movies/19/19_video.mp4', 112, 'Tom Hardy, Michelle Williams, Riz Ahmed', 'soon in cinema', '2025-03-09', '2025-03-23', '0000-00-00 00:00:00', '2025-03-24 10:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `movie_genres`
--

CREATE TABLE `movie_genres` (
  `movie_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_genres`
--

INSERT INTO `movie_genres` (`movie_id`, `genre_id`) VALUES
(1, 1),
(1, 7),
(1, 8),
(2, 1),
(2, 2),
(3, 5),
(3, 10),
(11, 2),
(11, 6),
(11, 11),
(12, 2),
(12, 3),
(12, 6),
(16, 3),
(16, 4),
(16, 6),
(18, 3),
(18, 4),
(18, 5),
(19, 1),
(19, 2),
(19, 8);

-- --------------------------------------------------------

--
-- Table structure for table `movie_images`
--

CREATE TABLE `movie_images` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movie_images`
--

INSERT INTO `movie_images` (`id`, `movie_id`, `image_path`) VALUES
(1, 1, 'Movies/1/img1.jpg'),
(2, 2, 'Movies/2/img1.jpg'),
(3, 3, 'Movies/3/img1.jpg'),
(4, 11, 'Movies/11/11_img1.jpg'),
(5, 12, 'Movies/12/12_img1.jpg'),
(6, 16, 'Movies/16/16_img1.jpg'),
(7, 18, 'Movies/18/18_img1.jpg'),
(8, 19, 'Movies/19/19_img1.jpg'),
(16, 1, 'Movies/1/img2.jpg'),
(17, 2, 'Movies/2/img2.jpg'),
(18, 3, 'Movies/3/img2.jpg'),
(19, 11, 'Movies/11/11_img2.jpg'),
(20, 12, 'Movies/12/12_img2.jpg'),
(21, 16, 'Movies/16/16_img2.jpg'),
(22, 18, 'Movies/18/18_img2.jpg'),
(23, 19, 'Movies/19/19_img2.jpg'),
(31, 1, 'Movies/1/img3.jpg'),
(32, 2, 'Movies/2/img3.jpg'),
(33, 3, 'Movies/3/img3.jpg'),
(34, 11, 'Movies/11/11_img3.jpg'),
(35, 12, 'Movies/12/12_img3.jpg'),
(36, 16, 'Movies/16/16_img3.jpg'),
(37, 18, 'Movies/18/18_img3.jpg'),
(38, 19, 'Movies/19/19_img3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `purchased_tickets`
--

CREATE TABLE `purchased_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `movie_id` int(11) NOT NULL,
  `screening_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `ticket_type` enum('regular','children','club','youth','senior') NOT NULL,
  `price` decimal(5,2) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchased_tickets`
--

INSERT INTO `purchased_tickets` (`id`, `user_id`, `movie_id`, `screening_id`, `seat_id`, `ticket_type`, `price`, `purchase_date`) VALUES
(1, NULL, 1, 1, 243, 'regular', 7.99, '2025-03-10 10:13:17'),
(2, 6, 19, 6, 303, 'regular', 7.99, '2025-03-10 16:08:26'),
(4, 6, 1, 1, 245, 'club', 5.00, '2025-03-10 16:12:45'),
(5, 6, 1, 1, 303, 'club', 5.00, '2025-03-10 16:12:45'),
(6, 6, 1, 5, 244, 'children', 4.50, '2025-03-10 16:22:56'),
(7, 6, 1, 5, 245, 'children', 4.50, '2025-03-10 16:22:56'),
(8, 6, 1, 1, 189, 'regular', 7.99, '2025-03-10 16:25:40'),
(9, 6, 3, 7, 250, 'club', 5.00, '2025-03-11 08:25:50'),
(10, 6, 3, 7, 245, 'club', 5.00, '2025-03-11 08:29:52'),
(11, 6, 3, 7, 64, 'children', 4.50, '2025-03-11 09:14:29'),
(12, 6, 3, 7, 366, 'youth', 5.50, '2025-03-11 09:27:55'),
(13, 6, 3, 7, 189, 'regular', 7.99, '2025-03-11 09:30:03'),
(14, 6, 3, 7, 247, 'regular', 7.99, '2025-03-11 09:30:03'),
(15, 6, 11, 4, 304, 'children', 4.50, '2025-03-11 09:33:56'),
(16, 6, 19, 6, 362, 'youth', 5.50, '2025-03-11 09:34:13'),
(17, 6, 19, 6, 371, 'youth', 5.50, '2025-03-11 09:34:13'),
(18, 6, 19, 6, 372, 'youth', 5.50, '2025-03-11 09:34:13'),
(19, 6, 1, 5, 312, 'senior', 4.00, '2025-03-11 09:51:55'),
(20, 6, 1, 5, 361, 'senior', 4.00, '2025-03-11 09:51:55'),
(21, 6, 1, 5, 363, 'children', 4.50, '2025-03-11 09:56:52'),
(22, 6, 1, 5, 364, 'children', 4.50, '2025-03-11 09:56:52'),
(23, 6, 1, 5, 429, 'children', 4.50, '2025-03-11 09:58:01'),
(24, 6, 1, 5, 430, 'children', 4.50, '2025-03-11 09:58:01'),
(25, 6, 1, 5, 431, 'children', 4.50, '2025-03-11 09:58:01'),
(26, 6, 19, 6, 422, 'children', 4.50, '2025-03-12 07:32:44'),
(27, 6, 19, 6, 423, 'children', 4.50, '2025-03-12 07:32:44'),
(28, NULL, 19, 14, 259, 'children', 4.50, '2025-03-20 11:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `reviews_ratings`
--

CREATE TABLE `reviews_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `review` text DEFAULT NULL,
  `star` tinyint(4) DEFAULT NULL CHECK (`star` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews_ratings`
--

INSERT INTO `reviews_ratings` (`id`, `user_id`, `movie_id`, `review`, `star`, `created_at`) VALUES
(3, 6, 1, 'awesome!', 5, '2025-03-14 08:36:52');

-- --------------------------------------------------------

--
-- Table structure for table `screenings`
--

CREATE TABLE `screenings` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `screening_date` date NOT NULL,
  `start_time` time NOT NULL,
  `auditorium_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `screenings`
--

INSERT INTO `screenings` (`id`, `movie_id`, `screening_date`, `start_time`, `auditorium_id`) VALUES
(1, 1, '2025-03-11', '18:00:00', 1),
(2, 2, '2025-02-17', '18:30:00', 1),
(3, 3, '2025-03-06', '14:15:00', 1),
(4, 11, '2025-03-11', '20:30:00', 1),
(5, 1, '2025-03-12', '12:00:00', 1),
(6, 19, '2025-03-12', '14:30:00', 1),
(7, 3, '2025-03-11', '10:30:00', 1),
(8, 11, '2025-03-16', '16:00:00', 3),
(9, 1, '2025-03-17', '09:00:00', 1),
(10, 11, '2025-03-15', '10:30:00', 1),
(11, 3, '2025-03-17', '11:00:00', 1),
(12, 3, '2025-03-19', '10:30:00', 1),
(13, 3, '2025-03-18', '10:30:00', 1),
(14, 19, '2025-03-22', '18:00:00', 2),
(15, 19, '2025-03-24', '17:30:00', 2),
(16, 3, '2025-03-22', '19:00:00', 1),
(17, 12, '2025-03-25', '09:00:00', 1),
(18, 1, '2025-03-25', '11:30:00', 1),
(19, 1, '2025-03-26', '09:30:00', 1),
(20, 11, '2025-03-26', '12:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `id` int(11) NOT NULL,
  `auditorium_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `row_number` int(11) NOT NULL,
  `is_taken` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`id`, `auditorium_id`, `seat_number`, `row_number`, `is_taken`) VALUES
(1, 1, '1', 1, 0),
(2, 1, '2', 1, 0),
(3, 1, '3', 1, 0),
(4, 1, '4', 1, 0),
(5, 1, '5', 1, 0),
(6, 1, '6', 1, 0),
(7, 1, '7', 1, 0),
(8, 1, '8', 1, 0),
(9, 1, '9', 1, 0),
(10, 1, '10', 1, 0),
(11, 1, '11', 2, 0),
(12, 1, '12', 2, 0),
(13, 2, '1', 1, 0),
(14, 2, '2', 1, 0),
(15, 2, '3', 1, 0),
(16, 2, '4', 1, 0),
(17, 2, '5', 1, 0),
(18, 2, '6', 1, 0),
(19, 2, '7', 1, 0),
(20, 2, '8', 1, 0),
(21, 2, '9', 1, 0),
(22, 2, '10', 1, 0),
(23, 2, '11', 2, 0),
(24, 2, '12', 2, 0),
(25, 3, '1', 1, 0),
(26, 3, '2', 1, 0),
(27, 3, '3', 1, 0),
(28, 3, '4', 1, 0),
(29, 3, '5', 1, 0),
(30, 3, '6', 1, 0),
(31, 3, '7', 1, 0),
(32, 3, '8', 1, 0),
(33, 3, '9', 1, 0),
(34, 3, '10', 1, 0),
(35, 3, '11', 2, 0),
(36, 3, '12', 2, 0),
(37, 4, '1', 1, 0),
(38, 4, '2', 1, 0),
(39, 4, '3', 1, 0),
(40, 4, '4', 1, 0),
(41, 4, '5', 1, 0),
(42, 4, '6', 1, 0),
(43, 4, '7', 1, 0),
(44, 4, '8', 1, 0),
(45, 4, '9', 1, 0),
(46, 4, '10', 1, 0),
(47, 4, '11', 2, 0),
(48, 4, '12', 2, 0),
(49, 5, '1', 1, 0),
(50, 5, '2', 1, 0),
(51, 5, '3', 1, 0),
(52, 5, '4', 1, 0),
(53, 5, '5', 1, 0),
(54, 5, '6', 1, 0),
(55, 5, '7', 1, 0),
(56, 5, '8', 1, 0),
(57, 5, '9', 1, 0),
(58, 5, '10', 1, 0),
(59, 5, '11', 2, 0),
(60, 5, '12', 2, 0),
(61, 1, '13', 2, 0),
(62, 1, '14', 2, 0),
(63, 1, '15', 2, 0),
(64, 1, '16', 2, 0),
(65, 1, '17', 2, 0),
(66, 1, '18', 2, 0),
(67, 1, '19', 2, 0),
(68, 1, '20', 2, 0),
(69, 1, '21', 3, 0),
(70, 1, '22', 3, 0),
(71, 1, '23', 3, 0),
(72, 1, '24', 3, 0),
(73, 2, '13', 2, 0),
(74, 2, '14', 2, 0),
(75, 2, '15', 2, 0),
(76, 2, '16', 2, 0),
(77, 2, '17', 2, 0),
(78, 2, '18', 2, 0),
(79, 2, '19', 2, 0),
(80, 2, '20', 2, 0),
(81, 2, '21', 3, 0),
(82, 2, '22', 3, 0),
(83, 2, '23', 3, 0),
(84, 2, '24', 3, 0),
(85, 3, '13', 2, 0),
(86, 3, '14', 2, 0),
(87, 3, '15', 2, 0),
(88, 3, '16', 2, 0),
(89, 3, '17', 2, 0),
(90, 3, '18', 2, 0),
(91, 3, '19', 2, 0),
(92, 3, '20', 2, 0),
(93, 3, '21', 3, 0),
(94, 3, '22', 3, 0),
(95, 3, '23', 3, 0),
(96, 3, '24', 3, 0),
(97, 4, '13', 2, 0),
(98, 4, '14', 2, 0),
(99, 4, '15', 2, 0),
(100, 4, '16', 2, 0),
(101, 4, '17', 2, 0),
(102, 4, '18', 2, 0),
(103, 4, '19', 2, 0),
(104, 4, '20', 2, 0),
(105, 4, '21', 3, 0),
(106, 4, '22', 3, 0),
(107, 4, '23', 3, 0),
(108, 4, '24', 3, 0),
(109, 5, '13', 2, 0),
(110, 5, '14', 2, 0),
(111, 5, '15', 2, 0),
(112, 5, '16', 2, 0),
(113, 5, '17', 2, 0),
(114, 5, '18', 2, 0),
(115, 5, '19', 2, 0),
(116, 5, '20', 2, 0),
(117, 5, '21', 3, 0),
(118, 5, '22', 3, 0),
(119, 5, '23', 3, 0),
(120, 5, '24', 3, 0),
(121, 1, '25', 3, 0),
(122, 1, '26', 3, 0),
(123, 1, '27', 3, 0),
(124, 1, '28', 3, 0),
(125, 1, '29', 3, 0),
(126, 1, '30', 3, 0),
(127, 1, '31', 4, 0),
(128, 1, '32', 4, 0),
(129, 1, '33', 4, 0),
(130, 1, '34', 4, 0),
(131, 1, '35', 4, 0),
(132, 1, '36', 4, 0),
(133, 2, '25', 3, 0),
(134, 2, '26', 3, 0),
(135, 2, '27', 3, 0),
(136, 2, '28', 3, 0),
(137, 2, '29', 3, 0),
(138, 2, '30', 3, 0),
(139, 2, '31', 4, 0),
(140, 2, '32', 4, 0),
(141, 2, '33', 4, 0),
(142, 2, '34', 4, 0),
(143, 2, '35', 4, 0),
(144, 2, '36', 4, 0),
(145, 3, '25', 3, 0),
(146, 3, '26', 3, 0),
(147, 3, '27', 3, 0),
(148, 3, '28', 3, 0),
(149, 3, '29', 3, 0),
(150, 3, '30', 3, 0),
(151, 3, '31', 4, 0),
(152, 3, '32', 4, 0),
(153, 3, '33', 4, 0),
(154, 3, '34', 4, 0),
(155, 3, '35', 4, 0),
(156, 3, '36', 4, 0),
(157, 4, '25', 3, 0),
(158, 4, '26', 3, 0),
(159, 4, '27', 3, 0),
(160, 4, '28', 3, 0),
(161, 4, '29', 3, 0),
(162, 4, '30', 3, 0),
(163, 4, '31', 4, 0),
(164, 4, '32', 4, 0),
(165, 4, '33', 4, 0),
(166, 4, '34', 4, 0),
(167, 4, '35', 4, 0),
(168, 4, '36', 4, 0),
(169, 5, '25', 3, 0),
(170, 5, '26', 3, 0),
(171, 5, '27', 3, 0),
(172, 5, '28', 3, 0),
(173, 5, '29', 3, 0),
(174, 5, '30', 3, 0),
(175, 5, '31', 4, 0),
(176, 5, '32', 4, 0),
(177, 5, '33', 4, 0),
(178, 5, '34', 4, 0),
(179, 5, '35', 4, 0),
(180, 5, '36', 4, 0),
(181, 1, '37', 4, 0),
(182, 1, '38', 4, 0),
(183, 1, '39', 4, 0),
(184, 1, '40', 4, 0),
(185, 1, '41', 5, 0),
(186, 1, '42', 5, 0),
(187, 1, '43', 5, 0),
(188, 1, '44', 5, 0),
(189, 1, '45', 5, 0),
(190, 1, '46', 5, 0),
(191, 1, '47', 5, 0),
(192, 1, '48', 5, 0),
(193, 2, '37', 4, 0),
(194, 2, '38', 4, 0),
(195, 2, '39', 4, 0),
(196, 2, '40', 4, 0),
(197, 2, '41', 5, 0),
(198, 2, '42', 5, 0),
(199, 2, '43', 5, 0),
(200, 2, '44', 5, 0),
(201, 2, '45', 5, 0),
(202, 2, '46', 5, 0),
(203, 2, '47', 5, 0),
(204, 2, '48', 5, 0),
(205, 3, '37', 4, 0),
(206, 3, '38', 4, 0),
(207, 3, '39', 4, 0),
(208, 3, '40', 4, 0),
(209, 3, '41', 5, 0),
(210, 3, '42', 5, 0),
(211, 3, '43', 5, 0),
(212, 3, '44', 5, 0),
(213, 3, '45', 5, 0),
(214, 3, '46', 5, 0),
(215, 3, '47', 5, 0),
(216, 3, '48', 5, 0),
(217, 4, '37', 4, 0),
(218, 4, '38', 4, 0),
(219, 4, '39', 4, 0),
(220, 4, '40', 4, 0),
(221, 4, '41', 5, 0),
(222, 4, '42', 5, 0),
(223, 4, '43', 5, 0),
(224, 4, '44', 5, 0),
(225, 4, '45', 5, 0),
(226, 4, '46', 5, 0),
(227, 4, '47', 5, 0),
(228, 4, '48', 5, 0),
(229, 5, '37', 4, 0),
(230, 5, '38', 4, 0),
(231, 5, '39', 4, 0),
(232, 5, '40', 4, 0),
(233, 5, '41', 5, 0),
(234, 5, '42', 5, 0),
(235, 5, '43', 5, 0),
(236, 5, '44', 5, 0),
(237, 5, '45', 5, 0),
(238, 5, '46', 5, 0),
(239, 5, '47', 5, 0),
(240, 5, '48', 5, 0),
(241, 1, '49', 5, 0),
(242, 1, '50', 5, 0),
(243, 1, '51', 6, 0),
(244, 1, '52', 6, 0),
(245, 1, '53', 6, 0),
(246, 1, '54', 6, 0),
(247, 1, '55', 6, 0),
(248, 1, '56', 6, 0),
(249, 1, '57', 6, 0),
(250, 1, '58', 6, 0),
(251, 1, '59', 6, 0),
(252, 1, '60', 6, 0),
(253, 2, '49', 5, 0),
(254, 2, '50', 5, 0),
(255, 2, '51', 6, 0),
(256, 2, '52', 6, 0),
(257, 2, '53', 6, 0),
(258, 2, '54', 6, 0),
(259, 2, '55', 6, 0),
(260, 2, '56', 6, 0),
(261, 2, '57', 6, 0),
(262, 2, '58', 6, 0),
(263, 2, '59', 6, 0),
(264, 2, '60', 6, 0),
(265, 3, '49', 5, 0),
(266, 3, '50', 5, 0),
(267, 3, '51', 6, 0),
(268, 3, '52', 6, 0),
(269, 3, '53', 6, 0),
(270, 3, '54', 6, 0),
(271, 3, '55', 6, 0),
(272, 3, '56', 6, 0),
(273, 3, '57', 6, 0),
(274, 3, '58', 6, 0),
(275, 3, '59', 6, 0),
(276, 3, '60', 6, 0),
(277, 4, '49', 5, 0),
(278, 4, '50', 5, 0),
(279, 4, '51', 6, 0),
(280, 4, '52', 6, 0),
(281, 4, '53', 6, 0),
(282, 4, '54', 6, 0),
(283, 4, '55', 6, 0),
(284, 4, '56', 6, 0),
(285, 4, '57', 6, 0),
(286, 4, '58', 6, 0),
(287, 4, '59', 6, 0),
(288, 4, '60', 6, 0),
(289, 5, '49', 5, 0),
(290, 5, '50', 5, 0),
(291, 5, '51', 6, 0),
(292, 5, '52', 6, 0),
(293, 5, '53', 6, 0),
(294, 5, '54', 6, 0),
(295, 5, '55', 6, 0),
(296, 5, '56', 6, 0),
(297, 5, '57', 6, 0),
(298, 5, '58', 6, 0),
(299, 5, '59', 6, 0),
(300, 5, '60', 6, 0),
(301, 1, '61', 7, 0),
(302, 1, '62', 7, 0),
(303, 1, '63', 7, 0),
(304, 1, '64', 7, 0),
(305, 1, '65', 7, 0),
(306, 1, '66', 7, 0),
(307, 1, '67', 7, 0),
(308, 1, '68', 7, 0),
(309, 1, '69', 7, 0),
(310, 1, '70', 7, 0),
(311, 1, '71', 8, 0),
(312, 1, '72', 8, 0),
(313, 2, '61', 7, 0),
(314, 2, '62', 7, 0),
(315, 2, '63', 7, 0),
(316, 2, '64', 7, 0),
(317, 2, '65', 7, 0),
(318, 2, '66', 7, 0),
(319, 2, '67', 7, 0),
(320, 2, '68', 7, 0),
(321, 2, '69', 7, 0),
(322, 2, '70', 7, 0),
(323, 2, '71', 8, 0),
(324, 2, '72', 8, 0),
(325, 3, '61', 7, 0),
(326, 3, '62', 7, 0),
(327, 3, '63', 7, 0),
(328, 3, '64', 7, 0),
(329, 3, '65', 7, 0),
(330, 3, '66', 7, 0),
(331, 3, '67', 7, 0),
(332, 3, '68', 7, 0),
(333, 3, '69', 7, 0),
(334, 3, '70', 7, 0),
(335, 3, '71', 8, 0),
(336, 3, '72', 8, 0),
(337, 4, '61', 7, 0),
(338, 4, '62', 7, 0),
(339, 4, '63', 7, 0),
(340, 4, '64', 7, 0),
(341, 4, '65', 7, 0),
(342, 4, '66', 7, 0),
(343, 4, '67', 7, 0),
(344, 4, '68', 7, 0),
(345, 4, '69', 7, 0),
(346, 4, '70', 7, 0),
(347, 4, '71', 8, 0),
(348, 4, '72', 8, 0),
(349, 5, '61', 7, 0),
(350, 5, '62', 7, 0),
(351, 5, '63', 7, 0),
(352, 5, '64', 7, 0),
(353, 5, '65', 7, 0),
(354, 5, '66', 7, 0),
(355, 5, '67', 7, 0),
(356, 5, '68', 7, 0),
(357, 5, '69', 7, 0),
(358, 5, '70', 7, 0),
(359, 5, '71', 8, 0),
(360, 5, '72', 8, 0),
(361, 1, '73', 8, 0),
(362, 1, '74', 8, 0),
(363, 1, '75', 8, 0),
(364, 1, '76', 8, 0),
(365, 1, '77', 8, 0),
(366, 1, '78', 8, 0),
(367, 1, '79', 8, 0),
(368, 1, '80', 8, 0),
(369, 1, '81', 9, 0),
(370, 1, '82', 9, 0),
(371, 1, '83', 9, 0),
(372, 1, '84', 9, 0),
(373, 2, '73', 8, 0),
(374, 2, '74', 8, 0),
(375, 2, '75', 8, 0),
(376, 2, '76', 8, 0),
(377, 2, '77', 8, 0),
(378, 2, '78', 8, 0),
(379, 2, '79', 8, 0),
(380, 2, '80', 8, 0),
(381, 2, '81', 9, 0),
(382, 2, '82', 9, 0),
(383, 2, '83', 9, 0),
(384, 2, '84', 9, 0),
(385, 3, '73', 8, 0),
(386, 3, '74', 8, 0),
(387, 3, '75', 8, 0),
(388, 3, '76', 8, 0),
(389, 3, '77', 8, 0),
(390, 3, '78', 8, 0),
(391, 3, '79', 8, 0),
(392, 3, '80', 8, 0),
(393, 3, '81', 9, 0),
(394, 3, '82', 9, 0),
(395, 3, '83', 9, 0),
(396, 3, '84', 9, 0),
(397, 4, '73', 8, 0),
(398, 4, '74', 8, 0),
(399, 4, '75', 8, 0),
(400, 4, '76', 8, 0),
(401, 4, '77', 8, 0),
(402, 4, '78', 8, 0),
(403, 4, '79', 8, 0),
(404, 4, '80', 8, 0),
(405, 4, '81', 9, 0),
(406, 4, '82', 9, 0),
(407, 4, '83', 9, 0),
(408, 4, '84', 9, 0),
(409, 5, '73', 8, 0),
(410, 5, '74', 8, 0),
(411, 5, '75', 8, 0),
(412, 5, '76', 8, 0),
(413, 5, '77', 8, 0),
(414, 5, '78', 8, 0),
(415, 5, '79', 8, 0),
(416, 5, '80', 8, 0),
(417, 5, '81', 9, 0),
(418, 5, '82', 9, 0),
(419, 5, '83', 9, 0),
(420, 5, '84', 9, 0),
(421, 1, '85', 9, 0),
(422, 1, '86', 9, 0),
(423, 1, '87', 9, 0),
(424, 1, '88', 9, 0),
(425, 1, '89', 9, 0),
(426, 1, '90', 9, 0),
(427, 1, '91', 10, 0),
(428, 1, '92', 10, 0),
(429, 1, '93', 10, 0),
(430, 1, '94', 10, 0),
(431, 1, '95', 10, 0),
(432, 1, '96', 10, 0),
(433, 2, '85', 9, 0),
(434, 2, '86', 9, 0),
(435, 2, '87', 9, 0),
(436, 2, '88', 9, 0),
(437, 2, '89', 9, 0),
(438, 2, '90', 9, 0),
(439, 2, '91', 10, 0),
(440, 2, '92', 10, 0),
(441, 2, '93', 10, 0),
(442, 2, '94', 10, 0),
(443, 2, '95', 10, 0),
(444, 2, '96', 10, 0),
(445, 3, '85', 9, 0),
(446, 3, '86', 9, 0),
(447, 3, '87', 9, 0),
(448, 3, '88', 9, 0),
(449, 3, '89', 9, 0),
(450, 3, '90', 9, 0),
(451, 3, '91', 10, 0),
(452, 3, '92', 10, 0),
(453, 3, '93', 10, 0),
(454, 3, '94', 10, 0),
(455, 3, '95', 10, 0),
(456, 3, '96', 10, 0),
(457, 4, '85', 9, 0),
(458, 4, '86', 9, 0),
(459, 4, '87', 9, 0),
(460, 4, '88', 9, 0),
(461, 4, '89', 9, 0),
(462, 4, '90', 9, 0),
(463, 4, '91', 10, 0),
(464, 4, '92', 10, 0),
(465, 4, '93', 10, 0),
(466, 4, '94', 10, 0),
(467, 4, '95', 10, 0),
(468, 4, '96', 10, 0),
(469, 5, '85', 9, 0),
(470, 5, '86', 9, 0),
(471, 5, '87', 9, 0),
(472, 5, '88', 9, 0),
(473, 5, '89', 9, 0),
(474, 5, '90', 9, 0),
(475, 5, '91', 10, 0),
(476, 5, '92', 10, 0),
(477, 5, '93', 10, 0),
(478, 5, '94', 10, 0),
(479, 5, '95', 10, 0),
(480, 5, '96', 10, 0),
(481, 1, '97', 10, 0),
(482, 1, '98', 10, 0),
(483, 1, '99', 10, 0),
(484, 1, '100', 10, 0),
(485, 2, '97', 10, 0),
(486, 2, '98', 10, 0),
(487, 2, '99', 10, 0),
(488, 2, '100', 10, 0),
(489, 3, '97', 10, 0),
(490, 3, '98', 10, 0),
(491, 3, '99', 10, 0),
(492, 3, '100', 10, 0),
(493, 4, '97', 10, 0),
(494, 4, '98', 10, 0),
(495, 4, '99', 10, 0),
(496, 4, '100', 10, 0),
(497, 5, '97', 10, 0),
(498, 5, '98', 10, 0),
(499, 5, '99', 10, 0),
(500, 5, '100', 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_urodzenia` date NOT NULL,
  `profile_image` varchar(255) NOT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `imie`, `nazwisko`, `password`, `data_urodzenia`, `profile_image`, `Status`, `created_at`) VALUES
(3, 'shirvinkaya05@gmail.com', 'Justyna', 'Sirvinskaja', '$2y$10$VgrHgSIldY1WaupT0n/Tfe.ThbOMUcJznVfFLR8Mc4NaUE3tZ3.Rq', '2025-01-15', '1742471498_color pallet2.jpg', 'admin', '2025-01-19 13:55:46'),
(6, 'jsirvinskaja@gmail.com', 'userJust', 'user', '$2y$10$LmpSJxsS6d6/aUyDsRuzQO54Ajlya3ldyTB9cXHcdiP32VW4qyrK.', '2024-12-31', '1741681478_c.jpg', 'user', '2025-01-20 13:48:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auditoriums`
--
ALTER TABLE `auditoriums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD PRIMARY KEY (`movie_id`,`genre_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Indexes for table `movie_images`
--
ALTER TABLE `movie_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `purchased_tickets`
--
ALTER TABLE `purchased_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `screening_id` (`screening_id`),
  ADD KEY `seat_id` (`seat_id`);

--
-- Indexes for table `reviews_ratings`
--
ALTER TABLE `reviews_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- Indexes for table `screenings`
--
ALTER TABLE `screenings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `fk_screenings_auditorium_id` (`auditorium_id`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auditorium_id` (`auditorium_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auditoriums`
--
ALTER TABLE `auditoriums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `movie_images`
--
ALTER TABLE `movie_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `purchased_tickets`
--
ALTER TABLE `purchased_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reviews_ratings`
--
ALTER TABLE `reviews_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `screenings`
--
ALTER TABLE `screenings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD CONSTRAINT `movie_genres_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movie_images`
--
ALTER TABLE `movie_images`
  ADD CONSTRAINT `movie_images_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchased_tickets`
--
ALTER TABLE `purchased_tickets`
  ADD CONSTRAINT `purchased_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchased_tickets_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchased_tickets_ibfk_3` FOREIGN KEY (`screening_id`) REFERENCES `screenings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchased_tickets_ibfk_4` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews_ratings`
--
ALTER TABLE `reviews_ratings`
  ADD CONSTRAINT `reviews_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ratings_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `screenings`
--
ALTER TABLE `screenings`
  ADD CONSTRAINT `fk_screenings_auditorium` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_screenings_auditorium_id` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `screenings_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
