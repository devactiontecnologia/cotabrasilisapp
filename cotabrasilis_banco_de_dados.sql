-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: cotabrasilis
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_logs_admin_id_foreign` (`admin_id`),
  CONSTRAINT `admin_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_logs_chk_1` CHECK (json_valid(`old_data`)),
  CONSTRAINT `admin_logs_chk_2` CHECK (json_valid(`new_data`))
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advanced_auctions`
--

DROP TABLE IF EXISTS `advanced_auctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `advanced_auctions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rental_offer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NOT NULL,
  `minimum_price` decimal(10,2) NOT NULL,
  `duration_minutes` int NOT NULL,
  `bid_extension_minutes` int NOT NULL DEFAULT '1',
  `status` enum('scheduled','active','ended','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `current_bid` decimal(10,2) DEFAULT NULL,
  `current_winner_id` bigint unsigned DEFAULT NULL,
  `total_bids` int NOT NULL DEFAULT '0',
  `auction_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `auto_extend` tinyint(1) NOT NULL DEFAULT '0',
  `max_extensions` int NOT NULL DEFAULT '3',
  `extensions_used` int NOT NULL DEFAULT '0',
  `last_bid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advanced_auctions_rental_offer_id_foreign` (`rental_offer_id`),
  KEY `advanced_auctions_current_winner_id_foreign` (`current_winner_id`),
  KEY `advanced_auctions_status_start_time_index` (`status`,`start_time`),
  KEY `advanced_auctions_end_time_status_index` (`end_time`,`status`),
  KEY `advanced_auctions_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `advanced_auctions_current_winner_id_foreign` FOREIGN KEY (`current_winner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `advanced_auctions_rental_offer_id_foreign` FOREIGN KEY (`rental_offer_id`) REFERENCES `rental_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `advanced_auctions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `advanced_auctions_chk_1` CHECK (json_valid(`auction_rules`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advanced_auctions`
--

LOCK TABLES `advanced_auctions` WRITE;
/*!40000 ALTER TABLE `advanced_auctions` DISABLE KEYS */;
/*!40000 ALTER TABLE `advanced_auctions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auction_limits`
--

DROP TABLE IF EXISTS `auction_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auction_limits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `quota_id` bigint unsigned NOT NULL,
  `auctions_used` int NOT NULL DEFAULT '0',
  `auctions_limit` int NOT NULL DEFAULT '0',
  `limit_period` enum('year','month','usage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'year',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auction_limits_user_id_quota_id_limit_period_period_start_unique` (`user_id`,`quota_id`,`limit_period`,`period_start`),
  KEY `auction_limits_user_id_limit_period_period_start_index` (`user_id`,`limit_period`,`period_start`),
  KEY `auction_limits_quota_id_limit_period_index` (`quota_id`,`limit_period`),
  CONSTRAINT `auction_limits_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auction_limits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auction_limits`
--

LOCK TABLES `auction_limits` WRITE;
/*!40000 ALTER TABLE `auction_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `auction_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auctions`
--

DROP TABLE IF EXISTS `auctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auctions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rental_offer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `is_winning_bid` tinyint(1) NOT NULL DEFAULT '0',
  `bid_at` timestamp NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auctions_rental_offer_id_bid_amount_index` (`rental_offer_id`,`bid_amount`),
  KEY `auctions_user_id_bid_at_index` (`user_id`,`bid_at`),
  CONSTRAINT `auctions_rental_offer_id_foreign` FOREIGN KEY (`rental_offer_id`) REFERENCES `rental_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auctions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auctions`
--

LOCK TABLES `auctions` WRITE;
/*!40000 ALTER TABLE `auctions` DISABLE KEYS */;
/*!40000 ALTER TABLE `auctions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `digital_contracts`
--

DROP TABLE IF EXISTS `digital_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `contract_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_signature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `renter_signature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `owner_signed_at` timestamp NULL DEFAULT NULL,
  `renter_signed_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `hotel_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_to_hotel` tinyint(1) NOT NULL DEFAULT '0',
  `sent_to_hotel_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digital_contracts_transaction_id_foreign` (`transaction_id`),
  CONSTRAINT `digital_contracts_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `quota_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `digital_contracts_chk_1` CHECK (json_valid(`owner_signature`)),
  CONSTRAINT `digital_contracts_chk_2` CHECK (json_valid(`renter_signature`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `digital_contracts`
--

LOCK TABLES `digital_contracts` WRITE;
/*!40000 ALTER TABLE `digital_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `digital_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `educational_contents`
--

DROP TABLE IF EXISTS `educational_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `educational_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content_type` enum('article','guide','faq','tutorial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'article',
  `profile_type_required` enum('curioso','inteligente','sabio') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `educational_contents_is_active_profile_type_required_index` (`is_active`,`profile_type_required`),
  KEY `educational_contents_category_index` (`category`),
  CONSTRAINT `educational_contents_chk_1` CHECK (json_valid(`tags`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `educational_contents`
--

LOCK TABLES `educational_contents` WRITE;
/*!40000 ALTER TABLE `educational_contents` DISABLE KEYS */;
/*!40000 ALTER TABLE `educational_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `educational_videos`
--

DROP TABLE IF EXISTS `educational_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `educational_videos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `educational_content_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `profile_type_required` enum('curioso','inteligente','sabio') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `views_count` int NOT NULL DEFAULT '0',
  `likes_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `educational_videos_educational_content_id_foreign` (`educational_content_id`),
  KEY `educational_videos_is_active_profile_type_required_index` (`is_active`,`profile_type_required`),
  KEY `educational_videos_category_index` (`category`),
  CONSTRAINT `educational_videos_educational_content_id_foreign` FOREIGN KEY (`educational_content_id`) REFERENCES `educational_contents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `educational_videos_chk_1` CHECK (json_valid(`tags`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `educational_videos`
--

LOCK TABLES `educational_videos` WRITE;
/*!40000 ALTER TABLE `educational_videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `educational_videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exchange_offers`
--

DROP TABLE IF EXISTS `exchange_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exchange_offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `quota_id` bigint unsigned NOT NULL,
  `exchange_type` enum('semana','titularidade') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'semana',
  `desired_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desired_period_start` date DEFAULT NULL,
  `desired_period_end` date DEFAULT NULL,
  `desired_hotel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desired_people` int DEFAULT NULL,
  `desired_rooms` int DEFAULT NULL,
  `price_range_min` decimal(10,2) DEFAULT NULL,
  `price_range_max` decimal(10,2) DEFAULT NULL,
  `exchange_mode` enum('simples','mais') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'simples',
  `additional_value` decimal(10,2) DEFAULT NULL,
  `days_difference` int DEFAULT NULL,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','negotiating','completed','cancelled','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `validity_until` timestamp NULL DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `max_options` int NOT NULL DEFAULT '3',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exchange_offers_quota_id_foreign` (`quota_id`),
  KEY `exchange_offers_status_validity_until_index` (`status`,`validity_until`),
  KEY `exchange_offers_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `exchange_offers_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exchange_offers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exchange_offers_chk_1` CHECK (json_valid(`selected_options`))
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exchange_offers`
--

LOCK TABLES `exchange_offers` WRITE;
/*!40000 ALTER TABLE `exchange_offers` DISABLE KEYS */;
/*!40000 ALTER TABLE `exchange_offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorite_list_items`
--

DROP TABLE IF EXISTS `favorite_list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorite_list_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `favorite_list_id` bigint unsigned NOT NULL,
  `quota_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorite_list_items_favorite_list_id_quota_id_unique` (`favorite_list_id`,`quota_id`),
  KEY `favorite_list_items_quota_id_foreign` (`quota_id`),
  CONSTRAINT `favorite_list_items_favorite_list_id_foreign` FOREIGN KEY (`favorite_list_id`) REFERENCES `favorite_lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorite_list_items_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorite_list_items`
--

LOCK TABLES `favorite_list_items` WRITE;
/*!40000 ALTER TABLE `favorite_list_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorite_list_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorite_lists`
--

DROP TABLE IF EXISTS `favorite_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorite_lists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('city','hotel','state') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_type` enum('rental','purchase','exchange') COLLATE utf8mb4_unicode_ci DEFAULT 'rental',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorite_lists_user_id_foreign` (`user_id`),
  CONSTRAINT `favorite_lists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorite_lists`
--

LOCK TABLES `favorite_lists` WRITE;
/*!40000 ALTER TABLE `favorite_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorite_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hospitality_authorizations`
--

DROP TABLE IF EXISTS `hospitality_authorizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hospitality_authorizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rental_offer_id` bigint unsigned NOT NULL,
  `quota_id` bigint unsigned NOT NULL,
  `guest_user_id` bigint unsigned NOT NULL,
  `authorization_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_document` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `number_of_guests` int NOT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected','used','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `hotel_notes` text COLLATE utf8mb4_unicode_ci,
  `is_transferable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hospitality_authorizations_authorization_code_unique` (`authorization_code`),
  KEY `hospitality_authorizations_rental_offer_id_foreign` (`rental_offer_id`),
  KEY `hospitality_authorizations_quota_id_foreign` (`quota_id`),
  KEY `hospitality_authorizations_guest_user_id_foreign` (`guest_user_id`),
  CONSTRAINT `hospitality_authorizations_guest_user_id_foreign` FOREIGN KEY (`guest_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hospitality_authorizations_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hospitality_authorizations_rental_offer_id_foreign` FOREIGN KEY (`rental_offer_id`) REFERENCES `rental_offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hospitality_authorizations`
--

LOCK TABLES `hospitality_authorizations` WRITE;
/*!40000 ALTER TABLE `hospitality_authorizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `hospitality_authorizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotels`
--

DROP TABLE IF EXISTS `hotels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `stars` int NOT NULL DEFAULT '3',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_functioning` tinyint(1) NOT NULL DEFAULT '1',
  `status_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `hotels_chk_1` CHECK (json_valid(`images`)),
  CONSTRAINT `hotels_chk_2` CHECK (json_valid(`amenities`))
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotels`
--

LOCK TABLES `hotels` WRITE;
/*!40000 ALTER TABLE `hotels` DISABLE KEYS */;
INSERT INTO `hotels` VALUES (1,'Hotel Exemplo','São Paulo, SP','São Paulo','SP','Rua das Flores, 123','01234-567','(11) 1234-5678','contato@hotelexemplo.com.br','Hotel de exemplo para demonstração do sistema','[\"hotels/690cb8619ecfc_São Paolo.jpg\", \"hotels/690cb8619f699_São Paolo1.webp\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]',NULL,NULL,4,1,1,NULL,'2025-10-24 23:56:07','2025-11-06 18:01:53'),(2,'Resort Praia Dourada','Rio de Janeiro, RJ','Rio de Janeiro','RJ','Av. Beira Mar, 456','22000-000','(21) 9876-5432','reservas@praiadourada.com.br','Resort à beira-mar com vista para o oceano','[\"hotels/690cb861a633b_Rio de Janeiro.jpg\", \"hotels/690cb861a6dd2_Búzios.jpg\", \"hotels/690cb861a7492_Búzios1.jpg\"]',NULL,NULL,NULL,5,1,1,NULL,'2025-10-24 23:56:07','2025-11-06 18:01:53'),(3,'Pousada Serra Verde','Campos do Jordão, SP','Campos do Jordão','SP','Rua da Montanha, 789','12460-000','(12) 3456-7890','contato@serraverde.com.br','Pousada aconchegante na serra paulista','[\"hotels/690cb861a88bd_São Paolo.jpg\", \"hotels/690cb861a9042_São Paolo1.webp\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]',NULL,NULL,NULL,3,1,1,NULL,'2025-10-24 23:56:07','2025-11-06 18:01:53'),(4,'Hotel Business Center','Belo Horizonte, MG','Belo Horizonte','MG','Av. Afonso Pena, 1000','30130-000','(31) 2345-6789','reservas@businesscenter.com.br','Hotel executivo no centro da cidade','[\"hotels/690cb861aa6ee_Goiás.jpg\", \"hotels/690cb861ab02b_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]',NULL,NULL,NULL,4,1,1,NULL,'2025-10-24 23:56:07','2025-11-06 18:01:53'),(5,'Resort Amazônia','Manaus, AM','Manaus','AM','Rodovia AM-010, Km 15','69000-000','(92) 3456-7890','contato@amazoniaresort.com.br','Resort ecológico na floresta amazônica','[\"hotels/690cb861ac63b_Amazônia.jpg\", \"hotels/690cb861acc82_Manaus.webp\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]',NULL,NULL,NULL,5,1,1,NULL,'2025-10-24 23:56:07','2025-11-06 18:01:53'),(6,'Ipioca Beach Residence','Maceió, AL','Maceió','AL','AL-101, 649 – Ipioca','57039-700','(82) 3321-5000','contato@ipiocabeach.com.br','Resort à beira-mar em Ipioca, Maceió, com vista para o oceano Atlântico.','[\"hotels/690cb861adfd9_Bahia.jpg\", \"hotels/690cb861ae601_Bahia1.jpg\", \"hotels/690cb861aec85_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.ipiocabeach.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(7,'Casa Blanca Park','Maceió, AL','Maceió','AL','Av. Beira Mar, s/n','57000-000','(82) 3322-6000','reservas@casablancapark.com.br','Hotel resort com ampla estrutura de lazer e hospedagem.','[\"hotels/690cb861b00a6_Bahia.jpg\", \"hotels/690cb861b05ba_Bahia1.jpg\", \"hotels/690cb861b0b3e_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.casablancapark.com.br',4.30,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(8,'Ondas Praia Resort','Porto Seguro, BA','Porto Seguro','BA','Av. Beira Mar (BR-367), nº 12.675, Km 75, Mutá','45810-000','(73) 3288-1000','contato@ondaspraia.com.br','Resort à beira-mar em Porto Seguro com estrutura completa de lazer.','[\"hotels/690cb861b1d61_Bahia.jpg\", \"hotels/690cb861b22a3_Bahia1.jpg\", \"hotels/690cb861b28a6_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.vivantecobeach.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(9,'Casa Blanca Park Hotel','Porto Seguro, BA','Porto Seguro','BA','Av. 22 de Abril, 435 – Centro','45810-000','(73) 3288-2000','reservas@casablancapark.com.br','Hotel no centro de Porto Seguro com fácil acesso às praias.','[\"hotels/690cb861b3dde_Bahia.jpg\", \"hotels/690cb861b42ba_Bahia1.jpg\", \"hotels/690cb861b480c_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.casablancapark.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(10,'Vivant Ecobeach Resort','Maraú, BA','Maraú','BA','Barra Grande — Taipus de Fora','45520-000','(73) 3258-3000','contato@vivantecobeach.com.br','Resort ecológico em Barra Grande, uma das praias mais belas da Bahia.','[\"hotels/690cb861b59c2_Bahia.jpg\", \"hotels/690cb861b5f05_Bahia1.jpg\", \"hotels/690cb861b6532_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.vivantecobeach.com.br',4.80,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(11,'Makaira Beach Resort','Canavieiras, BA','Canavieiras','BA','Ilha de Atalaia','45860-000','(73) 3284-4000','reservas@makaira.com.br','Resort exclusivo na Ilha de Atalaia, entre o Rio Patipe e o Oceano Atlântico.','[\"hotels/690cb861b79ef_Bahia.jpg\", \"hotels/690cb861b7f77_Bahia1.jpg\", \"hotels/690cb861b851b_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.makaira.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(12,'Tree Bies Resort','Entre Rios, BA','Entre Rios','BA','Praia de Subaúma — Lot. Miramar','48190-000','(75) 3431-5000','contato@treebies.com.br','Resort na Praia de Subaúma com estrutura completa de lazer.','[\"hotels/690cb861b97d3_Bahia.jpg\", \"hotels/690cb861b9d5d_Bahia1.jpg\", \"hotels/690cb861ba3e0_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.treebies.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(13,'Dom Pedro Laguna','Aquiraz, CE','Aquiraz','CE','Av. Marginal do Empreendimento Aquiraz Riviera, s/n – Praia da Marambaia','61700-000','(85) 3361-6000','reservas@dompedrolaguna.com.br','Resort na Praia da Marambaia com vista para o mar e lagoa.','[\"hotels/690cb861bbbc7_Beach Park Ceará.webp\", \"hotels/690cb861bc2e5_Beach Park Ceará1.jpg\", \"hotels/690cb861bd407_Jericoacoara.webp\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.dompedrolaguna.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(14,'The Coral Beach Resort','Trairi, CE','Trairi','CE','Praia de Guajiru / Flecheiras — Rua 01, S/N, Lotes 3 e 4','62690-000','(85) 3315-7000','contato@coralbeach.com.br','Resort nas praias de Guajiru e Flecheiras, uma das mais belas do Ceará.','[\"hotels/690cb861bf153_Beach Park Ceará.webp\", \"hotels/690cb861bf906_Beach Park Ceará1.jpg\", \"hotels/690cb861c09ca_Jericoacoara.webp\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.coralbeach.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(15,'Gran Lençóis Flat Residence','Barreirinhas, MA','Barreirinhas','MA','Estrada de São Domingos, s/n — Povoado Boa Vista','65590-000','(98) 3349-8000','reservas@granlencois.com.br','Flat residence próximo aos Lençóis Maranhenses, uma das maravilhas naturais do Brasil.','[\"hotels/690cb861c1e48_Lençóis Maranhenses.jpg\", \"hotels/690cb861c24d8_Lençóis Maranhenses1.jpg\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.granlencoisflat.com',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(16,'Infinity at The Sea','João Pessoa, PB','João Pessoa','PB','Av. Cabo Branco, 1780 – Cabo Branco','58045-010','(83) 3247-9000','contato@infinityatthesea.com.br','Hotel à beira-mar em Cabo Branco, João Pessoa.','[\"hotels/690cb861c3900_Bahia.jpg\", \"hotels/690cb861c3fdd_Bahia1.jpg\", \"hotels/690cb861c4695_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.infinityatthesea.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(17,'Asenza Beach Resort','Pitimbú, PB','Pitimbú','PB','Litoral sul da Paraíba, ao lado da Barra do rio Abiaí','58324-000','(83) 3298-1000','reservas@asenza.com.br','Resort no litoral sul da Paraíba, próximo à Barra do rio Abiaí.','[\"hotels/690cb861c5bd7_Bahia.jpg\", \"hotels/690cb861c61fc_Bahia1.jpg\", \"hotels/690cb861c68d6_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.asenzabeachresort.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(18,'Porto Alto Resort','Ipojuca, PE','Ipojuca','PE','Rua MA 01, Lote 02-B, s/n – em frente à Praia de Muro Alto','55900-000','(81) 3552-2000','contato@portoalto.com.br','Resort em frente à Praia de Muro Alto, próximo a Porto de Galinhas.','[\"hotels/690cb861c857b_Recife.jpg\", \"hotels/690cb861c8def_RECIFE1.webp\", \"hotels/690cb861c94e3_Recife2.webp\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.portoaltoresort.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(19,'Porto 2 Life Resort','Ipojuca, PE','Ipojuca','PE','Praia de Muro Alto / Porto de Galinhas','55900-000','(81) 3552-3000','reservas@porto2life.com.br','Resort na região de Porto de Galinhas, uma das praias mais famosas do Brasil.','[\"hotels/690cb861cb0c5_Recife.jpg\", \"hotels/690cb861cb89d_RECIFE1.webp\", \"hotels/690cb861cbf5c_Recife2.webp\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.porto2life.com.br',4.80,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(20,'Vila Atlântica / Vila Atlântida','Luís Correia, PI','Luís Correia','PI','Rua José de Freitas, região da Praia de Atalaia','64220-000','(86) 3263-4000','contato@vilatlantica.com.br','Complexo de flats na Praia de Atalaia, Luís Correia.','[\"hotels/690cb861cd59a_Piauí.jpg\", \"hotels/690cb861cdc37_Lençóis Maranhenses.jpg\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.vilatlantica.com.br',4.30,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(21,'Bello Mare Hotel','Natal, RN','Natal','RN','Av. Engenheiro Roberto Freire, 4917 – Ponta Negra','59090-000','(84) 3209-5000','reservas@bellomare.com.br','Hotel em Ponta Negra, uma das praias mais famosas de Natal.','[\"hotels/690cb861cf1a7_Bahia.jpg\", \"hotels/690cb861cf903_Bahia1.jpg\", \"hotels/690cb861d0036_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.redeandradebellomare.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(22,'Rio das Garças Ecoresort','São José de Mipibu, RN','São José de Mipibu','RN','Região de São José de Mipibu','59162-000','(84) 3218-6000','contato@riodasgarcas.com.br','Ecoresort com foco em sustentabilidade e contato com a natureza.','[\"hotels/690cb861d158a_Bahia.jpg\", \"hotels/690cb861d1be2_Bahia1.jpg\", \"hotels/690cb861d22eb_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.riodasgarcasecoresort.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(23,'Vila do Mar','Natal, RN','Natal','RN','Av. Praia de Ponta Negra, s/n','59090-000','(84) 3209-7000','reservas@viladomar.com.br','Resort all inclusive em Natal com estrutura completa de lazer.','[\"hotels/690cb861d3bfd_Bahia.jpg\", \"hotels/690cb861d425a_Bahia1.jpg\", \"hotels/690cb861d48f7_Muro Alto.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.viladomar.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(24,'Encontro das Águas','Caldas Novas, GO','Caldas Novas','GO','Av. Caminho do Lago c/ Alameda Chico Batata, Jardim Metodista','75690-000','(64) 3453-8000','reservas@encontrodasaguas.com.br','Resort em Caldas Novas com águas termais e estrutura completa.','[\"hotels/690cb861d5ece_Goiás.jpg\", \"hotels/690cb861d6713_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861d70a0_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.encontro-das-aguas.caldasnovashotels.com',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(25,'Evian Thermas Residence','Caldas Novas, GO','Caldas Novas','GO','Rua Presidente Kennedy, 194-236','75690-000','(64) 3453-9000','contato@evianthermas.com.br','Residence com águas termais em Caldas Novas.','[\"hotels/690cb861d86a6_Goiás.jpg\", \"hotels/690cb861d8d88_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861d93e6_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.evianthermas.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(26,'Privê Ilhas do Lago / Ilhas do Lago Eco Resort','Caldas Novas, GO','Caldas Novas','GO','Av. Caminho do Lago, s/n','75690-000','(64) 3454-1000','reservas@ilhasdolago.com.br','Eco resort em Caldas Novas com foco em sustentabilidade.','[\"hotels/690cb861da654_Goiás.jpg\", \"hotels/690cb861dae6f_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861db646_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.ilhasdolagocaldasnovas.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(27,'Lagoa EcoTower / Lagoa Eco Towers','Caldas Novas, GO','Caldas Novas','GO','Próximo à Av. Lagoa Quente','75690-000','(64) 3454-2000','contato@lagoaecotower.com.br','Torre residencial com vista para a Lagoa Quente.','[\"hotels/690cb861dc90b_Goiás.jpg\", \"hotels/690cb861dcfa1_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861dd5b9_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.lagoaecotower.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(28,'Mandala dos Pyrineus','Pirenópolis, GO','Pirenópolis','GO','Estrada para Pirenópolis, s/n','72980-000','(62) 3331-3000','reservas@mandalapyrineus.com.br','Eco village em Pirenópolis com foco em bem-estar e natureza.','[\"hotels/690cb861de7c9_Goiás.jpg\", \"hotels/690cb861def96_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861df698_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.wamexperience.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(29,'Marina Flat & Náutica / Marina Flat','Caldas Novas, GO','Caldas Novas','GO','Avenida Caminho do Lago / margens do Lago Corumbá','75690-000','(64) 3454-3000','contato@marinaflat.com.br','Flat náutico às margens do Lago Corumbá.','[\"hotels/690cb861e0946_Goiás.jpg\", \"hotels/690cb861e0fe1_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861e161a_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.marinaflat.com.br',4.30,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(30,'Alta Vista Thermas Resort','Caldas Novas, GO','Caldas Novas','GO','Rua 18, Qd 68, Lt 1-R – Bairro Turista II','75690-000','(64) 3454-4000','reservas@altavista.com.br','Resort com águas termais e vista panorâmica.','[\"hotels/690cb861e290e_Goiás.jpg\", \"hotels/690cb861e320c_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861e3aaf_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.altavistathermasresort.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(31,'Quinta Santa Bárbara Ecoresort','Pirenópolis, GO','Pirenópolis','GO','Rua do Bonfim, nº 1','72980-000','(62) 3331-4000','contato@quintasantabarbara.com.br','Ecoresort em Pirenópolis com arquitetura colonial.','[\"hotels/690cb861e50a0_Goiás.jpg\", \"hotels/690cb861e5719_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861e5d1b_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.quintasantabarbara.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(32,'Serra Madre Hotel','Rio Quente, GO','Rio Quente','GO','Av. Principal, s/n','75695-000','(64) 3455-5000','reservas@serramadre.com.br','Hotel em Rio Quente com acesso às águas termais.','[\"hotels/690cb861e701e_Goiás.jpg\", \"hotels/690cb861e778a_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861e7dc8_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.serramadre.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(33,'Atrium Thermas Residence Service','Caldas Novas, GO','Caldas Novas','GO','Avenida Coronel Cirilo Lopes de Morais, 33','75690-000','(64) 3454-6000','contato@atriumthermas.com.br','Residence service com águas termais em Caldas Novas.','[\"hotels/690cb861e9011_Goiás.jpg\", \"hotels/690cb861e966a_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861e9c75_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.prive-atrium-thermas-residence-service-caldas-novas.caldasnovashotels.com',4.30,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(34,'Best Western Suítes Le Jardin','Caldas Novas, GO','Caldas Novas','GO','Rua Machado de Assis, nº 555 — Bairro Bandeirantes','75690-000','(64) 3454-7000','reservas@lejardin.com.br','Hotel da rede Best Western em Caldas Novas.','[\"hotels/690cb861eafae_Goiás.jpg\", \"hotels/690cb861eb859_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861ebebf_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.bestwestern.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(35,'Golden Dolphin Grand Hotel','Caldas Novas, GO','Caldas Novas','GO','Av. Elias Bufaiçal, Gleba 01','75690-000','(64) 3454-8000','reservas@goldendolphin.com.br','Grand hotel em Caldas Novas com estrutura completa.','[\"hotels/690cb861ed2d7_Goiás.jpg\", \"hotels/690cb861ed9b5_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861ee0cd_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.goldendolphin.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(36,'Golden Dolphin Supreme','Caldas Novas, GO','Caldas Novas','GO','Av. Principal, s/n','75690-000','(64) 3454-9000','contato@goldendolphinsupreme.com.br','Hotel da rede Golden Dolphin em Caldas Novas.','[\"hotels/690cb861ef77d_Goiás.jpg\", \"hotels/690cb861f0029_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861f087d_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.goldendolphinsupreme.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(37,'Hotspring B3 Hotels','Caldas Novas, GO','Caldas Novas','GO','Rua Dona Francisca Alla Cunha, 152 – Bairro Turista I','75690-000','(64) 3455-1000','reservas@hotsprings.com.br','Hotel com águas termais em Caldas Novas.','[\"hotels/690cb861f1e7c_Goiás.jpg\", \"hotels/690cb861f25ae_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb861f2cbc_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.hotsprings.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:53'),(38,'Jardins da Lagoa / Jardins da Lagoa CondoResort','Caldas Novas, GO','Caldas Novas','GO','Av. Lagoa Quente, 5','75690-000','(64) 3455-2000','contato@jardinsdalagoa.com.br','CondoResort às margens da Lagoa Quente.','[\"hotels/690cb862001c1_Goiás.jpg\", \"hotels/690cb86200865_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb86200e70_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.lagoaparquesehoteis.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(39,'Privê Praias do Lago Ecoresort','Caldas Novas, GO','Caldas Novas','GO','Av. Caminho do Lago, Gleba 04 – Jardim Interlago','75690-000','(64) 3455-3000','reservas@praiado lago.com.br','Ecoresort às margens do lago em Caldas Novas.','[\"hotels/690cb86202251_Goiás.jpg\", \"hotels/690cb862028dd_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb86202eb7_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.wamhoteis.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(40,'Toulon Park Residence Resort','Caldas Novas, GO','Caldas Novas','GO','Av. das Nações, Quadra 19, Lote 1R','75690-000','(64) 3455-4000','contato@toulonpark.com.br','Residence resort em Caldas Novas.','[\"hotels/690cb86204569_Goiás.jpg\", \"hotels/690cb86204c7a_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb86205213_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.toulonpark.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(41,'Prive Riviera Park Hotel','Caldas Novas, GO','Caldas Novas','GO','Av. Deputado Jamel Cecílio, nº 2690','75690-000','(64) 3455-6000','reservas@rivierapark.com.br','Hotel da rede WAM em Caldas Novas.','[\"hotels/690cb862063a1_Goiás.jpg\", \"hotels/690cb862069d3_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/690cb86206f5c_Caldas Novas.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.wamhoteis.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(42,'China Park','Domingos Martins, ES','Domingos Martins','ES','Região de Pedra Azul','29260-000','(27) 3268-7000','contato@chinapark.com.br','Eco resort nas Montanhas Capixabas, próximo à Pedra Azul.','[\"hotels/690cb86208589_Rio de Janeiro.jpg\", \"hotels/690cb86208f81_Búzios.jpg\", \"hotels/690cb8620956b_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.chinaparkecoresort.com.br',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(43,'Vista Azul Hotel','Domingos Martins, ES','Domingos Martins','ES','Pedra Azul','29260-000','(27) 3268-8000','reservas@vistaazul.com.br','Hotel nas Montanhas Capixabas com vista para a Pedra Azul.','[\"hotels/690cb8620a616_Rio de Janeiro.jpg\", \"hotels/690cb8620b0cc_Búzios.jpg\", \"hotels/690cb8620b869_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.vistaazulhotel.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(44,'Náutico Clube Fronteira','Fronteira, MG','Fronteira','MG','Rod. BR-153, Km 231','38230-000','(34) 3351-9000','contato@nauticoclubefronteira.com.br','Clube náutico em Fronteira, Minas Gerais.','[\"hotels/690cb8620e814_Goiás.jpg\", \"hotels/690cb8620eea7_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.nauticoclubefronteira.com.br',4.30,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(45,'Pousada Terramare','Guapé, MG','Guapé','MG','Condomínio Terramare Península','37177-000','(35) 3551-1000','reservas@terramare.com.br','Pousada no Condomínio Terramare Península.','[\"hotels/690cb8621063c_Goiás.jpg\", \"hotels/690cb86210e19_Goiás. Chapada dos Veadeiros.jpg\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.pousadaterramare.com.br',4.40,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(46,'Búzios Beach Resort','Armação dos Búzios, RJ','Armação dos Búzios','RJ','Avenida dos Tucuns – Praia de Tucuns','28950-000','(22) 2623-2000','reservas@buziosresort.com.br','Resort na Praia de Tucuns, Búzios.','[\"hotels/690cb862120d0_Rio de Janeiro.jpg\", \"hotels/690cb86212a90_Búzios.jpg\", \"hotels/690cb86213079_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.buziosresort.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(47,'Hotel Nacional','Rio de Janeiro, RJ','Rio de Janeiro','RJ','Av. Niemeyer, 769 – São Conrado','22450-220','(21) 2103-3000','reservas@hotelnacionalriodejaneiro.com','Hotel em São Conrado com vista para o mar.','[\"hotels/690cb8621434f_Rio de Janeiro.jpg\", \"hotels/690cb86214d5f_Búzios.jpg\", \"hotels/690cb86215351_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.hotelnacionalriodejaneiro.com',4.80,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(48,'Livyd Angra dos Reis','Angra dos Reis, RJ','Angra dos Reis','RJ','Rodovia BR-101, Km 533 – Mambucaba','23900-000','(24) 3365-4000','contato@livyd.com.br','Resort em Angra dos Reis, região entre Angra e Paraty.','[\"hotels/690cb86216322_Rio de Janeiro.jpg\", \"hotels/690cb86216c65_Búzios.jpg\", \"hotels/690cb862172ee_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.livyd.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(49,'Aldeia das Águas Park Resort Quartier','Barra do Piraí, RJ','Barra do Piraí','RJ','Complexo Aldeia das Águas','27100-000','(24) 2443-5000','reservas@aldeiadasaguas.com.br','Complexo resort com parque aquático em Barra do Piraí.','[\"hotels/690cb862186a8_Rio de Janeiro.jpg\", \"hotels/690cb86218f63_Búzios.jpg\", \"hotels/690cb8621965e_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.aldeiadasaguas.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(50,'Angra Beach Hotel','Angra dos Reis, RJ','Angra dos Reis','RJ','Rua José Watanabe, 111 – Parque das Palmeiras','23906-520','(24) 3365-6000','contato@angrabeach.com','Hotel à beira-mar em Angra dos Reis.','[\"hotels/690cb8621a70c_Rio de Janeiro.jpg\", \"hotels/690cb8621b091_Búzios.jpg\", \"hotels/690cb8621b74a_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.angrabeach.com',4.50,4,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(51,'Plaza Barra First / Plaza Rio Residences Barra First','Rio de Janeiro, RJ','Rio de Janeiro','RJ','Av. das Américas, 7897 — Barra da Tijuca','22793-081','(21) 2103-7000','reservas@plazabarra.com.br','Hotel da rede Plaza na Barra da Tijuca.','[\"hotels/690cb8621ce49_Rio de Janeiro.jpg\", \"hotels/690cb8621d7b5_Búzios.jpg\", \"hotels/690cb8621de7c_Búzios1.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.redeplaza.com.br',4.60,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(52,'Bendito Cacao Resort & Spa','Campos do Jordão, SP','Campos do Jordão','SP','Av. Principal, s/n','12460-000','(12) 3668-8000','reservas@benditocacao.com.br','Resort & Spa em Campos do Jordão (anteriormente Blue Mountain Resort & Spa).','[\"hotels/690cb8621f17a_São Paolo.jpg\", \"hotels/690cb8621f98e_São Paolo1.webp\", \"hotels/Kt45hO67hZ8OFvr3ryDhivgjtdl2wsvxeX50X9bJ.jpg\"]','[\"wifi\", \"gym\", \"business_center\", \"restaurant\", \"bar\", \"spa\", \"concierge\", \"pool\", \"parking\"]','https://www.benditocacao.com.br',4.70,5,1,1,NULL,'2025-11-06 17:31:42','2025-11-06 18:01:54'),(53,'Hotel Demo 1','São Paulo, SP',NULL,NULL,'Endereço de teste - São Paulo, SP',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,1,1,NULL,'2025-11-22 03:39:03','2025-11-22 03:39:03'),(54,'Hotel Demo 2','São Paulo, SP',NULL,NULL,'Endereço de teste - São Paulo, SP',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,1,1,NULL,'2025-11-22 03:39:03','2025-11-22 03:39:03');
/*!40000 ALTER TABLE `hotels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kyc_validations`
--

DROP TABLE IF EXISTS `kyc_validations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kyc_validations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `document_type` enum('rg','cnh','passport') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rg',
  `document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_status` enum('pending','processing','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `ocr_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `validation_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `validated_at` timestamp NULL DEFAULT NULL,
  `validated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kyc_validations_validated_by_foreign` (`validated_by`),
  KEY `kyc_validations_user_id_document_type_index` (`user_id`,`document_type`),
  KEY `kyc_validations_validation_status_index` (`validation_status`),
  CONSTRAINT `kyc_validations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kyc_validations_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kyc_validations_chk_1` CHECK (json_valid(`ocr_data`)),
  CONSTRAINT `kyc_validations_chk_2` CHECK (json_valid(`validation_metadata`))
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kyc_validations`
--

LOCK TABLES `kyc_validations` WRITE;
/*!40000 ALTER TABLE `kyc_validations` DISABLE KEYS */;
INSERT INTO `kyc_validations` VALUES (21,43,'rg',NULL,'documents/20260205020105_FqpwlWSQ.jpg','pending',NULL,NULL,NULL,NULL,NULL,'2026-02-05 05:01:05','2026-02-05 05:01:05');
/*!40000 ALTER TABLE `kyc_validations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_09_03_223155_create_quotas_table',1),(5,'2025_09_03_223157_create_quota_transactions_table',1),(6,'2025_09_03_223158_create_digital_contracts_table',1),(7,'2025_09_03_223200_create_notifications_table',1),(8,'2025_09_03_223203_create_user_profiles_table',1),(9,'2025_09_10_213358_add_role_to_users_table',2),(10,'2025_09_10_213400_create_hotels_table',2),(11,'2025_09_10_213402_create_admin_logs_table',2),(12,'2025_09_12_194929_add_ingress_date_to_users_table',3),(13,'2025_09_12_194942_update_user_profiles_for_kyc',3),(14,'2025_09_12_194955_create_kyc_validations_table',3),(15,'2025_09_12_200548_create_rental_offers_table',4),(17,'2025_09_12_200604_create_auctions_table',5),(18,'2025_09_12_201340_update_rental_offers_for_types',6),(19,'2025_09_12_201403_create_advanced_auctions_table',6),(20,'2025_09_12_201419_create_auction_limits_table',6),(21,'2025_09_12_201509_update_quotas_table_for_management',6),(22,'2025_09_12_203515_add_functioning_status_to_hotels_table',7),(23,'2025_09_12_203545_add_discount_system_to_rental_offers',8),(24,'2025_09_12_214501_create_hospitality_authorizations_table',9),(25,'2025_09_12_214537_create_wishlist_requests_table',9),(26,'2025_09_12_220633_add_quota_management_fields_to_user_profiles_table',10),(28,'2025_09_21_191906_update_user_profiles_address_fields',11),(29,'2025_09_21_230515_add_gestor_fields_to_user_profiles_table',12),(31,'2025_09_21_232625_add_gestor_allowed_uses_to_user_profiles_table',13),(32,'2025_09_24_221431_add_gestor_quota_people_to_user_profiles_table',13),(33,'2025_09_24_222201_add_gestor_quota_details_to_user_profiles_table',14),(34,'2025_09_24_222652_add_gestor_quota_beds_to_user_profiles_table',15),(35,'2025_09_24_223014_add_gestor_quota_amenities_to_user_profiles_table',16),(36,'2025_09_24_225242_update_gestor_quota_bed_fields_in_user_profiles_table',17),(38,'2025_10_01_215337_add_owner_quota_fields_to_user_profiles_table',18),(39,'2025_10_24_205539_add_fields_to_hotels_table',19),(40,'2025_10_30_174800_add_images_to_hotels_table',20),(41,'2025_11_06_134402_add_allowed_uses_to_quotas_table',21),(42,'2025_11_21_215215_improve_rental_offers_for_advanced_features',22),(43,'2025_11_21_215247_improve_wishlist_requests_for_no_offer_system',22),(44,'2025_11_21_215811_create_payment_transactions_table',22),(45,'2025_11_21_215816_create_exchange_offers_table',22),(46,'2025_11_21_215822_create_sale_offers_table',22),(47,'2025_11_21_215827_create_purchase_requests_table',22),(48,'2025_11_21_215832_create_educational_contents_table',22),(49,'2025_11_21_215834_create_educational_videos_table',22),(50,'2025_11_27_210147_create_favorite_lists_table',23),(51,'2025_11_27_210149_create_wishlist_searches_table',23),(52,'2025_12_26_135630_create_success_fees_table',24),(53,'2025_01_15_000001_add_negotiation_fields_to_quotas_table',25),(54,'2025_01_15_000002_add_negotiation_fields_to_quota_transactions_table',25),(55,'2026_01_19_171638_add_owner_allowed_uses_to_user_profiles_table',25),(56,'2026_01_19_171859_add_quota_details_to_user_profiles_table',26),(57,'2026_01_20_210350_add_quota_type_fields_to_user_profiles_and_quotas_tables',27),(58,'2026_01_21_224159_add_payment_status_to_quota_transactions_table',28),(59,'2026_01_21_224607_add_negotiating_status_to_quota_transactions_table',29),(60,'2026_01_21_225615_add_hospitality_authorization_term_to_user_profiles_table',30),(61,'2026_01_22_204831_add_state_type_to_favorite_lists_table',31),(62,'2026_01_22_205819_add_transaction_type_to_favorite_lists_table',32),(63,'2026_01_22_210436_fix_add_transaction_type_to_favorite_lists_table',33),(64,'2026_01_30_142358_add_asaas_fields_to_payment_transactions_table',34),(65,'2026_02_02_225655_create_personal_access_tokens_table',35);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `channel` enum('email','whatsapp','in_app') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_app',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_chk_1` CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('contato@tauanaraujo.com.br','$2y$12$vW10v1G5NCoou9b2hMojO.FcmJDB51KfL6Uzt9ubc0LQgeQz1E4pm','2026-01-30 19:54:35');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `payment_method` enum('credit_card','debit_card','pix','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pix',
  `amount` decimal(10,2) NOT NULL,
  `fees` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asaas_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asaas_webhook_data` json DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `authorization_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at_hour` tinyint(1) NOT NULL DEFAULT '0',
  `payment_due_at` timestamp NULL DEFAULT NULL,
  `payment_completed_at` timestamp NULL DEFAULT NULL,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `block_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_transactions_transaction_id_foreign` (`transaction_id`),
  KEY `payment_transactions_status_payment_due_at_index` (`status`,`payment_due_at`),
  KEY `payment_transactions_user_id_status_index` (`user_id`,`status`),
  KEY `payment_transactions_asaas_payment_id_index` (`asaas_payment_id`),
  CONSTRAINT `payment_transactions_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `quota_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_transactions_chk_1` CHECK (json_valid(`payment_details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_transactions`
--

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `hotel_id` bigint unsigned DEFAULT NULL,
  `weeks` int DEFAULT NULL,
  `month` int DEFAULT NULL,
  `period_type` enum('fixo','flexivel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixo',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_range_min` decimal(10,2) DEFAULT NULL,
  `price_range_max` decimal(10,2) DEFAULT NULL,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','matched','purchased','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `delegated_to_admin` tinyint(1) NOT NULL DEFAULT '0',
  `max_price` decimal(10,2) DEFAULT NULL,
  `purchase_fee_percentage` decimal(5,2) NOT NULL DEFAULT '10.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_requests_hotel_id_foreign` (`hotel_id`),
  KEY `purchase_requests_status_delegated_to_admin_index` (`status`,`delegated_to_admin`),
  KEY `purchase_requests_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `purchase_requests_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requests`
--

LOCK TABLES `purchase_requests` WRITE;
/*!40000 ALTER TABLE `purchase_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quota_transactions`
--

DROP TABLE IF EXISTS `quota_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quota_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quota_id` bigint unsigned NOT NULL,
  `renter_id` bigint unsigned NOT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `transaction_type` enum('rental','exchange') COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `owner_amount` decimal(10,2) DEFAULT NULL,
  `platform_fee` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','contract_signed','negotiating','payment_pending','document_pending','payment_completed','completed','cancelled','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `negotiation_started_at` timestamp NULL DEFAULT NULL,
  `negotiation_deadline` timestamp NULL DEFAULT NULL,
  `document_upload_deadline` timestamp NULL DEFAULT NULL,
  `document_uploaded_at` timestamp NULL DEFAULT NULL,
  `document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_deadline_hours` int NOT NULL DEFAULT '24',
  `document_deadline_hours` int NOT NULL DEFAULT '24',
  `contract_signed_at` timestamp NULL DEFAULT NULL,
  `payment_due_at` timestamp NULL DEFAULT NULL,
  `payment_completed_at` timestamp NULL DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quota_transactions_quota_id_foreign` (`quota_id`),
  KEY `quota_transactions_renter_id_foreign` (`renter_id`),
  KEY `quota_transactions_owner_id_foreign` (`owner_id`),
  CONSTRAINT `quota_transactions_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quota_transactions_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quota_transactions_renter_id_foreign` FOREIGN KEY (`renter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quota_transactions_chk_1` CHECK (json_valid(`payment_details`))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quota_transactions`
--

LOCK TABLES `quota_transactions` WRITE;
/*!40000 ALTER TABLE `quota_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `quota_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotas`
--

DROP TABLE IF EXISTS `quotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `hotel_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weeks` int NOT NULL DEFAULT '1',
  `number_of_rooms` int NOT NULL DEFAULT '1',
  `seasonality` enum('low','medium','high','peak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `payment_status` enum('paid','unpaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `is_owner` tinyint(1) NOT NULL DEFAULT '1',
  `authorizations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `quota_status` enum('active','inactive','suspended','transferred') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `allowed_uses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `transferred_at` timestamp NULL DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `number_of_guests` int NOT NULL,
  `rental_price` decimal(10,2) DEFAULT NULL,
  `price_min` decimal(10,2) DEFAULT NULL,
  `price_max` decimal(10,2) DEFAULT NULL,
  `is_exchange` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_exchange` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_sale` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_diaria_exchange` tinyint(1) NOT NULL DEFAULT '0',
  `observations` text COLLATE utf8mb4_unicode_ci,
  `contract_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('available','negotiating','rented','exchanged','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `negotiation_deadline` timestamp NULL DEFAULT NULL,
  `current_transaction_id` bigint unsigned DEFAULT NULL,
  `is_fractioned` tinyint(1) NOT NULL DEFAULT '0',
  `quota_type` enum('fixa','flexivel','fix_flexivel') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fraction_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `previous_owner_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotas_user_id_foreign` (`user_id`),
  KEY `quotas_previous_owner_id_foreign` (`previous_owner_id`),
  KEY `quotas_current_transaction_id_foreign` (`current_transaction_id`),
  CONSTRAINT `quotas_current_transaction_id_foreign` FOREIGN KEY (`current_transaction_id`) REFERENCES `quota_transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotas_previous_owner_id_foreign` FOREIGN KEY (`previous_owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotas_chk_1` CHECK (json_valid(`authorizations`)),
  CONSTRAINT `quotas_chk_2` CHECK (json_valid(`allowed_uses`)),
  CONSTRAINT `quotas_chk_3` CHECK (json_valid(`fraction_details`))
) ENGINE=InnoDB AUTO_INCREMENT=435 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotas`
--

LOCK TABLES `quotas` WRITE;
/*!40000 ALTER TABLE `quotas` DISABLE KEYS */;
INSERT INTO `quotas` VALUES (434,43,'China Park',2,1,'medium','paid',1,NULL,0,NULL,'active','[\"rent\",\"exchange\",\"sell\",\"buy\"]',NULL,'Domingos Martins, ES','2026-09-17','2026-09-24',3,NULL,NULL,NULL,0,0,0,0,'','quota_contracts/20260205020105_8x2dGy95.pdf','available',NULL,NULL,1,NULL,'{\"fraction_type\":null,\"fraction_weeks\":{\"1\":{\"periods\":{\"1\":{\"enabled\":\"1\",\"action\":\"rent_exchange\",\"start\":\"2026-09-17\",\"end\":\"2026-09-19\"},\"2\":{\"enabled\":\"1\",\"action\":\"rent_exchange\",\"start\":\"2026-09-19\",\"end\":\"2026-09-24\"}}},\"2\":{\"periods\":{\"1\":{\"enabled\":\"1\",\"action\":\"rent_exchange\",\"start\":\"2026-08-15\",\"end\":\"2026-08-20\"},\"2\":{\"enabled\":\"1\",\"action\":\"rent_exchange\",\"start\":\"2026-08-20\",\"end\":\"2026-08-22\"}}}}}','2026-02-05 05:01:05','2026-02-05 05:01:05',NULL);
/*!40000 ALTER TABLE `quotas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rental_offers`
--

DROP TABLE IF EXISTS `rental_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rental_offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `quota_id` bigint unsigned NOT NULL,
  `hotel_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offer_type` enum('rent','exchange','sell','buy') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rent',
  `description` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `period_type` enum('exact','flexible') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exact',
  `flexible_weeks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_flexible_period` tinyint(1) NOT NULL DEFAULT '0',
  `flexible_dates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `min_days` int NOT NULL DEFAULT '2',
  `max_days` int NOT NULL DEFAULT '7',
  `sale_minimum_price` decimal(10,2) DEFAULT NULL,
  `acceptable_price` decimal(10,2) DEFAULT NULL,
  `desired_price` decimal(10,2) DEFAULT NULL,
  `auction_fee_percentage` decimal(5,2) NOT NULL DEFAULT '10.00',
  `exchange_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `max_exchange_options` int NOT NULL DEFAULT '3',
  `exchange_valid_until` timestamp NULL DEFAULT NULL,
  `delegate_to_manager` tinyint(1) NOT NULL DEFAULT '0',
  `delegation_fee` decimal(10,2) DEFAULT NULL,
  `search_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `include_partial_matches` tinyint(1) NOT NULL DEFAULT '1',
  `number_of_days` int NOT NULL,
  `number_of_people` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_min` decimal(10,2) DEFAULT NULL,
  `price_max` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','negotiated','cancelled','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_fractioned` tinyint(1) NOT NULL DEFAULT '0',
  `fraction_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_auction` tinyint(1) NOT NULL DEFAULT '0',
  `super_desconto_applied` tinyint(1) NOT NULL DEFAULT '0',
  `super_desconto_applied_at` timestamp NULL DEFAULT NULL,
  `super_desconto_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `mega_oferta_applied` tinyint(1) NOT NULL DEFAULT '0',
  `mega_oferta_applied_at` timestamp NULL DEFAULT NULL,
  `mega_oferta_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `app_commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_penalized` tinyint(1) NOT NULL DEFAULT '0',
  `penalty_until` timestamp NULL DEFAULT NULL,
  `penalty_reason` text COLLATE utf8mb4_unicode_ci,
  `minimum_price` decimal(10,2) DEFAULT NULL,
  `auction_end_time` timestamp NULL DEFAULT NULL,
  `auction_start_time` timestamp NULL DEFAULT NULL,
  `auction_duration_minutes` int DEFAULT NULL,
  `auction_day` date DEFAULT NULL,
  `auction_start_hour` time DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `is_batch_offer` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_exchange` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_sale` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_diaria_exchange` tinyint(1) NOT NULL DEFAULT '0',
  `days_until_start` int DEFAULT NULL,
  `auto_discount_applied` tinyint(1) NOT NULL DEFAULT '0',
  `auto_discount_percentage` decimal(5,2) DEFAULT NULL,
  `auto_discount_applied_at` timestamp NULL DEFAULT NULL,
  `batch_quota_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `views_count` int NOT NULL DEFAULT '0',
  `favorites_count` int NOT NULL DEFAULT '0',
  `negotiated_at` timestamp NULL DEFAULT NULL,
  `rented_at` timestamp NULL DEFAULT NULL,
  `moved_to_metrics` tinyint(1) NOT NULL DEFAULT '0',
  `metrics_type` enum('rented','exchanged','sold') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `negotiated_with` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_offers_user_id_foreign` (`user_id`),
  KEY `rental_offers_quota_id_foreign` (`quota_id`),
  KEY `rental_offers_hotel_id_foreign` (`hotel_id`),
  KEY `rental_offers_negotiated_with_foreign` (`negotiated_with`),
  KEY `rental_offers_status_start_date_index` (`status`,`start_date`),
  KEY `rental_offers_city_state_index` (`city`,`state`),
  KEY `rental_offers_price_status_index` (`price`,`status`),
  KEY `rental_offers_is_auction_auction_end_time_index` (`is_auction`,`auction_end_time`),
  CONSTRAINT `rental_offers_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rental_offers_negotiated_with_foreign` FOREIGN KEY (`negotiated_with`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rental_offers_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rental_offers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rental_offers_chk_1` CHECK (json_valid(`flexible_weeks`)),
  CONSTRAINT `rental_offers_chk_2` CHECK (json_valid(`flexible_dates`)),
  CONSTRAINT `rental_offers_chk_3` CHECK (json_valid(`exchange_options`)),
  CONSTRAINT `rental_offers_chk_4` CHECK (json_valid(`search_criteria`)),
  CONSTRAINT `rental_offers_chk_5` CHECK (json_valid(`fraction_details`)),
  CONSTRAINT `rental_offers_chk_6` CHECK (json_valid(`photos`)),
  CONSTRAINT `rental_offers_chk_7` CHECK (json_valid(`batch_quota_ids`))
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rental_offers`
--

LOCK TABLES `rental_offers` WRITE;
/*!40000 ALTER TABLE `rental_offers` DISABLE KEYS */;
/*!40000 ALTER TABLE `rental_offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_offers`
--

DROP TABLE IF EXISTS `sale_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `quota_id` bigint unsigned DEFAULT NULL,
  `hotel_id` bigint unsigned NOT NULL,
  `weeks` int NOT NULL DEFAULT '1',
  `number_of_rooms` int NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_price` decimal(10,2) NOT NULL,
  `acceptable_price` decimal(10,2) NOT NULL,
  `desired_price` decimal(10,2) NOT NULL,
  `observations_by_price` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` enum('pending','negotiating','sold','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `negotiation_status` enum('direct','admin','auction') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `admin_id` bigint unsigned DEFAULT NULL,
  `auction_id` bigint unsigned DEFAULT NULL,
  `app_commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_offers_quota_id_foreign` (`quota_id`),
  KEY `sale_offers_hotel_id_foreign` (`hotel_id`),
  KEY `sale_offers_admin_id_foreign` (`admin_id`),
  KEY `sale_offers_auction_id_foreign` (`auction_id`),
  KEY `sale_offers_status_negotiation_status_index` (`status`,`negotiation_status`),
  KEY `sale_offers_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `sale_offers_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_offers_auction_id_foreign` FOREIGN KEY (`auction_id`) REFERENCES `auctions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_offers_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_offers_quota_id_foreign` FOREIGN KEY (`quota_id`) REFERENCES `quotas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_offers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_offers_chk_1` CHECK (json_valid(`observations_by_price`))
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_offers`
--

LOCK TABLES `sale_offers` WRITE;
/*!40000 ALTER TABLE `sale_offers` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('5EqYzjNsLJzPrkV40kAPrMf8ecYQ12egNieHTcIn',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZkdVTWxoZmdYdTc5Nzk3cFBKcklnODJkcXRmaFJrQXFFZk1CZzFPOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3QvY290YWJyYXNpbGlzL3B1YmxpYy9yZWdpc3RlciI7fX0=',1769816633),('afykCjEg10LD5My3Xl3hEiOCUbYa0aj7pO9fsSDh',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQUtuM21vMmRlOWV6N1B1QW51MjJTcnQzTGJMbDVoMU9YMU56N2pKViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3QvY290YWJyYXNpbGlzL3B1YmxpYy9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1769860186),('hTfXOGe41Ms1VuOulA1RPGDwLBTTrvG3F9sYsQt9',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidW9xZGtjb0tIdVRvU1dNNWNjZWNJNFhYZml0UmlzY1FJbTlnSG9iTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3QvY290YWJyYXNpbGlzL3B1YmxpYy9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1770054715),('Qbe4nSwM7XUzJdX9T6FQOpJrhWhWUVUrkQdbsgne',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ1NKTzhzVkhKUm9rWVdWRHR4NnRlb1g4WEtBRXY1Z0liRjVYa2EwViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1770255140),('s5RKljJEEJJHcb1S8MIhDFl7JfsfhtKeiyDSqWEZ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiN090OExkZGt2bnBqNXRXNElhVGx0REFiN1c4RTk3d1ZVazk0eDlzQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1770214403),('SqAwWoTnNFRfLtlOEMUIIPL9FYN7dQoWf4LcMFg3',43,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicktmaHpybGRscWZCYTF2aFRtd25CS1EzNUVEcWVEcTJWNWxWZXZENSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RyYXRpb24vc3VjY2VzcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQzO30=',1770256869);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `success_fees`
--

DROP TABLE IF EXISTS `success_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `success_fees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `profile_type` enum('curioso','inteligente','sabio') COLLATE utf8mb4_unicode_ci NOT NULL,
  `days` int NOT NULL COMMENT 'Número de dias do fracionamento',
  `fee_amount` decimal(10,2) NOT NULL COMMENT 'Valor da taxa de êxito em R$',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Se a taxa está ativa',
  `order` int NOT NULL DEFAULT '0' COMMENT 'Ordem de exibição',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descrição opcional da taxa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_profile_days` (`profile_type`,`days`),
  KEY `success_fees_profile_type_is_active_index` (`profile_type`,`is_active`),
  KEY `success_fees_profile_type_days_index` (`profile_type`,`days`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `success_fees`
--

LOCK TABLES `success_fees` WRITE;
/*!40000 ALTER TABLE `success_fees` DISABLE KEYS */;
INSERT INTO `success_fees` VALUES (1,'curioso',7,225.00,1,1,NULL,'2025-12-26 17:05:24','2025-12-26 14:26:10'),(2,'inteligente',3,90.00,1,2,NULL,'2025-12-26 17:05:48','2025-12-26 14:26:51'),(3,'inteligente',4,110.00,1,3,NULL,'2025-12-26 17:06:23','2025-12-26 14:27:17'),(4,'inteligente',7,125.00,1,4,'Para aluguel e Troca(em qualquer Hotel)','2025-12-26 17:06:58','2026-01-19 17:27:17'),(5,'sabio',2,65.00,1,5,NULL,'2025-12-26 17:07:22','2025-12-26 17:07:22'),(6,'sabio',3,90.00,1,6,NULL,'2025-12-26 17:07:37','2025-12-26 17:07:37'),(7,'sabio',4,105.00,1,7,NULL,'2025-12-26 17:07:54','2026-01-20 18:02:15'),(8,'sabio',5,150.00,1,8,NULL,'2025-12-26 17:08:16','2026-01-19 17:28:16'),(9,'sabio',7,190.00,1,9,NULL,'2025-12-26 17:08:28','2025-12-26 14:28:41');
/*!40000 ALTER TABLE `success_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `profile_type` enum('curioso','inteligente','sabio') COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `house_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnh_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rg_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quota_contract_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quota_paid_off` tinyint(1) NOT NULL DEFAULT '0',
  `hotel_operational` tinyint(1) NOT NULL DEFAULT '1',
  `allowed_uses` json DEFAULT NULL,
  `terms_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `digital_signature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `auctions_used` int NOT NULL DEFAULT '0',
  `search_views_used` int NOT NULL DEFAULT '0',
  `last_search_view` timestamp NULL DEFAULT NULL,
  `alert_cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `has_quota` tinyint(1) NOT NULL DEFAULT '0',
  `quota_status` enum('paid','unpaid') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quota_payment_deadline` date DEFAULT NULL,
  `is_quota_owner` tinyint(1) NOT NULL DEFAULT '1',
  `is_authorized_user` tinyint(1) NOT NULL DEFAULT '0',
  `authorization_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gov_br_signature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `gov_br_signature_at` timestamp NULL DEFAULT NULL,
  `kyc_completed` tinyint(1) NOT NULL DEFAULT '0',
  `kyc_completed_at` timestamp NULL DEFAULT NULL,
  `kyc_status` enum('pending','under_review','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `kyc_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `quota_contracts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `quota_details` json DEFAULT NULL,
  `owner_hotel_id` bigint unsigned DEFAULT NULL,
  `owner_quota_rooms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_people` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_double_bed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_single_bed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_sofa_bed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_jacuzzi` tinyint(1) DEFAULT NULL,
  `owner_quota_kitchen` tinyint(1) DEFAULT NULL,
  `owner_quota_parking` tinyint(1) DEFAULT NULL,
  `owner_quota_breakfast` tinyint(1) DEFAULT NULL,
  `owner_quota_seasonality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_observations` text COLLATE utf8mb4_unicode_ci,
  `hospitality_authorization_term_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_quota_type` enum('fixa','flexivel','fix_flexivel') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_hotel_operational` tinyint(1) DEFAULT NULL,
  `gestor_quota_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_payment_deadline` date DEFAULT NULL,
  `gestor_authorization_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_hotel_id` bigint unsigned DEFAULT NULL,
  `gestor_quota_people` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_double_bed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_single_bed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_sofa_bed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_rooms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_jacuzzi` tinyint(1) DEFAULT NULL,
  `gestor_quota_kitchen` tinyint(1) DEFAULT NULL,
  `gestor_quota_parking` tinyint(1) DEFAULT NULL,
  `gestor_quota_breakfast` tinyint(1) DEFAULT NULL,
  `gestor_quota_seasonality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_observations` text COLLATE utf8mb4_unicode_ci,
  `gestor_hospitality_authorization_term_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_quota_type` enum('fixa','flexivel','fix_flexivel') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestor_allowed_uses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_profiles_cpf_unique` (`cpf`),
  KEY `user_profiles_user_id_foreign` (`user_id`),
  KEY `user_profiles_gestor_hotel_id_foreign` (`gestor_hotel_id`),
  KEY `user_profiles_owner_hotel_id_foreign` (`owner_hotel_id`),
  CONSTRAINT `user_profiles_gestor_hotel_id_foreign` FOREIGN KEY (`gestor_hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_profiles_owner_hotel_id_foreign` FOREIGN KEY (`owner_hotel_id`) REFERENCES `hotels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_profiles_chk_1` CHECK (json_valid(`digital_signature`)),
  CONSTRAINT `user_profiles_chk_2` CHECK (json_valid(`alert_cities`)),
  CONSTRAINT `user_profiles_chk_3` CHECK (json_valid(`gov_br_signature`)),
  CONSTRAINT `user_profiles_chk_4` CHECK (json_valid(`quota_contracts`)),
  CONSTRAINT `user_profiles_chk_5` CHECK (json_valid(`gestor_allowed_uses`))
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
INSERT INTO `user_profiles` VALUES (38,43,'sabio','Tauan Araujo 30 07','456.091.158-41','(11) 98882-6339','07980-000','Rua José Ortiz Sanches','Recanto Feliz','Francisco Morato','SP','268',NULL,'documents/20260205020105_FqpwlWSQ.jpg','user_photos/20260205020105_67wdxnXE.jpg','quota_contracts/20260205020105_8x2dGy95.pdf',0,1,'[\"rent\", \"exchange\", \"sell\", \"buy\"]',1,'2026-02-05 05:01:05',NULL,0,0,NULL,NULL,1,'paid','2025-05-11',1,0,NULL,NULL,'2026-02-05 05:01:05',1,'2026-02-05 05:01:05','approved',NULL,'[\"quota_contracts\\/20260205020105_8x2dGy95.pdf\"]','{\"owner_rooms\": {\"1\": {\"suite\": 0, \"people\": 3, \"bunk_bed\": 0, \"sofa_bed\": 0, \"double_bed\": 2, \"single_bed\": 1}}, \"owner_weeks\": {\"1\": {\"year\": \"2026\", \"month\": \"09\", \"end_day\": \"24\", \"authorize\": \"yes\", \"start_day\": \"17\", \"proof_path\": \"period_proofs/20260205020105_jUG8ZHk1.pdf\"}, \"2\": {\"year\": \"2026\", \"month\": \"08\", \"end_day\": \"22\", \"authorize\": \"yes\", \"start_day\": \"15\", \"proof_path\": \"period_proofs/20260205020105_00XCBxN0.pdf\"}}, \"owner_quota_wifi\": 1, \"owner_rooms_data\": {\"total_people\": 3, \"total_bunk_bed\": 0, \"total_sofa_bed\": 0, \"total_double_bed\": 2, \"total_single_bed\": 1}, \"owner_quota_adega\": 1, \"owner_weeks_count\": 2, \"owner_quota_lareira\": 1, \"owner_quota_area_kids\": 1, \"owner_quota_vista_mar\": 1, \"owner_quota_area_trabalho\": 1}',42,'1','3','2','1','0','45',1,1,1,1,'media',NULL,'hospitality_authorizations/20260205020105_oVhzYqY4.png','fixa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-05 05:01:05','2026-02-05 05:01:05');
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','moderator','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `ingress_date` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `blocked_until` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (43,'Tauan Araujo 30 07','tauan.gt@gmail.com','user',0,NULL,'1996-07-11 03:00:00','$2y$12$mUV12Swm4TNoVYT.YZ.u1uUlbZsAa4AToxbdPPRjClGIn5uwnogOy',NULL,1,0,NULL,NULL,'2026-02-05 05:01:05','2026-02-05 05:01:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `video_comments`
--

DROP TABLE IF EXISTS `video_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `video_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `educational_video_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `video_comments_user_id_foreign` (`user_id`),
  KEY `video_comments_parent_id_foreign` (`parent_id`),
  KEY `video_comments_educational_video_id_is_approved_index` (`educational_video_id`,`is_approved`),
  CONSTRAINT `video_comments_educational_video_id_foreign` FOREIGN KEY (`educational_video_id`) REFERENCES `educational_videos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `video_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `video_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `video_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `video_comments`
--

LOCK TABLES `video_comments` WRITE;
/*!40000 ALTER TABLE `video_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `video_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `video_views`
--

DROP TABLE IF EXISTS `video_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `video_views` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `educational_video_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `viewed_at` timestamp NOT NULL,
  `duration_watched` int NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `video_views_educational_video_id_user_id_viewed_at_unique` (`educational_video_id`,`user_id`,`viewed_at`),
  KEY `video_views_user_id_foreign` (`user_id`),
  KEY `video_views_educational_video_id_user_id_index` (`educational_video_id`,`user_id`),
  CONSTRAINT `video_views_educational_video_id_foreign` FOREIGN KEY (`educational_video_id`) REFERENCES `educational_videos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `video_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `video_views`
--

LOCK TABLES `video_views` WRITE;
/*!40000 ALTER TABLE `video_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `video_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist_requests`
--

DROP TABLE IF EXISTS `wishlist_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `demand_observations` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desired_hotel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desired_start_date` date NOT NULL,
  `desired_end_date` date NOT NULL,
  `desired_month` int DEFAULT NULL,
  `desired_year` int DEFAULT NULL,
  `number_of_people` int NOT NULL,
  `number_of_rooms` int NOT NULL,
  `specific_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `max_price` decimal(10,2) DEFAULT NULL,
  `price_range_min` decimal(10,2) DEFAULT NULL,
  `price_range_max` decimal(10,2) DEFAULT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('active','fulfilled','cancelled','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `fulfilled_by_offer_id` bigint unsigned DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `alert_sent_to_owner` tinyint(1) NOT NULL DEFAULT '0',
  `alert_sent_to_admin` tinyint(1) NOT NULL DEFAULT '0',
  `alert_sent_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `matched_offer_id` bigint unsigned DEFAULT NULL,
  `matched_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wishlist_requests_user_id_foreign` (`user_id`),
  KEY `wishlist_requests_fulfilled_by_offer_id_foreign` (`fulfilled_by_offer_id`),
  KEY `wishlist_requests_matched_offer_id_foreign` (`matched_offer_id`),
  CONSTRAINT `wishlist_requests_fulfilled_by_offer_id_foreign` FOREIGN KEY (`fulfilled_by_offer_id`) REFERENCES `rental_offers` (`id`),
  CONSTRAINT `wishlist_requests_matched_offer_id_foreign` FOREIGN KEY (`matched_offer_id`) REFERENCES `rental_offers` (`id`),
  CONSTRAINT `wishlist_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_requests_chk_1` CHECK (json_valid(`specific_days`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist_requests`
--

LOCK TABLES `wishlist_requests` WRITE;
/*!40000 ALTER TABLE `wishlist_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlist_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist_searches`
--

DROP TABLE IF EXISTS `wishlist_searches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist_searches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `hotel_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `number_of_guests` int DEFAULT NULL,
  `number_of_rooms` int DEFAULT NULL,
  `nights` int DEFAULT NULL,
  `seasonality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quota_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_min` decimal(10,2) DEFAULT NULL,
  `price_max` decimal(10,2) DEFAULT NULL,
  `apartment_amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wishlist_searches_user_id_foreign` (`user_id`),
  CONSTRAINT `wishlist_searches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_searches_chk_1` CHECK (json_valid(`apartment_amenities`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist_searches`
--

LOCK TABLES `wishlist_searches` WRITE;
/*!40000 ALTER TABLE `wishlist_searches` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlist_searches` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-06 14:22:37
