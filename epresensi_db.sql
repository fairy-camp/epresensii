-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 30, 2026 at 06:26 AM
-- Server version: 8.0.30
-- PHP Version: 8.4.23

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
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shift_assignment_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attendance_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
(11, '2026_07_29_060050_create_sessions_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_type` enum('guru','waka','kepala_sekolah','petugas','kepala_kompetensi','staff_waka') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_management` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`, `role_type`, `description`, `is_management`, `created_at`, `updated_at`) VALUES
('019fb0db-3658-7177-afd0-f189f678c95a', 'Kepala Sekolah', 'kepala_sekolah', 'Kepala Sekolah Utama', 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-365a-708b-be90-250e1b688c09', 'Waka Kurikulum', 'waka', 'Wakil Kepala Sekolah Bidang Kurikulum', 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-365b-712e-9856-f24d07d8e473', 'Waka Kesiswaan', 'waka', 'Wakil Kepala Sekolah Bidang Kesiswaan', 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-365d-70ac-a9db-98ae3ee0f82d', 'Waka Sarana Prasarana', 'waka', 'Wakil Kepala Sekolah Bidang Sarpras', 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-365e-71c9-9f89-f8aa545029aa', 'Waka Humas', 'waka', 'Wakil Kepala Sekolah Bidang Hubungan Masyarakat', 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-3660-700b-97a1-2a7a079a77ba', 'Kepala Kompetensi RPL', 'kepala_kompetensi', 'Kepala Program Keahlian RPL', 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-3661-72e0-817d-15799ed44302', 'Guru Pengajar', 'guru', 'Guru Mata Pelajaran', 0, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-3662-7104-84c7-b74168e7583f', 'Staff Waka Kurikulum', 'staff_waka', 'Guru Pembantu Waka Kurikulum', 0, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-3664-714f-86e2-6459f2548705', 'Satpam / Security', 'petugas', 'Petugas Keamanan Sekolah', 0, '2026-07-29 19:29:44', '2026-07-29 19:29:44');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`id`, `teacher_id`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
('019fb0db-fb8f-70aa-891d-e548d4a71223', '019fb0db-fb8d-7327-adf8-90e826fc1a78', 'QR-NLMGFYMAHN-1785378634', 1, '2026-07-29 19:30:34', '2026-07-29 19:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
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
('019fb0db-3679-73ed-8635-8cd0ad285c13', 'SMK UP RPL CodePelita', -6.91534770, 109.66747661, 1000, '2026-07-29 19:29:44', '2026-07-29 20:09:49');

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
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_schedule_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nuptk` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waka_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmt` date DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_schedule_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `nip`, `nik`, `nuptk`, `npy`, `full_name`, `gender`, `photo`, `department`, `position_id`, `waka_id`, `tmt`, `phone`, `work_schedule_id`, `is_active`, `created_at`, `updated_at`) VALUES
('019fb0db-fb8d-7327-adf8-90e826fc1a78', '019fb0db-fb87-700e-b3e7-34e189233211', '332511', '333333333333', NULL, NULL, 'Guru 1', 'L', NULL, NULL, '019fb0db-3661-72e0-817d-15799ed44302', NULL, NULL, '08555555555', '019fb0db-3669-7118-ba35-06c7ac32fcbd', 1, '2026-07-29 19:30:34', '2026-07-29 19:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','guru','kepala_sekolah','waka','satpam','staff') COLLATE utf8mb4_unicode_ci NOT NULL,
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
('019fb0db-3537-7129-9c03-76853f51a745', 'superadmin@codepelita.sch.id', '$2y$12$LBQHdJcJ1W1roFMDOA107etMHApiMeoCDlBs/jzxUB3YTA1ZuCB6y', 'super_admin', 'Super Admin System', 1, 1, NULL, NULL, '2026-07-29 19:29:43', '2026-07-29 19:29:43'),
('019fb0db-364c-70ef-ad63-e7b61f8fa24a', 'admin@codepelita.sch.id', '$2y$12$bjd7.xhfZ5q052RhRl/.RO.Kkyzhqcg/eI5Nv51lZRp/lmJsVxuJy', 'admin', 'Administrator Sekolah', 0, 1, NULL, NULL, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-fb87-700e-b3e7-34e189233211', 'guru@codepelita.sch.id', '$2y$12$fr0TA/jajtL3WoJSeP6.YegmSjCfdIEu.HnDBY.sP8wDw1ByGG7m2', 'guru', NULL, 0, 1, NULL, NULL, '2026-07-29 19:30:34', '2026-07-29 19:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
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
('019fb0db-3669-7118-ba35-06c7ac32fcbd', 'Reguler (Guru & Staff)', 'fixed', '06:45:00', '14:00:00', 15, 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-366d-7056-8456-9e1db86034d1', 'Shift Pagi (Satpam)', 'shift', '06:00:00', '14:00:00', 10, 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-3670-710e-a17e-979189f8cf34', 'Shift Siang (Satpam)', 'shift', '14:00:00', '22:00:00', 10, 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44'),
('019fb0db-3671-7090-b42f-50e51e5de216', 'Shift Malam (Satpam)', 'shift', '22:00:00', '06:00:00', 10, 1, '2026-07-29 19:29:44', '2026-07-29 19:29:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_records_teacher_id_shift_assignment_id_unique` (`teacher_id`,`shift_assignment_id`),
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
  ADD KEY `shift_assignments_teacher_id_foreign` (`teacher_id`),
  ADD KEY `shift_assignments_work_schedule_id_foreign` (`work_schedule_id`),
  ADD KEY `shift_assignments_date_index` (`date`);

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
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

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
