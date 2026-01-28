CREATE DATABASE  IF NOT EXISTS `sioseg` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;
USE `sioseg`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sioseg
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `avaliacao_tecnica`
--

DROP TABLE IF EXISTS `avaliacao_tecnica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacao_tecnica` (
  `id_ava` int(11) NOT NULL AUTO_INCREMENT,
  `nota` enum('1','2','3','4','5') NOT NULL DEFAULT '1',
  `comentario` text DEFAULT NULL,
  `id_os_fk` int(11) NOT NULL,
  PRIMARY KEY (`id_ava`),
  KEY `fk_avaliacao_tecnica_ordem_servico1_idx` (`id_os_fk`),
  CONSTRAINT `fk_avaliacao_tecnica_ordem_servico1` FOREIGN KEY (`id_os_fk`) REFERENCES `ordem_servico` (`id_os`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliacao_tecnica`
--

LOCK TABLES `avaliacao_tecnica` WRITE;
/*!40000 ALTER TABLE `avaliacao_tecnica` DISABLE KEYS */;
INSERT INTO `avaliacao_tecnica` VALUES (22,'3','Serviço Realizado Perfeitamente',2);
/*!40000 ALTER TABLE `avaliacao_tecnica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `id_cli` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cli` varchar(255) DEFAULT NULL,
  `nome_social` varchar(255) DEFAULT NULL,
  `cnpj` varchar(14) DEFAULT NULL,
  `cpf_cli` varchar(11) DEFAULT NULL,
  `rg_cli` varchar(11) DEFAULT NULL,
  `rg_emissor_cli` varchar(10) DEFAULT NULL,
  `data_expedicao_rg_cli` date DEFAULT NULL,
  `data_nascimento_cli` date DEFAULT NULL,
  `data_cadastro_cli` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo_pessoa` enum('fisica','juridica') NOT NULL DEFAULT 'fisica',
  `tel1_cli` varchar(12) NOT NULL,
  `tel2_cli` varchar(12) DEFAULT NULL,
  `razao_social` varchar(255) DEFAULT NULL,
  `email_cli` varchar(255) NOT NULL,
  `senha_hash_cli` varchar(255) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `tipo_moradia` enum('casa','apartamento','outro') NOT NULL DEFAULT 'casa',
  `logradouro` varchar(255) NOT NULL,
  `cidade` varchar(40) NOT NULL,
  `bairro` varchar(40) NOT NULL,
  `uf` char(2) NOT NULL,
  `cep` char(8) NOT NULL,
  `ponto_referencia` varchar(255) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `num_end` varchar(10) NOT NULL DEFAULT 's/n',
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id_cli`),
  UNIQUE KEY `email_UNIQUE` (`email_cli`),
  UNIQUE KEY `rg_UNIQUE` (`rg_cli`),
  UNIQUE KEY `cnpj_UNIQUE` (`cnpj`),
  UNIQUE KEY `cpf_UNIQUE` (`cpf_cli`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (2,NULL,NULL,'21932707000106',NULL,NULL,NULL,NULL,NULL,'2025-09-26 02:55:29','juridica','61992754454','','Bem estar LTDA.','bem@gmail.com','$2y$10$.NF82EjX9bMMYc5LAr9FYOKVlEFGoYTUvXRpdQNE/TiUGm6alvyr2','Quadra QNP 36 Conjunto J','casa','Quadra QNP 36 Conjunto J','Brasília','Ceilândia Sul (Ceilândia)','DF','72236610','EM frente ao Supermercado','P-Norte','55','ativo'),(11,'João da Silva','',NULL,'10891489096','2577722','SSP-DF','2007-05-05','1987-02-02','2025-10-13 22:42:48','fisica','61987554242','',NULL,'joao@gmail.com','$2y$10$zt5P/09wQMwohq1eKvtpbOGEODSGONUFBygKMnQt1YPvUfQfBlfqa','Quadra QNP 36 Conjunto J','casa','Quadra QNP 36 Conjunto J','Brasília','Ceilândia Sul (Ceilândia)','DF','72236610','Em frente a igreja','P-sul','44','ativo');
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_usado`
--

DROP TABLE IF EXISTS `material_usado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_usado` (
  `id_prod_fk` int(11) NOT NULL,
  `id_os_fk` int(11) NOT NULL,
  `qtd_usada` int(11) NOT NULL,
  PRIMARY KEY (`id_prod_fk`,`id_os_fk`),
  KEY `fk_produto_has_ordem_servico_ordem_servico1_idx` (`id_os_fk`),
  KEY `fk_produto_has_ordem_servico_produto1_idx` (`id_prod_fk`),
  CONSTRAINT `fk_produto_has_ordem_servico_ordem_servico1` FOREIGN KEY (`id_os_fk`) REFERENCES `ordem_servico` (`id_os`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_produto_has_ordem_servico_produto1` FOREIGN KEY (`id_prod_fk`) REFERENCES `produto` (`id_prod`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_usado`
--

LOCK TABLES `material_usado` WRITE;
/*!40000 ALTER TABLE `material_usado` DISABLE KEYS */;
INSERT INTO `material_usado` VALUES (1,2,5);
/*!40000 ALTER TABLE `material_usado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordem_servico`
--

DROP TABLE IF EXISTS `ordem_servico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordem_servico` (
  `id_os` int(11) NOT NULL AUTO_INCREMENT,
  `servico_prestado` varchar(255) DEFAULT NULL,
  `tipo_servico` enum('instalacao','manutencao') NOT NULL,
  `status` enum('encerrada','em andamento','aberta','concluida') NOT NULL DEFAULT 'aberta',
  `data_abertura` datetime NOT NULL DEFAULT current_timestamp(),
  `data_agendamento` datetime NOT NULL,
  `data_encerramento` datetime DEFAULT NULL,
  `conclusao_cliente` enum('pendente','concluida') DEFAULT 'pendente',
  `conclusao_tecnico` enum('pendente','concluida') DEFAULT 'pendente',
  `id_tec_fk` int(11) NOT NULL,
  `id_usu_fk` int(11) NOT NULL,
  `id_cli_fk` int(11) NOT NULL,
  PRIMARY KEY (`id_os`),
  KEY `fk_ordem_servico_tecnico1_idx` (`id_tec_fk`),
  KEY `fk_ordem_servico_usuario1_idx` (`id_usu_fk`),
  KEY `fk_ordem_servico_cliente1_idx` (`id_cli_fk`),
  CONSTRAINT `fk_ordem_servico_cliente1` FOREIGN KEY (`id_cli_fk`) REFERENCES `cliente` (`id_cli`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ordem_servico_tecnico1` FOREIGN KEY (`id_tec_fk`) REFERENCES `tecnico` (`id_tec`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ordem_servico_usuario1` FOREIGN KEY (`id_usu_fk`) REFERENCES `usuario` (`id_usu`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordem_servico`
--

LOCK TABLES `ordem_servico` WRITE;
/*!40000 ALTER TABLE `ordem_servico` DISABLE KEYS */;
INSERT INTO `ordem_servico` VALUES (2,'','instalacao','concluida','2025-10-23 17:33:57','2025-10-23 17:35:00','2025-10-23 17:38:42','concluida','concluida',3,2,2),(1,'OS Encerrada Devido a testes','instalacao','encerrada','2025-10-23 17:37:10','2025-10-23 17:36:00',NULL,'pendente','pendente',3,2,2);
/*!40000 ALTER TABLE `ordem_servico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produto`
--

DROP TABLE IF EXISTS `produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto` (
  `id_prod` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `qtde` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id_prod`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produto`
--

LOCK TABLES `produto` WRITE;
/*!40000 ALTER TABLE `produto` DISABLE KEYS */;
INSERT INTO `produto` VALUES (1,'Tramontina','3,5x25mm','Parafuso com cabeça chata e fenda Phillips, fabricado em aço carbono com acabamento niquelado, indicado para montagem de móveis.',451,'Parafuso Phillips','ativo'),(2,'Furukawa','Gigalan OS2 Loose Tube','Cabo de Fibra Óptica Monomodo Furukawa Gigalan OS2 é indicado para aplicações de longa distância, garantindo alta performance e baixa atenuação no tráfego de dados.',100,'Cabo de Fibra Óptica Monomodo 2 Fibras','ativo');
/*!40000 ALTER TABLE `produto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tecnico`
--

DROP TABLE IF EXISTS `tecnico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tecnico` (
  `id_tec` int(11) NOT NULL AUTO_INCREMENT,
  `nome_tec` varchar(255) NOT NULL,
  `cpf_tec` varchar(11) NOT NULL,
  `rg_tec` varchar(11) NOT NULL,
  `rg_emissor_tec` varchar(10) NOT NULL,
  `data_expedicao_rg_tec` date NOT NULL,
  `data_nascimento_tec` date DEFAULT NULL,
  `data_cadastro_tec` datetime NOT NULL DEFAULT current_timestamp(),
  `email_tec` varchar(255) NOT NULL,
  `senha_hash_tec` varchar(255) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `tel_pessoal` varchar(12) NOT NULL,
  `tel_empresa` varchar(12) NOT NULL,
  PRIMARY KEY (`id_tec`),
  UNIQUE KEY `cpf_UNIQUE` (`cpf_tec`),
  UNIQUE KEY `rg_UNIQUE` (`rg_tec`),
  UNIQUE KEY `email_tec_UNIQUE` (`email_tec`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tecnico`
--

LOCK TABLES `tecnico` WRITE;
/*!40000 ALTER TABLE `tecnico` DISABLE KEYS */;
INSERT INTO `tecnico` VALUES (3,'Amanda','35479665073','2243775','SSP-DF','2005-10-10','1995-05-02','2025-10-02 16:26:31','amanda@gmail.com','$2y$10$984ZmWh2vZ61zJ.gYbvyB.GQ5EGqgO.bY.MpbR/i3OwwKI0uoqoPq','ativo','61927453232',''),(4,'Carlos Henrique da Silva','94497627080','2243774','SSP-SP','2010-02-02','2000-12-07','2025-10-13 22:52:50','carlos@gmail.com','$2y$10$BkfDszCdwokxLBne7HAdue0vPwFRP.tlFofaGyRFHKVlqgfkr5hgy','ativo','61994752222','');
/*!40000 ALTER TABLE `tecnico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usu` int(11) NOT NULL AUTO_INCREMENT,
  `nome_usu` varchar(255) NOT NULL,
  `cpf_usu` varchar(11) NOT NULL,
  `rg_usu` varchar(11) NOT NULL,
  `rg_emissor_usu` varchar(10) NOT NULL,
  `data_expedicao_rg_usu` date NOT NULL,
  `data_nascimento_usu` date DEFAULT NULL,
  `data_cadastro_usu` datetime NOT NULL DEFAULT current_timestamp(),
  `tel1_usu` varchar(12) NOT NULL,
  `tel2_usu` varchar(12) DEFAULT NULL,
  `tel3_usu` varchar(12) DEFAULT NULL,
  `email_usu` varchar(255) NOT NULL,
  `senha_hash_usu` varchar(255) NOT NULL,
  `perfil` enum('admin','funcionario') NOT NULL DEFAULT 'funcionario',
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id_usu`),
  UNIQUE KEY `email_usu_UNIQUE` (`email_usu`),
  UNIQUE KEY `cpf_usu_UNIQUE` (`cpf_usu`),
  UNIQUE KEY `rg_usu_UNIQUE` (`rg_usu`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (2,'Pedro Henrique Silva Assunção','30302483055','2999550','SSP-DF','2007-09-01','1995-09-07','2025-09-25 21:09:40','61999451212','','','admin@gmail.com','$2y$10$pMO.b.bNYpAHm5f9NvJdY.Jq1nSHwiKX6q4wSLaDRv8MafNi084rS','admin','ativo'),(3,'George','53610246006','2525099','SSP-DF','2015-10-10','2000-10-10','2025-09-26 02:25:35','61992754242','','','george@gmail.com','$2y$10$B7Q7N4Yf/h/2YB37hr6ZXOLHHMUmXTa7cpNzvStEEv.2olLHzjDrm','funcionario','ativo');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sioseg'
--

--
-- Dumping routines for database 'sioseg'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-23 17:47:45
