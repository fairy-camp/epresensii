-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 10, 2026 at 07:00 AM
-- Server version: 8.4.3
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `epresensi_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `apel_attendances`
--

CREATE TABLE `apel_attendances` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `scan_time` time NOT NULL,
  `status` enum('present','late','absent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apel_attendances`
--

INSERT INTO `apel_attendances` (`id`, `teacher_id`, `date`, `scan_time`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-08-08', '11:22:12', 'late', NULL, '2026-08-08 04:22:12', '2026-08-08 04:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `shift_assignment_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `status` enum('present','late','absent','early_leave') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `geolocation_logs`
--

CREATE TABLE `geolocation_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `attendance_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `accuracy_meters` decimal(6,2) DEFAULT NULL,
  `distance_from_school` decimal(10,2) DEFAULT NULL,
  `is_within_radius` tinyint(1) NOT NULL DEFAULT '0',
  `permission_status` enum('granted','denied','unavailable') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_07_29_054238_create_users_table', 1),
(2, '2026_07_29_054254_create_positions_table', 1),
(3, '2026_07_29_054301_create_work_schedules_table', 1),
(4, '2026_07_29_054308_create_school_settings_table', 1),
(5, '2026_07_29_054315_create_teachers_table', 1),
(6, '2026_07_29_054321_create_shift_assignments_table', 1),
(7, '2026_07_29_054329_create_qr_codes_table', 1),
(8, '2026_07_29_054454_create_attendance_records_table', 1),
(9, '2026_07_29_054501_create_geolocation_logs_table', 1),
(10, '2026_07_29_054508_create_audit_logs_table', 1),
(11, '2026_07_29_060050_create_sessions_table', 1),
(12, '2026_08_04_125406_remove_date_from_shift_assignments_table', 1),
(13, '2026_08_05_090906_fix_attendance_records_unique_index', 1),
(14, '2026_08_08_084412_create_apel_attendances_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_type` enum('kepala_sekolah','waka','guru','petugas','satpam','staff') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_management` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`, `role_type`, `description`, `is_management`, `created_at`, `updated_at`) VALUES
(1, 'Kepala Sekolah', 'kepala_sekolah', 'Kepala Sekolah', 1, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(2, 'Waka', 'waka', 'Wakil Kepala Sekolah', 1, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(3, 'Guru Pengajar', 'guru', 'Guru Mata Pelajaran', 0, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(4, 'Staff dan Karyawan', 'staff', 'Staff dan Karyawan', 0, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(5, 'Satpam / Security', 'satpam', 'Petugas Keamanan Sekolah', 0, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(6, 'Petugas Presensi', 'petugas', 'Petugas Presensi', 0, '2026-08-08 04:13:01', '2026-08-08 04:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`id`, `teacher_id`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'QR-DUKCS7IQXX-1786162853', 1, '2026-08-08 04:20:53', '2026-08-08 04:20:53'),
(2, 2, 'QR-VQVNO6NJUW-1786162900', 1, '2026-08-08 04:21:40', '2026-08-08 04:21:40'),
(3, 3, 'QR-FYGXMPMOEP-1786340805', 1, '2026-08-10 05:46:45', '2026-08-10 05:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `school_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `geofence_radius` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`id`, `school_name`, `latitude`, `longitude`, `geofence_radius`, `created_at`, `updated_at`) VALUES
(1, 'SMK Syafi\'i Akrom', -6.91425769, 109.66765112, 100, '2026-08-08 04:13:02', '2026-08-10 03:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shift_assignments`
--

CREATE TABLE `shift_assignments` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `work_schedule_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_assignments`
--

INSERT INTO `shift_assignments` (`id`, `teacher_id`, `work_schedule_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2026-08-10 05:46:58', '2026-08-10 05:46:58'),
(2, 3, 1, '2026-08-10 05:46:58', '2026-08-10 05:46:58');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nuptk` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_id` bigint UNSIGNED DEFAULT NULL,
  `waka_id` bigint UNSIGNED DEFAULT NULL,
  `tmt` date DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_schedule_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `nip`, `nik`, `nuptk`, `npy`, `full_name`, `gender`, `photo`, `department`, `position_id`, `waka_id`, `tmt`, `phone`, `work_schedule_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, '11', NULL, NULL, NULL, 'petugas 1', 'L', NULL, NULL, 6, NULL, NULL, NULL, 1, 1, '2026-08-08 04:20:53', '2026-08-08 04:20:53'),
(2, 4, '22', NULL, NULL, NULL, 'imam ichsan arifin, S.Kom', 'L', NULL, NULL, 3, NULL, NULL, NULL, 1, 1, '2026-08-08 04:21:40', '2026-08-08 04:21:40'),
(3, 5, '33', NULL, NULL, NULL, 'M. Bachtiar', 'L', NULL, NULL, 3, NULL, NULL, NULL, 1, 1, '2026-08-10 05:46:45', '2026-08-10 05:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','guru','kepala_sekolah','waka','satpam','staff','petugas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guru',
  `role_keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `role_keterangan`, `is_hidden`, `is_active`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'superadmin@codepelita.sch.id', '$2y$12$TvfavIojthuO4I2xoqvqsuJjTrIB9w74zD2KYRk9Huo88/nQhFyjW', 'super_admin', 'Super Admin System', 1, 1, NULL, NULL, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(2, 'admin@codepelita.sch.id', '$2y$12$nmZwczoxDHyEsKJ5OsPruOr.zJ6f3NvGEV4PFKLymfy90mfUXgFuS', 'admin', 'Administrator Sekolah', 0, 1, NULL, NULL, '2026-08-08 04:13:01', '2026-08-08 04:13:01'),
(3, 'petugas@codepelita.sch.id', '$2y$12$oXflE7DWTjM0GinwCagFiODNH9DcrOhb3A32tr1CwKoN7aDvM8B7a', 'petugas', NULL, 0, 1, NULL, NULL, '2026-08-08 04:20:53', '2026-08-08 04:20:53'),
(4, 'imam@codepelita.sch.id', '$2y$12$5IoGH1CYx55BV93746UQ2ufAXOyh3kTggYaApZlpGYhdmzYVELuP.', 'guru', NULL, 0, 1, NULL, NULL, '2026-08-08 04:21:40', '2026-08-08 04:21:40'),
(5, 'bachtiar@codepelita.sch.id', '$2y$12$HXgWpEAgFf/Q/d9VW.6W2OHiirsJP/f9O8mm3wx6hqNiXQYpLPsWy', 'guru', NULL, 0, 1, NULL, NULL, '2026-08-10 05:46:45', '2026-08-10 05:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('fixed','shift') COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_in_time` time NOT NULL,
  `check_out_time` time NOT NULL,
  `late_tolerance_minutes` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`id`, `name`, `type`, `check_in_time`, `check_out_time`, `late_tolerance_minutes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Reguler (Guru & Staff)', 'fixed', '06:46:00', '11:30:00', 15, 1, '2026-08-08 04:13:02', '2026-08-10 05:48:03'),
(2, 'Shift Pagi (Satpam)', 'shift', '06:00:00', '14:00:00', 10, 1, '2026-08-08 04:13:02', '2026-08-08 04:13:02'),
(3, 'Shift Siang (Satpam)', 'shift', '14:00:00', '22:00:00', 10, 1, '2026-08-08 04:13:02', '2026-08-08 04:13:02'),
(5, 'Shitt Malam (Satpam)', 'fixed', '22:00:00', '06:00:00', 0, 1, '2026-08-10 05:42:33', '2026-08-10 05:42:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apel_attendances`
--
ALTER TABLE `apel_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apel_attendances_teacher_id_date_unique` (`teacher_id`,`date`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `att_teacher_shift_date_unique` (`teacher_id`,`shift_assignment_id`,`date`),
  ADD KEY `attendance_records_shift_assignment_id_foreign` (`shift_assignment_id`),
  ADD KEY `attendance_records_teacher_id_date_index` (`teacher_id`,`date`),
  ADD KEY `attendance_records_date_index` (`date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_created_at_index` (`created_at`);

--
-- Indexes for table `geolocation_logs`
--
ALTER TABLE `geolocation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `geolocation_logs_attendance_id_foreign` (`attendance_id`),
  ADD KEY `geolocation_logs_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `positions_name_unique` (`name`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_codes_code_unique` (`code`),
  ADD KEY `qr_codes_teacher_id_foreign` (`teacher_id`),
  ADD KEY `qr_codes_code_index` (`code`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shift_assignments`
--
ALTER TABLE `shift_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shift_assignments_teacher_id_unique` (`teacher_id`),
  ADD KEY `shift_assignments_work_schedule_id_foreign` (`work_schedule_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teachers_nip_unique` (`nip`),
  ADD UNIQUE KEY `teachers_nik_unique` (`nik`),
  ADD KEY `teachers_user_id_foreign` (`user_id`),
  ADD KEY `teachers_position_id_foreign` (`position_id`),
  ADD KEY `teachers_waka_id_foreign` (`waka_id`),
  ADD KEY `teachers_work_schedule_id_foreign` (`work_schedule_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apel_attendances`
--
ALTER TABLE `apel_attendances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geolocation_logs`
--
ALTER TABLE `geolocation_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shift_assignments`
--
ALTER TABLE `shift_assignments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apel_attendances`
--
ALTER TABLE `apel_attendances`
  ADD CONSTRAINT `apel_attendances_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_shift_assignment_id_foreign` FOREIGN KEY (`shift_assignment_id`) REFERENCES `shift_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `geolocation_logs`
--
ALTER TABLE `geolocation_logs`
  ADD CONSTRAINT `geolocation_logs_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance_records` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `geolocation_logs_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shift_assignments`
--
ALTER TABLE `shift_assignments`
  ADD CONSTRAINT `shift_assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shift_assignments_work_schedule_id_foreign` FOREIGN KEY (`work_schedule_id`) REFERENCES `work_schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_waka_id_foreign` FOREIGN KEY (`waka_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teachers_work_schedule_id_foreign` FOREIGN KEY (`work_schedule_id`) REFERENCES `work_schedules` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
