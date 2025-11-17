-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 02:05 PM
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
-- Database: `jst_industry`
--

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id_department` int(11) NOT NULL,
  `name_department` varchar(100) NOT NULL,
  `description_department` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id_department`, `name_department`, `description_department`) VALUES
(1, 'ฝ่ายบุคคล (HR)', 'ดูแลพนักงาน สวัสดิการ การจ้างงาน'),
(2, 'ฝ่ายบัญชี', 'ดูแลการเงิน บัญชี รายรับรายจ่าย'),
(3, 'ฝ่ายไอที (IT)', 'ดูแลระบบคอมพิวเตอร์และเครือข่าย'),
(4, 'ฝ่ายคลังสินค้า', 'ดูแลสินค้าคงคลังและอุปกรณ์'),
(5, 'ฝ่ายซ่อมบำรุง', 'ดูแลการซ่อมและบำรุงรักษาอุปกรณ์');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id_employee` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `gender` enum('male','female','other') DEFAULT NULL,
  `prefix` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id_employee`, `firstname`, `lastname`, `department_id`, `position`, `start_date`, `status`, `gender`, `prefix`) VALUES
(1, 'กอไก่', 'สมจริง', 4, 'จัดการคลังสินค้า', '2025-11-16', 'active', 'male', 'นาย'),
(2, 'ขอไข่', 'ของแทร่', 1, 'HR', '2025-11-16', 'active', 'female', 'นางสาว');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id_inventory` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `name_equipment` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `id_item_category` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id_inventory`, `item_code`, `name_equipment`, `type`, `unit`, `current_stock`, `min_stock`, `location`, `status`, `id_item_category`) VALUES
(1, 'RM-001', 'Iron Powder', 'Powder', 'kg', 850, 200, 'Warehouse A1', 'Available', 1),
(2, 'CH-014', 'Isopropyl Alcohol 99%', 'Liquid', 'liter', 120, 50, 'Chemical Room B2', 'Available', 2);

-- --------------------------------------------------------

--
-- Table structure for table `item_category`
--

CREATE TABLE `item_category` (
  `id_item_category` int(11) NOT NULL,
  `name_category` varchar(100) NOT NULL,
  `description_category` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_category`
--

INSERT INTO `item_category` (`id_item_category`, `name_category`, `description_category`) VALUES
(1, 'Raw Materials', 'วัตถุดิบหลักที่ใช้สำหรับกระบวนการผลิต'),
(2, 'Chemical', 'สารเคมีที่ใช้ในโรงงานและในกระบวนการผลิต'),
(3, 'Machine Spare Parts', 'อะไหล่เครื่องจักรและอุปกรณ์'),
(4, 'Safety Equipment', 'อุปกรณ์ความปลอดภัย'),
(5, 'Tools', 'เครื่องมือช่าง'),
(6, 'Packaging', 'วัสดุบรรจุภัณฑ์'),
(7, 'Electrical Parts', 'อุปกรณ์ไฟฟ้า'),
(8, 'Lubricants', 'น้ำมันหล่อลื่น'),
(9, 'Office Supplies', 'อุปกรณ์สำนักงาน'),
(10, 'Cleaning Materials', 'อุปกรณ์ทำความสะอาด');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id_requisitions` int(11) NOT NULL,
  `id_inventory` int(11) NOT NULL,
  `id_employee` int(11) NOT NULL,
  `req_date` date NOT NULL,
  `quantity` int(11) NOT NULL,
  `action_type` varchar(20) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_record`
--

CREATE TABLE `time_record` (
  `id_time_record` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_record`
--

INSERT INTO `time_record` (`id_time_record`, `employee_id`, `work_date`, `status`, `source`, `created_by`, `updated_by`, `check_in_time`, `check_out_time`, `remark`) VALUES
(1, 1, '2025-11-01', 'absent', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, '2025-11-02', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(3, 1, '2025-11-03', 'late', NULL, NULL, NULL, '08:50:00', '17:00:00', NULL),
(4, 1, '2025-11-04', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(5, 1, '2025-11-05', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(6, 1, '2025-11-06', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(7, 1, '2025-11-07', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(8, 1, '2025-11-08', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(9, 1, '2025-11-09', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(10, 1, '2025-11-10', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(11, 1, '2025-11-11', 'sick_leave', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 1, '2025-11-12', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(13, 1, '2025-11-13', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(14, 1, '2025-11-14', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL),
(15, 1, '2025-11-15', 'present', NULL, NULL, NULL, '08:00:00', '17:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `id_employee` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `id_employee`) VALUES
(1, 'admin', '$2y$10$WS.IaHEbU8VDiJgIVO7pZOiMtsNNXCr1GSWb5Tqp1r.LOqpYWxx9a', 'admin', NULL),
(2, 'inven001', '$2y$10$15pQOlhWOnPiGTH.JSp63enRDEJsDs478G6D.8N9CiYN/hhZ2VhAG', 'employee', 1),
(3, 'hr02', '$2y$10$MkzO6pWFE59WOuhT9v1sGe2M119mLqfGWEU.65LAG2ySJOinTHy3u', 'hr', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id_department`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id_employee`),
  ADD KEY `fk_employee_department` (`department_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id_inventory`),
  ADD UNIQUE KEY `uk_inventory_item_code` (`item_code`),
  ADD KEY `fk_inventory_category` (`id_item_category`);

--
-- Indexes for table `item_category`
--
ALTER TABLE `item_category`
  ADD PRIMARY KEY (`id_item_category`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id_requisitions`),
  ADD KEY `fk_req_inventory` (`id_inventory`),
  ADD KEY `fk_req_employee` (`id_employee`);

--
-- Indexes for table `time_record`
--
ALTER TABLE `time_record`
  ADD PRIMARY KEY (`id_time_record`),
  ADD KEY `fk_timerecord_employee` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD KEY `fk_users_employee` (`id_employee`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id_department` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id_employee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id_inventory` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `item_category`
--
ALTER TABLE `item_category`
  MODIFY `id_item_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id_requisitions` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_record`
--
ALTER TABLE `time_record`
  MODIFY `id_time_record` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `fk_employee_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`id_department`) ON UPDATE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_category` FOREIGN KEY (`id_item_category`) REFERENCES `item_category` (`id_item_category`);

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `fk_req_employee` FOREIGN KEY (`id_employee`) REFERENCES `employee` (`id_employee`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_req_inventory` FOREIGN KEY (`id_inventory`) REFERENCES `inventory` (`id_inventory`) ON UPDATE CASCADE;

--
-- Constraints for table `time_record`
--
ALTER TABLE `time_record`
  ADD CONSTRAINT `fk_timerecord_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id_employee`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee` FOREIGN KEY (`id_employee`) REFERENCES `employee` (`id_employee`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
