-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `hbrd_adm_app_leadszapp`;
CREATE TABLE `hbrd_adm_app_leadszapp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `contexto` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `mensagem` blob,
  `desativado` tinyint(1) DEFAULT '1',
  `local` enum('consultor','indicador','clinica','coordenador','associado') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_associado`;
CREATE TABLE `hbrd_adm_associado` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_estado` int(10) unsigned DEFAULT NULL,
  `id_cidade` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `telefone` varchar(45) DEFAULT NULL,
  `cpf` varchar(45) DEFAULT NULL,
  `cep` varchar(45) DEFAULT NULL,
  `rua` varchar(255) DEFAULT NULL,
  `numero` varchar(45) DEFAULT NULL,
  `complemento` text,
  `bairro` varchar(255) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `token` text,
  `access_token` text,
  `ip_session` int(11) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `foto` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_adm_associado_hist`;
CREATE TABLE `hbrd_adm_associado_hist` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_associado` int(10) unsigned DEFAULT NULL,
  `acao` text,
  `ip` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_adm_card`;
CREATE TABLE `hbrd_adm_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `saldo` decimal(8,2) DEFAULT NULL,
  `saldo_inicial` decimal(8,2) DEFAULT NULL,
  `bonificacao` decimal(8,2) DEFAULT '0.00',
  `stats` tinyint(1) DEFAULT '0',
  `desativado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_adm_company`;
CREATE TABLE `hbrd_adm_company` (
  `nome` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `nome_fantasia` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `logo` text COLLATE utf8_bin,
  `telefone` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `cnpj` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `endereco` text CHARACTER SET utf8,
  `id` int(10) NOT NULL,
  `logo_sistema` text COLLATE utf8_bin,
  `inspection_configs` mediumtext COLLATE utf8_bin,
  `indication_edit_config` mediumtext COLLATE utf8_bin,
  `link_facebook` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `link_instagram` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `link_playstore` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `link_applestore` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `link_app` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `vistoria_distancia` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_consultant`;
CREATE TABLE `hbrd_adm_consultant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_estado` int(11) unsigned DEFAULT NULL,
  `id_cidade` int(11) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_nascimento` datetime DEFAULT NULL,
  `rua` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numero` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complemento` text COLLATE utf8_unicode_ci,
  `bairro` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cep` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banco` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agencia` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `operacao` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conta` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recebe_comissao` tinyint(1) DEFAULT '0',
  `valor_comissao` decimal(8,2) DEFAULT NULL,
  `desativado` tinyint(1) DEFAULT '0',
  `possui_seguro` tinyint(1) DEFAULT NULL,
  `placa` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ano` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `valor` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `solicitado` tinyint(1) DEFAULT NULL,
  `telefone2` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `foto` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cnh_frente` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cnh_verso` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rg_frente` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rg_verso` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `id_equipe` int(10) DEFAULT NULL,
  `segundo_email` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_session` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` text COLLATE utf8_unicode_ci,
  `senha` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` text COLLATE utf8_unicode_ci,
  `aprovacao` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `obs_docs` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `fk_consultant_for_state_idx` (`id_estado`),
  KEY `fk_consultant_for_city_idx` (`id_cidade`),
  CONSTRAINT `fk_consultant_for_city` FOREIGN KEY (`id_cidade`) REFERENCES `hbrd_main_util_city` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_consultant_for_state` FOREIGN KEY (`id_estado`) REFERENCES `hbrd_main_util_state` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_adm_consultant_has_adm_card`;
CREATE TABLE `hbrd_adm_consultant_has_adm_card` (
  `id_consultor` int(10) unsigned NOT NULL,
  `id_cartao` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_consultor`,`id_cartao`),
  KEY `fk_hbrd_adm_consultant_has_adm_card_hbrd_adm_card1_idx` (`id_cartao`),
  CONSTRAINT `fk_hbrd_adm_consultant_has_adm_card_hbrd_adm_card1` FOREIGN KEY (`id_cartao`) REFERENCES `hbrd_adm_card` (`id`),
  CONSTRAINT `fk_hbrd_adm_consultant_has_adm_card_hbrd_adm_consultant1` FOREIGN KEY (`id_consultor`) REFERENCES `hbrd_adm_consultant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_adm_consultant_has_team`;
CREATE TABLE `hbrd_adm_consultant_has_team` (
  `id_equipe` int(10) unsigned NOT NULL,
  `id_consultor` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_consultor`),
  KEY `fk_team` (`id_equipe`),
  CONSTRAINT `fk_consutant` FOREIGN KEY (`id_consultor`) REFERENCES `hbrd_adm_consultant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_team` FOREIGN KEY (`id_equipe`) REFERENCES `hbrd_adm_team` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_adm_consultant_hist`;
CREATE TABLE `hbrd_adm_consultant_hist` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_consultant` int(10) unsigned DEFAULT NULL,
  `acao` text,
  `ip` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_adm_indication_zap`;
CREATE TABLE `hbrd_adm_indication_zap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `contexto` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `mensagem` blob,
  `desativado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_integration`;
CREATE TABLE `hbrd_adm_integration` (
  `id` int(10) unsigned NOT NULL,
  `leadszapp_status` tinyint(1) DEFAULT '0',
  `leadszapp_token` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `leadszapp_bot` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `public_key` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `access_token` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `client_id` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `client_secret` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_payment`;
CREATE TABLE `hbrd_adm_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_comissionado` int(10) unsigned DEFAULT NULL,
  `id_consultor` int(10) unsigned DEFAULT NULL,
  `id_cartao` int(10) unsigned DEFAULT NULL,
  `qtd_indicacoes` int(11) DEFAULT NULL,
  `valor` decimal(8,2) DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `referencia` enum('consultor','comissionado') COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_solicitacao` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_adm_payment_hbrd_adm_comissioned1_idx` (`id_comissionado`),
  KEY `fk_hbrd_adm_payment_hbrd_adm_card1_idx` (`id_cartao`),
  KEY `fk_hbrd_adm_payment_hbrd_adm_consultant1_idx` (`id_consultor`),
  CONSTRAINT `fk_hbrd_adm_payment_hbrd_adm_card1` FOREIGN KEY (`id_cartao`) REFERENCES `hbrd_adm_card` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_hbrd_adm_payment_hbrd_adm_comissioned1` FOREIGN KEY (`id_comissionado`) REFERENCES `hbrd_cms_comissioned` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_hbrd_adm_payment_hbrd_adm_consultant1` FOREIGN KEY (`id_consultor`) REFERENCES `hbrd_adm_consultant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_adm_plan_doc`;
CREATE TABLE `hbrd_adm_plan_doc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `template` mediumtext COLLATE utf8_bin,
  `contexto` text COLLATE utf8_bin,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_plan_doc_variables`;
CREATE TABLE `hbrd_adm_plan_doc_variables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_plan_doc` int(10) unsigned DEFAULT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `variavel` varchar(25) DEFAULT NULL,
  `ordem` tinyint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_adm_plan_doc_variables_1_idx` (`id_plan_doc`),
  CONSTRAINT `fk_hbrd_adm_plan_doc_variables_1` FOREIGN KEY (`id_plan_doc`) REFERENCES `hbrd_adm_plan_doc` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_adm_plan_has_doc`;
CREATE TABLE `hbrd_adm_plan_has_doc` (
  `id_plan` int(10) unsigned NOT NULL,
  `id_doc` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_plan_item`;
CREATE TABLE `hbrd_adm_plan_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_plan` int(10) unsigned NOT NULL,
  `de_val` decimal(8,2) DEFAULT NULL,
  `ate_val` decimal(8,2) DEFAULT NULL,
  `grupo` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adesao` decimal(8,2) DEFAULT NULL,
  `val_mensal` decimal(8,2) DEFAULT NULL,
  `participacao` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `val_minimo` decimal(8,2) DEFAULT NULL,
  `id_categoria` int(10) unsigned NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `participacao_max` decimal(8,2) DEFAULT NULL,
  `participacao_min` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_adm_sectional`;
CREATE TABLE `hbrd_adm_sectional` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_bin NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `meta_ativacoes` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_sectional_has_manager`;
CREATE TABLE `hbrd_adm_sectional_has_manager` (
  `id_regional` int(10) unsigned NOT NULL,
  `id_consultor` int(10) unsigned NOT NULL,
  KEY `id_regional` (`id_regional`),
  KEY `id_consultor` (`id_consultor`),
  CONSTRAINT `hbrd_adm_sectional_has_manager_ibfk_1` FOREIGN KEY (`id_regional`) REFERENCES `hbrd_adm_sectional` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_adm_sectional_has_manager_ibfk_2` FOREIGN KEY (`id_consultor`) REFERENCES `hbrd_adm_consultant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_setting`;
CREATE TABLE `hbrd_adm_setting` (
  `id` int(10) unsigned NOT NULL,
  `arquivar_ind_dias` int(11) DEFAULT NULL,
  `arquivar_ind_motivo` int(10) NOT NULL,
  `arquivar_ind_status` tinyint(1) DEFAULT '0',
  `solicitacoes_pagamento` enum('consultor','sistema') COLLATE utf8_unicode_ci DEFAULT 'sistema',
  `hbrd_adm_settingcol` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bonificacao_min` decimal(8,2) DEFAULT '0.00',
  `bonificacao_max` decimal(8,2) DEFAULT '0.00',
  `apenas_assinatura` tinyint(1) DEFAULT '0',
  `consultor_desarquivar_indicacao` tinyint(1) DEFAULT '1',
  `centro_custo_obrigatorio` tinyint(1) DEFAULT '0',
  `categoria_obrigatorio` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_adm_store_hist`;
CREATE TABLE `hbrd_adm_store_hist` (
  `id` int(10) NOT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_store` int(10) unsigned DEFAULT NULL,
  `acao` text,
  `ip` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_adm_team`;
CREATE TABLE `hbrd_adm_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `equipe` varchar(255) CHARACTER SET utf8 NOT NULL,
  `meta` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `id_coordenador` int(10) unsigned DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `meta_consultores` int(10) DEFAULT NULL,
  `id_regional` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idhbrd_adm_team_UNIQUE` (`id`),
  KEY `fk_hbrd_adm_team_3452345341_idx` (`id_regional`),
  CONSTRAINT `fk_hbrd_adm_team_118927348901237409` FOREIGN KEY (`id_regional`) REFERENCES `hbrd_adm_sectional` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_adm_team_has_coordinator`;
CREATE TABLE `hbrd_adm_team_has_coordinator` (
  `id_equipe` int(10) unsigned NOT NULL,
  `id_consultor` int(10) unsigned NOT NULL,
  KEY `id_equipe` (`id_equipe`),
  KEY `id_consultor` (`id_consultor`),
  CONSTRAINT `hbrd_adm_team_has_coordinator_ibfk_1` FOREIGN KEY (`id_equipe`) REFERENCES `hbrd_adm_team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_adm_team_has_coordinator_ibfk_2` FOREIGN KEY (`id_consultor`) REFERENCES `hbrd_adm_consultant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_assinatura`;
CREATE TABLE `hbrd_app_assinatura` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_associado` int(10) unsigned NOT NULL,
  `user_id` varchar(200) DEFAULT NULL,
  `id_pet` varchar(45) DEFAULT NULL,
  `assinatura` varchar(500) NOT NULL,
  `application_id` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `external_reference` varchar(455) DEFAULT NULL,
  `payment_method_id` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `descricao` varchar(455) DEFAULT NULL,
  `cancelado_em` datetime DEFAULT NULL,
  `id_arquivamento` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assinatura_UNIQUE` (`assinatura`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_associado`;
CREATE TABLE `hbrd_app_associado` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(10) unsigned NOT NULL,
  `id_equipe` int(10) unsigned DEFAULT NULL COMMENT 'comercial',
  `observacao` text,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) NOT NULL DEFAULT '1',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pessoa` (`id_pessoa`),
  CONSTRAINT `hbrd_app_associado_ibfk_2` FOREIGN KEY (`id_pessoa`) REFERENCES `hbrd_app_pessoa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_campanha_doacoes`;
CREATE TABLE `hbrd_app_campanha_doacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `descricao` text,
  `responsavel` varchar(45) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `ordem` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_clinica`;
CREATE TABLE `hbrd_app_clinica` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(10) unsigned NOT NULL,
  `id_equipe` int(10) unsigned DEFAULT NULL COMMENT 'Faz parte da logica do comercial',
  `cnpj` varchar(500) DEFAULT NULL,
  `nome_fantasia` varchar(500) NOT NULL,
  `responsavel` varchar(60) DEFAULT NULL,
  `responsavel2` varchar(60) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `situacao` enum('aprovado','reprovado') DEFAULT NULL,
  `solicitado` tinyint(1) DEFAULT '1',
  `latitude` varchar(455) DEFAULT NULL,
  `longitude` varchar(455) DEFAULT NULL,
  `zoom` varchar(45) DEFAULT NULL,
  `observacao` text,
  `logo` varchar(500) DEFAULT NULL,
  `reativar` varchar(45) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_pessoa` (`id_pessoa`),
  CONSTRAINT `hbrd_app_clinica_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `hbrd_app_pessoa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_clinica_fotos`;
CREATE TABLE `hbrd_app_clinica_fotos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_clinica` int(10) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `legenda` varchar(255) DEFAULT NULL,
  `ordem` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_clinica_use_beneficio`;
CREATE TABLE `hbrd_app_clinica_use_beneficio` (
  `id_clinica` int(10) unsigned NOT NULL,
  `id_beneficio` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_clinica`,`id_beneficio`),
  KEY `id_beneficio` (`id_beneficio`),
  CONSTRAINT `hbrd_app_clinica_use_beneficio_ibfk_1` FOREIGN KEY (`id_clinica`) REFERENCES `hbrd_app_clinica` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_app_clinica_use_beneficio_ibfk_2` FOREIGN KEY (`id_beneficio`) REFERENCES `hbrd_app_plano_beneficio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_consultor`;
CREATE TABLE `hbrd_app_consultor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(10) unsigned NOT NULL,
  `id_equipe` int(10) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `cnh_frente` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cnh_verso` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rg_frente` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rg_verso` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `solicitado` tinyint(1) DEFAULT '1',
  `reativar` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_pessoa` (`id_pessoa`),
  CONSTRAINT `hbrd_app_consultor_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `hbrd_app_pessoa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_doacoes`;
CREATE TABLE `hbrd_app_doacoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(10) unsigned NOT NULL,
  `id_ong` int(10) unsigned NOT NULL,
  `id_campanha` int(10) unsigned DEFAULT NULL,
  `valor` float unsigned NOT NULL,
  `id_pagamento` int(10) unsigned NOT NULL,
  `status_pagamento` varchar(45) NOT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(45) DEFAULT NULL,
  `nome_doador` varchar(100) DEFAULT NULL,
  `cpf_doador` varchar(20) DEFAULT NULL,
  `email_doador` varchar(60) DEFAULT NULL,
  `telefone_doador` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_equipe`;
CREATE TABLE `hbrd_app_equipe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) CHARACTER SET utf8 NOT NULL,
  `meta` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `meta_consultores` int(10) DEFAULT NULL,
  `id_regional` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_equipe_coordenador`;
CREATE TABLE `hbrd_app_equipe_coordenador` (
  `id_consultor` int(10) unsigned NOT NULL,
  `id_equipe` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_log`;
CREATE TABLE `hbrd_app_log` (
  `id` bigint(19) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(45) DEFAULT NULL,
  `atividade` text,
  `date` datetime DEFAULT NULL,
  `id_pessoa` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_mensalidades`;
CREATE TABLE `hbrd_app_mensalidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_associado` int(10) unsigned NOT NULL,
  `id_mensalidade` varchar(455) DEFAULT NULL COMMENT 'id para consulta no mercado pago',
  `valor` float NOT NULL,
  `data_fatura` datetime DEFAULT NULL,
  `status` varchar(455) DEFAULT 'aberta',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_motive`;
CREATE TABLE `hbrd_app_motive` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` enum('1','0') COLLATE utf8_unicode_ci DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_motivo_cancelamento_agendamento`;
CREATE TABLE `hbrd_app_motivo_cancelamento_agendamento` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `motivo` varchar(100) NOT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `stats` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_notificacao`;
CREATE TABLE `hbrd_app_notificacao` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `contexto` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `mensagem` blob,
  `desativado` tinyint(1) DEFAULT '1',
  `local` enum('Consultor','Clinica','Associado') COLLATE utf8_bin DEFAULT NULL,
  `ta_pronto` tinyint(1) DEFAULT '0',
  `envia_email` tinyint(1) DEFAULT '0',
  `template_email` text COLLATE utf8_bin,
  `assunto` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_ong`;
CREATE TABLE `hbrd_app_ong` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_estado` int(10) unsigned DEFAULT NULL,
  `id_cidade` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `telefone1` varchar(45) DEFAULT NULL,
  `telefone2` varchar(45) DEFAULT NULL,
  `cep` varchar(45) DEFAULT NULL,
  `rua` varchar(255) DEFAULT NULL,
  `numero` varchar(45) DEFAULT NULL,
  `complemento` text,
  `descricao` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) DEFAULT NULL,
  `banco` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `agencia` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `operacao` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `conta` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf_cnpj` varchar(45) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `logo` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_ong_fotos`;
CREATE TABLE `hbrd_app_ong_fotos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ong` int(10) unsigned DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `legenda` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_blog_galerias_fotos_ong_hbrd_cms_blog_ong_idx` (`id_ong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_ong_use_campanha`;
CREATE TABLE `hbrd_app_ong_use_campanha` (
  `id_ong` int(11) NOT NULL,
  `id_campanha` int(11) NOT NULL,
  PRIMARY KEY (`id_ong`,`id_campanha`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pessoa`;
CREATE TABLE `hbrd_app_pessoa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(500) NOT NULL,
  `telefone` varchar(500) NOT NULL,
  `telefone2` varchar(500) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cpf` varchar(500) DEFAULT NULL,
  `email` varchar(500) DEFAULT NULL,
  `email2` varchar(500) DEFAULT NULL,
  `senha` varchar(128) DEFAULT NULL,
  `salt` varchar(10) DEFAULT NULL,
  `cep` varchar(500) DEFAULT NULL,
  `rg` varchar(500) DEFAULT NULL,
  `orgao_exp` varchar(45) DEFAULT NULL,
  `usuario` varchar(500) DEFAULT NULL,
  `delete_at` varchar(500) DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `rua` varchar(500) DEFAULT NULL,
  `endereco` varchar(500) DEFAULT NULL,
  `numero` varchar(500) DEFAULT NULL,
  `complemento` varchar(500) DEFAULT NULL,
  `bairro` varchar(500) DEFAULT NULL,
  `id_estado` int(11) unsigned DEFAULT NULL,
  `id_cidade` int(11) unsigned DEFAULT NULL,
  `foto` varchar(500) DEFAULT NULL,
  `username` varchar(500) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `cpf_UNIQUE` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pessoa_hist`;
CREATE TABLE `hbrd_app_pessoa_hist` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(10) unsigned NOT NULL,
  `acao` varchar(100) NOT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pessoa_pushtoken`;
CREATE TABLE `hbrd_app_pessoa_pushtoken` (
  `id_pessoa` int(10) unsigned NOT NULL,
  `token` text COLLATE utf8_bin NOT NULL,
  `device` varchar(1000) COLLATE utf8_bin NOT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `plataforma` enum('web','app') COLLATE utf8_bin DEFAULT 'web',
  PRIMARY KEY (`device`,`id_pessoa`),
  KEY `fk_hbrd_app_pessoa_pushtoken_1_idx` (`id_pessoa`),
  CONSTRAINT `fk_hbrd_app_pessoa_pushtoken_1` FOREIGN KEY (`id_pessoa`) REFERENCES `hbrd_app_pessoa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_pet`;
CREATE TABLE `hbrd_app_pet` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_proposta` int(10) unsigned DEFAULT NULL,
  `id_associado` int(10) unsigned DEFAULT NULL,
  `id_raca` int(10) unsigned DEFAULT NULL,
  `id_especie` int(10) unsigned DEFAULT NULL,
  `id_plano` int(10) unsigned DEFAULT NULL,
  `nome` varchar(200) DEFAULT NULL,
  `peso` decimal(8,2) DEFAULT NULL,
  `cor` varchar(45) DEFAULT NULL,
  `porte` enum('Pequeno','Médio','Grande') DEFAULT NULL,
  `sexo` enum('Macho','Fêmea') DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(500) DEFAULT NULL,
  `valor` decimal(8,2) DEFAULT NULL,
  `hash` varchar(32) DEFAULT NULL,
  `classificacao` enum('pendente','arquivada','ativada') DEFAULT 'pendente',
  `activated_at` datetime DEFAULT NULL,
  `activated_by` int(10) DEFAULT NULL COMMENT 'Usuario do sistema que ativou o pet',
  `id_status` int(10) DEFAULT '1',
  `status_final` tinyint(1) DEFAULT '0' COMMENT 'status final concluido',
  `id_arquivamento` int(10) DEFAULT NULL,
  `idade` int(10) DEFAULT NULL,
  `arquivado_em` datetime DEFAULT NULL,
  `bonificacao` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hbrd_app_pet_ibfk_1` (`id_proposta`),
  KEY `id_associado` (`id_associado`),
  CONSTRAINT `hbrd_app_pet_ibfk_1` FOREIGN KEY (`id_proposta`) REFERENCES `hbrd_app_proposta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_app_pet_ibfk_2` FOREIGN KEY (`id_associado`) REFERENCES `hbrd_app_associado` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_agendamento`;
CREATE TABLE `hbrd_app_pet_agendamento` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL,
  `data_hora` datetime NOT NULL,
  `id_especialidade` int(10) unsigned NOT NULL,
  `id_pet` int(10) NOT NULL,
  `id_status` int(10) unsigned NOT NULL DEFAULT '1',
  `id_clinica` int(10) unsigned NOT NULL,
  `data_agendamento` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_realizado` datetime DEFAULT NULL,
  `data_cancelado` datetime DEFAULT NULL,
  `status` enum('Pendente','Agendado','Concluido','Cancelado') DEFAULT 'Pendente',
  `id_motivo_cancelamento` int(10) unsigned DEFAULT NULL,
  `nome_medico` varchar(60) DEFAULT NULL,
  `crv_medico` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_agendamento_status`;
CREATE TABLE `hbrd_app_pet_agendamento_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(45) DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `mensagem` varchar(500) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `whastapp` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_banhos`;
CREATE TABLE `hbrd_app_pet_banhos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_pet` int(10) unsigned NOT NULL,
  `id_agendamento` int(10) unsigned DEFAULT NULL,
  `clinica` varchar(60) DEFAULT NULL,
  `nome` varchar(60) DEFAULT NULL,
  `data_banho` datetime DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `observacao` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_cirurgias`;
CREATE TABLE `hbrd_app_pet_cirurgias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pet` int(11) NOT NULL,
  `id_clinica` int(10) unsigned NOT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `observacao` varchar(500) DEFAULT NULL,
  `data_hora` datetime NOT NULL,
  `data_agendamento` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_realizado` datetime DEFAULT NULL,
  `data_cancelado` datetime DEFAULT NULL,
  `id_motivo_cancelamento` int(10) unsigned DEFAULT NULL,
  `status` enum('Pendente','Agendado','Concluido','Cancelado') DEFAULT 'Pendente',
  `id_especialidade` int(11) DEFAULT NULL,
  `nome_medico` varchar(60) DEFAULT NULL,
  `crv_medico` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_especie`;
CREATE TABLE `hbrd_app_pet_especie` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_exames`;
CREATE TABLE `hbrd_app_pet_exames` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_pet` int(10) unsigned NOT NULL,
  `id_agendamento` int(10) unsigned DEFAULT NULL,
  `id_clinica` int(10) unsigned DEFAULT NULL,
  `clinica` varchar(60) DEFAULT NULL,
  `id_especialidade` int(10) unsigned DEFAULT NULL,
  `nome_medico` varchar(60) DEFAULT NULL,
  `crv_medico` varchar(60) DEFAULT NULL,
  `nome` varchar(60) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `observacao` text,
  `id_motivo_cancelamento` int(11) DEFAULT NULL,
  `data_cancelado` datetime DEFAULT NULL,
  `status` enum('Pendente','Agendado','Concluido','Cancelado') DEFAULT 'Pendente',
  `anexo` varchar(500) DEFAULT NULL,
  `data` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_internacoes`;
CREATE TABLE `hbrd_app_pet_internacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pet` int(11) NOT NULL,
  `id_clinica` int(10) unsigned NOT NULL,
  `id_especialidade` int(11) DEFAULT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `observacao` varchar(500) DEFAULT NULL,
  `data_hora` datetime NOT NULL,
  `data_agendamento` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_entrada` datetime DEFAULT NULL,
  `data_saida` datetime DEFAULT NULL,
  `id_motivo_cancelamento` int(10) unsigned DEFAULT NULL,
  `status` enum('Pendente','Agendado','Concluido','Cancelado') DEFAULT 'Pendente',
  `nome_medico` varchar(60) DEFAULT NULL,
  `crv_medico` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_medicamentos`;
CREATE TABLE `hbrd_app_pet_medicamentos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_pet` int(10) unsigned NOT NULL,
  `id_agendamento` int(10) unsigned DEFAULT NULL,
  `nome` varchar(60) DEFAULT NULL,
  `dt_inicio` datetime DEFAULT NULL,
  `id_tipo_tratamento` int(10) unsigned DEFAULT NULL,
  `dosagem` varchar(100) DEFAULT NULL,
  `quantidade` varchar(45) DEFAULT NULL,
  `frequencia` varchar(100) DEFAULT NULL,
  `nome_medico` varchar(60) DEFAULT NULL,
  `instrucao` varchar(100) DEFAULT NULL,
  `validade` date DEFAULT NULL,
  `lote` varchar(45) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `observacao` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_proposta_status`;
CREATE TABLE `hbrd_app_pet_proposta_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(45) DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `mensagem` varchar(500) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `dados` tinyint(1) DEFAULT '0',
  `vistoria` tinyint(1) DEFAULT '0',
  `whastapp` tinyint(1) DEFAULT '1',
  `contrato` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_raca`;
CREATE TABLE `hbrd_app_pet_raca` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) DEFAULT NULL,
  `id_especie` int(10) unsigned DEFAULT NULL,
  `delete_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_especie_idx` (`id_especie`),
  CONSTRAINT `id_especie` FOREIGN KEY (`id_especie`) REFERENCES `hbrd_app_pet_especie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_termo`;
CREATE TABLE `hbrd_app_pet_termo` (
  `id_pet` int(10) unsigned NOT NULL,
  `assinatura` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `selfie` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `frente_doc` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `atras_doc` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `tipo_doc` enum('rg','cnh') COLLATE utf8_bin DEFAULT NULL,
  `lat` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `lng` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `id_contrato` int(10) unsigned DEFAULT NULL,
  `perguntas_contrato` json DEFAULT NULL,
  `contrato_tipo` enum('Manuscrito','Digital') COLLATE utf8_bin DEFAULT NULL,
  `status` enum('pendente','aprovada','reanalise') COLLATE utf8_bin DEFAULT 'pendente',
  `ip` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `dt_envio` datetime DEFAULT NULL,
  `doc_recusados` json DEFAULT NULL,
  `feito_em` enum('externo','app') COLLATE utf8_bin DEFAULT NULL,
  `contrato_pdf` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id_pet`),
  UNIQUE KEY `id_pet_UNIQUE` (`id_pet`),
  CONSTRAINT `k_termo_pet` FOREIGN KEY (`id_pet`) REFERENCES `hbrd_app_pet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_pet_tipo_tratamento`;
CREATE TABLE `hbrd_app_pet_tipo_tratamento` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_especie` int(10) unsigned NOT NULL,
  `tratamento` varchar(45) NOT NULL,
  `stats` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_vacinas`;
CREATE TABLE `hbrd_app_pet_vacinas` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_pet` int(10) unsigned NOT NULL,
  `id_agendamento` int(10) unsigned DEFAULT NULL,
  `id_clinica` int(11) unsigned DEFAULT NULL,
  `id_especialidade` int(11) unsigned DEFAULT NULL,
  `nome_vacina` varchar(455) DEFAULT NULL,
  `data_vacina` date DEFAULT NULL,
  `data_revacina` date DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `nome_medico` varchar(455) DEFAULT NULL,
  `crv_medico` varchar(455) DEFAULT NULL,
  `nome_clinica` varchar(255) DEFAULT NULL,
  `status` enum('Pendente','Agendado','Concluido','Cancelado') DEFAULT 'Pendente',
  `id_motivo_cancelamento` int(11) DEFAULT NULL,
  `data_cancelado` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_vermifugos`;
CREATE TABLE `hbrd_app_pet_vermifugos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_pet` int(10) unsigned NOT NULL,
  `id_agendamento` int(10) unsigned DEFAULT NULL,
  `nome_vermifungo` varchar(60) DEFAULT NULL,
  `data_vermifungo` datetime DEFAULT NULL,
  `proxima_data` datetime DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `observacao` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_vistoria`;
CREATE TABLE `hbrd_app_pet_vistoria` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pet` int(10) unsigned NOT NULL,
  `id_modelo` int(10) unsigned NOT NULL,
  `create_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('Pendente','Aprovada') NOT NULL DEFAULT 'Pendente',
  `observacao` varchar(500) DEFAULT NULL,
  `ultimo_item_enviado` datetime DEFAULT NULL,
  `reanalise` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_pet_vistoria_idx` (`id_pet`),
  CONSTRAINT `fk_pet_vistoria` FOREIGN KEY (`id_pet`) REFERENCES `hbrd_app_pet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_vistoria_arquivo`;
CREATE TABLE `hbrd_app_pet_vistoria_arquivo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_vistoria` int(10) unsigned NOT NULL,
  `descricao` varchar(500) NOT NULL,
  `arquivo` varchar(500) NOT NULL,
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_pet_vistoria_item`;
CREATE TABLE `hbrd_app_pet_vistoria_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `imagem` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aprovado` tinyint(4) DEFAULT NULL,
  `id_vistoria` int(10) unsigned NOT NULL,
  `id_modelo_item` int(10) unsigned NOT NULL,
  `lat` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `lng` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `motivo_recusa` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fotografado_em` datetime DEFAULT NULL,
  `localizacao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `upload_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_vistoria` (`id_vistoria`),
  KEY `id_modelo_item` (`id_modelo_item`),
  CONSTRAINT `hbrd_app_pet_vistoria_item_ibfk_1` FOREIGN KEY (`id_vistoria`) REFERENCES `hbrd_app_pet_vistoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_app_pet_vistoria_item_ibfk_2` FOREIGN KEY (`id_modelo_item`) REFERENCES `hbrd_app_vistoria_modelo_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_planos`;
CREATE TABLE `hbrd_app_planos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) DEFAULT NULL,
  `valor` double(10,2) DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '0',
  `delete_at` datetime DEFAULT NULL,
  `shared_msg` blob,
  `ordem` tinyint(10) unsigned DEFAULT NULL,
  `shared_msg_status` tinyint(1) unsigned DEFAULT NULL,
  `shared_pdf_status` tinyint(1) unsigned DEFAULT NULL,
  `shared_pdf` blob,
  `descricao` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_plano_beneficio`;
CREATE TABLE `hbrd_app_plano_beneficio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `descricao` text,
  `ordem` int(11) DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_plano_has_termo`;
CREATE TABLE `hbrd_app_plano_has_termo` (
  `id_plano` int(10) unsigned NOT NULL,
  `id_termo` int(10) unsigned NOT NULL,
  KEY `id_plano` (`id_plano`),
  KEY `id_termo` (`id_termo`),
  CONSTRAINT `hbrd_app_plano_has_termo_ibfk_1` FOREIGN KEY (`id_plano`) REFERENCES `hbrd_app_planos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_app_plano_has_termo_ibfk_2` FOREIGN KEY (`id_termo`) REFERENCES `hbrd_app_termo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_plano_in_regional`;
CREATE TABLE `hbrd_app_plano_in_regional` (
  `id_regional` int(10) unsigned NOT NULL,
  `id_plano` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_regional`,`id_plano`),
  KEY `fk_hbrd_adm_plan_has_sectional_2_idx` (`id_plano`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_plano_use_beneficio`;
CREATE TABLE `hbrd_app_plano_use_beneficio` (
  `id_plano` int(11) unsigned NOT NULL,
  `id_beneficio` int(11) unsigned NOT NULL,
  KEY `id_plano` (`id_plano`),
  KEY `id_beneficio` (`id_beneficio`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_proposta`;
CREATE TABLE `hbrd_app_proposta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pessoa` int(10) unsigned NOT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `indicador` enum('consultor','clinica','associado') DEFAULT NULL COMMENT 'Diz respeito a indicação',
  `id_consultor` int(10) DEFAULT NULL COMMENT 'Diz respeito a indicação',
  `id_associado` int(10) DEFAULT NULL COMMENT 'Diz respeito a indicação',
  `id_clinica` int(10) DEFAULT NULL COMMENT 'Diz respeito a indicação',
  PRIMARY KEY (`id`),
  KEY `id_consultor` (`id_consultor`),
  KEY `fk_hbrd_app_proposta_1_idx` (`id_pessoa`),
  CONSTRAINT `fk_hbrd_app_proposta_1` FOREIGN KEY (`id_pessoa`) REFERENCES `hbrd_app_pessoa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_proposta_anotacao`;
CREATE TABLE `hbrd_app_proposta_anotacao` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_proposta` int(10) unsigned DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `anotacao` varchar(500) DEFAULT NULL,
  `id_pessoa` int(10) DEFAULT NULL,
  `nome` varchar(45) NOT NULL COMMENT 'nome da pessoa que esta interagindo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_proposta_has_pet`;
CREATE TABLE `hbrd_app_proposta_has_pet` (
  `id_app_pet` int(10) unsigned NOT NULL,
  `id_app_proposta` int(10) unsigned NOT NULL,
  KEY `FK_hbrd_app_proposta_has_pet_hbrd_app_pet` (`id_app_pet`),
  KEY `FK_hbrd_app_proposta_has_pet_hbrd_app_proposta` (`id_app_proposta`),
  CONSTRAINT `FK_hbrd_app_proposta_has_pet_hbrd_app_pet` FOREIGN KEY (`id_app_pet`) REFERENCES `hbrd_app_pet_proposta` (`id`),
  CONSTRAINT `FK_hbrd_app_proposta_has_pet_hbrd_app_proposta` FOREIGN KEY (`id_app_proposta`) REFERENCES `hbrd_app_proposta` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_proposta_hist`;
CREATE TABLE `hbrd_app_proposta_hist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_proposta` int(10) unsigned DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `atividade` varchar(500) DEFAULT NULL,
  `id_pessoa` int(10) DEFAULT NULL,
  `nome` varchar(45) NOT NULL COMMENT 'nome da pessoa que esta interagindo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_regional`;
CREATE TABLE `hbrd_app_regional` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8_bin NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `meta_ativacoes` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_regional_gestor`;
CREATE TABLE `hbrd_app_regional_gestor` (
  `id_consultor` int(10) unsigned NOT NULL,
  `id_regional` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_status_agendamento`;
CREATE TABLE `hbrd_app_status_agendamento` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `status` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_termo`;
CREATE TABLE `hbrd_app_termo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `template` mediumtext COLLATE utf8_bin,
  `contexto` text COLLATE utf8_bin,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_app_termo_variables`;
CREATE TABLE `hbrd_app_termo_variables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_termo` int(10) unsigned DEFAULT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `variavel` varchar(25) DEFAULT NULL,
  `ordem` tinyint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_app_termo_variables_1_idx` (`id_termo`),
  CONSTRAINT `fk_hbrd_app_termo_variables_1_idx` FOREIGN KEY (`id_termo`) REFERENCES `hbrd_app_termo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_app_textos_app`;
CREATE TABLE `hbrd_app_textos_app` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) DEFAULT NULL,
  `texto` longtext,
  `slug` varchar(60) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_tipo_servico`;
CREATE TABLE `hbrd_app_tipo_servico` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `servico` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_app_user`;
CREATE TABLE `hbrd_app_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_estado` int(10) unsigned DEFAULT NULL,
  `id_cidade` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_nascimento` datetime DEFAULT NULL,
  `rua` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numero` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complemento` text COLLATE utf8_unicode_ci,
  `bairro` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cep` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banco` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agencia` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `operacao` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conta` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '0',
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `token` text COLLATE utf8_unicode_ci,
  `foto` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cnh` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_adm_user_1_idx` (`id_cidade`),
  KEY `fk_hbrd_adm_user_2` (`id_estado`),
  CONSTRAINT `fk_hbrd_adm_user_1` FOREIGN KEY (`id_cidade`) REFERENCES `hbrd_main_util_city` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_hbrd_adm_user_2` FOREIGN KEY (`id_estado`) REFERENCES `hbrd_main_util_state` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_vistoria_modelo`;
CREATE TABLE `hbrd_app_vistoria_modelo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `delete_at` datetime DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `ordem` tinyint(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_app_vistoria_modelo_item`;
CREATE TABLE `hbrd_app_vistoria_modelo_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `imagem_exemplo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_modelo` int(10) unsigned NOT NULL,
  `lib_access` tinyint(4) DEFAULT '0',
  `required` tinyint(4) DEFAULT '0',
  `delete_at` datetime DEFAULT NULL,
  `ordem` tinyint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_modelo` (`id_modelo`),
  CONSTRAINT `hbrd_app_vistoria_modelo_item_ibfk_1` FOREIGN KEY (`id_modelo`) REFERENCES `hbrd_app_vistoria_modelo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_blog`;
CREATE TABLE `hbrd_cms_blog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pagina` int(10) unsigned DEFAULT NULL,
  `id_categoria` int(10) unsigned DEFAULT NULL,
  `data` date DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `conteudo` text,
  `imagem` varchar(255) DEFAULT NULL,
  `agendar_entrada` datetime DEFAULT NULL,
  `agendar_saida` datetime DEFAULT NULL,
  `stats` tinyint(4) DEFAULT NULL,
  `ordem` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_seo` (`id_pagina`),
  KEY `fk_hbrd_cms_blog_1_idx` (`id_categoria`),
  CONSTRAINT `hbrd_cms_blog_ibfk_1` FOREIGN KEY (`id_pagina`) REFERENCES `hbrd_cms_paginas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_cms_blog_ibfk_4` FOREIGN KEY (`id_categoria`) REFERENCES `hbrd_cms_blog_categorias` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_cms_blog_categorias`;
CREATE TABLE `hbrd_cms_blog_categorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `stats` int(11) DEFAULT NULL,
  `seo_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_blog_galerias`;
CREATE TABLE `hbrd_cms_blog_galerias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_blog` int(10) unsigned DEFAULT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '0',
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_blog_galerias_hbrd_cms_blog_idx` (`id_blog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_blog_galerias_fotos`;
CREATE TABLE `hbrd_cms_blog_galerias_fotos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_galeria` int(10) unsigned DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `url` mediumblob,
  `legenda` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_blog_galerias_fotos_hbrd_cms_blog_idx` (`id_galeria`),
  CONSTRAINT `fk_hbrd_cms_blog_galerias_fotos_hbrd_cms_blog1` FOREIGN KEY (`id_galeria`) REFERENCES `hbrd_cms_blog_galerias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_blog_hbrd_cms_blog_categorias`;
CREATE TABLE `hbrd_cms_blog_hbrd_cms_blog_categorias` (
  `id_blog` int(10) unsigned NOT NULL,
  `id_categoria` int(10) unsigned NOT NULL,
  KEY `id_blog` (`id_blog`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `hbrd_cms_blog_hbrd_cms_blog_categorias_ibfk_1` FOREIGN KEY (`id_blog`) REFERENCES `hbrd_cms_blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_cms_blog_hbrd_cms_blog_categorias_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `hbrd_cms_blog_categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_comissioned`;
CREATE TABLE `hbrd_cms_comissioned` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cartao` int(10) unsigned DEFAULT NULL,
  `id_estado` int(10) unsigned DEFAULT NULL,
  `id_cidade` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpf` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `rua` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numero` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complemento` text COLLATE utf8_unicode_ci,
  `bairro` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cep` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banco` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agencia` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `operacao` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conta` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `desativado` tinyint(1) DEFAULT '0',
  `possui_seguro` tinyint(1) DEFAULT NULL,
  `seguro_vencimento` date DEFAULT NULL,
  `placa` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo_veiculo` enum('carro','moto') COLLATE utf8_unicode_ci DEFAULT NULL,
  `marca` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modelo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ano` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `valor` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(22) COLLATE utf8_unicode_ci DEFAULT NULL,
  `espontaneo` tinyint(1) DEFAULT '0',
  `solicitado` tinyint(1) DEFAULT '0',
  `possui_login` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_adm_comissioned_hbrd_adm_card1_idx` (`id_cartao`),
  KEY `fk_comiss_for_estados_idx` (`id_estado`),
  KEY `fk_comiss_for_cidades_idx` (`id_cidade`),
  CONSTRAINT `fk_comiss_for_cidades` FOREIGN KEY (`id_cidade`) REFERENCES `hbrd_main_util_city` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_comiss_for_estados` FOREIGN KEY (`id_estado`) REFERENCES `hbrd_main_util_state` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_hbrd_adm_comissioned_hbrd_adm_card1` FOREIGN KEY (`id_cartao`) REFERENCES `hbrd_adm_card` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_configuracoes_gerais`;
CREATE TABLE `hbrd_cms_configuracoes_gerais` (
  `id` int(11) NOT NULL,
  `link_twitter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_blog` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_linkedin` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_google_plus` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logomarca` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone_sou_cliente` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(145) COLLATE utf8_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `atendimento_online` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_facebook` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_instagram` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_youtube` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_googlemais` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nome` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_hypnobox` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_hypnobox` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_rdstation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_rdstation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_portal_cliente` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_portal_corretor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_assistencia_tecnica` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `revenda_telefone` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `revenda_whatsapp` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_whats_vendas` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link_whats_centralatendimento` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_dashboard_visitas`;
CREATE TABLE `hbrd_cms_dashboard_visitas` (
  `date` int(11) DEFAULT NULL,
  `visitas` int(11) DEFAULT NULL,
  `tablet` int(11) DEFAULT NULL,
  `mobile` int(11) DEFAULT NULL,
  `desktop` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_departamentos`;
CREATE TABLE `hbrd_cms_departamentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(105) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `stats` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_email`;
CREATE TABLE `hbrd_cms_email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `departamento` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `termo` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_email_cms_listas`;
CREATE TABLE `hbrd_cms_email_cms_listas` (
  `id_lista` int(10) unsigned NOT NULL,
  `id_email` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_lista`,`id_email`),
  KEY `fk_hbrd_cms_email_cms_listas_hbrd_cms_email1_idx` (`id_email`),
  CONSTRAINT `fk_hbrd_cms_email_cms_listas_hbrd_cms_email1` FOREIGN KEY (`id_email`) REFERENCES `hbrd_cms_email` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hbrd_cms_email_cms_listas_hbrd_cms_listas1` FOREIGN KEY (`id_lista`) REFERENCES `hbrd_cms_listas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_especialidade`;
CREATE TABLE `hbrd_cms_especialidade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `especialidade` varchar(500) DEFAULT NULL,
  `stats` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_formularios`;
CREATE TABLE `hbrd_cms_formularios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `identificador` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `mensagem` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `script` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ws_hypnobox_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ws_rdstation_token` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_formulario_pai` int(10) DEFAULT NULL,
  `email_stats` tinyint(1) NOT NULL DEFAULT '0',
  `email_assunto` varchar(500) COLLATE utf8_danish_ci NOT NULL,
  `email_remetente` varchar(500) COLLATE utf8_danish_ci NOT NULL,
  `email_corpo` text COLLATE utf8_danish_ci NOT NULL,
  `local` enum('desk','revenda') COLLATE utf8_danish_ci NOT NULL DEFAULT 'desk',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;


DROP TABLE IF EXISTS `hbrd_cms_home_app_infos`;
CREATE TABLE `hbrd_cms_home_app_infos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo_um` varchar(255) DEFAULT NULL,
  `texto_um` text,
  `titulo_dois` varchar(255) DEFAULT NULL,
  `texto_dois` text,
  `titulo_tres` varchar(255) DEFAULT NULL,
  `texto_tres` text,
  `titulo_quatro` varchar(255) DEFAULT NULL,
  `texto_quatro` text,
  `imagem` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_home_rede_credenciada`;
CREATE TABLE `hbrd_cms_home_rede_credenciada` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) DEFAULT NULL,
  `texto` text,
  `imagem` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_listas`;
CREATE TABLE `hbrd_cms_listas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_notificacoes`;
CREATE TABLE `hbrd_cms_notificacoes` (
  `id_form` int(10) unsigned NOT NULL,
  `id_usuario` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_form`,`id_usuario`),
  KEY `fk_notificacoes_for_usuarios_idx` (`id_usuario`),
  CONSTRAINT `fk_hbrd_cms_notificacoes_hbrd_cms_formularios1` FOREIGN KEY (`id_form`) REFERENCES `hbrd_cms_formularios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notificacoes_for_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `hbrd_main_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_paginas`;
CREATE TABLE `hbrd_cms_paginas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seo_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_description` text COLLATE utf8_unicode_ci,
  `seo_keywords` text COLLATE utf8_unicode_ci,
  `seo_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_url_breadcrumbs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_pagina` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_pagina_dinamica` tinyint(1) DEFAULT '0',
  `seo_pagina_referencia` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_pagina_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_pagina_conteudo` text COLLATE utf8_unicode_ci,
  `seo_pagina_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `seo_scripts` text COLLATE utf8_unicode_ci,
  `imagem` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imagem_dois` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `interna` tinyint(1) DEFAULT '0',
  `crop_h` int(11) DEFAULT NULL,
  `crop_w` int(11) DEFAULT NULL,
  `link_conteudo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text_aux` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_rastreios_bots`;
CREATE TABLE `hbrd_cms_rastreios_bots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identificador` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_seo_acessos`;
CREATE TABLE `hbrd_cms_seo_acessos` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `id_seo` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `origem` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pais` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_medium` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_term` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_content` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dispositivo` int(11) DEFAULT NULL,
  `contato` int(11) DEFAULT NULL,
  `cadastro_hotsite` int(11) DEFAULT NULL,
  `indique_hotsite` int(11) DEFAULT NULL,
  `venda_terreno` int(11) DEFAULT NULL,
  `trabalhe_conosco` int(11) DEFAULT NULL,
  `ligacao` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_seo_acessos_hbrd_cms_paginas1_idx` (`id_seo`),
  CONSTRAINT `fk_hbrd_cms_seo_acessos_hbrd_cms_paginas1` FOREIGN KEY (`id_seo`) REFERENCES `hbrd_cms_paginas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_seo_acessos_analise`;
CREATE TABLE `hbrd_cms_seo_acessos_analise` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `id_seo` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `origem` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pais` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_medium` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_term` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_content` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dispositivo` int(11) DEFAULT NULL,
  `contato` int(11) DEFAULT NULL,
  `cadastro_hotsite` int(11) DEFAULT NULL,
  `indique_hotsite` int(11) DEFAULT NULL,
  `venda_terreno` int(11) DEFAULT NULL,
  `trabalhe_conosco` int(11) DEFAULT NULL,
  `ligacao` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_seo_acessos_hbrd_cms_paginas1_idx` (`id_seo`),
  CONSTRAINT `fk_hbrd_cms_seo_acessos_hbrd_cms_paginas11` FOREIGN KEY (`id_seo`) REFERENCES `hbrd_cms_paginas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_cms_seo_acessos_historico`;
CREATE TABLE `hbrd_cms_seo_acessos_historico` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `id_seo` int(10) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `session` varchar(45) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `origem` varchar(255) DEFAULT NULL,
  `pais` varchar(255) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `dispositivo` int(11) DEFAULT NULL,
  `contato` int(11) DEFAULT NULL,
  `cadastro_hotsite` int(11) DEFAULT NULL,
  `indique_hotsite` int(11) DEFAULT NULL,
  `venda_terreno` int(11) DEFAULT NULL,
  `trabalhe_conosco` int(11) DEFAULT NULL,
  `ligacao` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_seo_acessos_hbrd_cms_paginas1_idx` (`id_seo`),
  CONSTRAINT `fk_hbrd_cms_seo_acessos_hbrd_cms_paginas10` FOREIGN KEY (`id_seo`) REFERENCES `hbrd_cms_paginas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_cms_sobre`;
CREATE TABLE `hbrd_cms_sobre` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `texto` text,
  `imagem` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_cms_sobre_galeria`;
CREATE TABLE `hbrd_cms_sobre_galeria` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_sobre` int(10) unsigned DEFAULT NULL,
  `titulo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '0',
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_sobre_galeria_1_idx` (`id_sobre`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_sobre_galeria_fotos`;
CREATE TABLE `hbrd_cms_sobre_galeria_fotos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_galeria` int(10) unsigned DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `url` mediumblob,
  `legenda` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_cms_sobre_galeria_fotos_1_idx` (`id_galeria`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_store`;
CREATE TABLE `hbrd_cms_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(155) CHARACTER SET utf8 DEFAULT NULL,
  `cnpj` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `razao_social` varchar(155) COLLATE utf8_bin DEFAULT NULL,
  `logo` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `telefone` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `id_cidade` int(10) DEFAULT NULL,
  `id_estado` int(10) DEFAULT NULL,
  `observacoes` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `cep` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `rua` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `numero` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `complemento` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `bairro` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `telefone1` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `telefone2` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `responsavel` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `responsavel1` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `email1` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `obs` text CHARACTER SET utf8,
  `obs_docs` text COLLATE utf8_bin,
  `senha` varchar(155) CHARACTER SET utf8 DEFAULT NULL,
  `id_user` int(11) unsigned NOT NULL,
  `stats` int(1) DEFAULT '0',
  `solicitado` int(1) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `aprovacao` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `latitude` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `zoom` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `token` text COLLATE utf8_bin,
  `access_token` text COLLATE utf8_bin,
  `ip_session` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `hbrd_cms_store_has_especialidade`;
CREATE TABLE `hbrd_cms_store_has_especialidade` (
  `id_store` int(10) unsigned DEFAULT NULL,
  `id_especialidade` int(10) unsigned DEFAULT NULL,
  KEY `FK_hbrd_cms_store` (`id_store`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_store_has_planos`;
CREATE TABLE `hbrd_cms_store_has_planos` (
  `id_store` int(10) unsigned DEFAULT NULL,
  `id_plano` int(10) unsigned DEFAULT NULL,
  KEY `FK_hbrd_cms_store` (`id_store`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_cms_store_hist`;
CREATE TABLE `hbrd_cms_store_hist` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_store` int(10) unsigned DEFAULT NULL,
  `acao` text,
  `ip` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_desk_assunto`;
CREATE TABLE `hbrd_desk_assunto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_contato`;
CREATE TABLE `hbrd_desk_contato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_situacao` int(11) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(45) NOT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `id_assunto` int(11) DEFAULT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `session` varchar(45) DEFAULT NULL,
  `origem` varchar(45) DEFAULT NULL,
  `localizacao` varchar(45) DEFAULT NULL,
  `dispositivo` varchar(45) DEFAULT NULL,
  `utm` varchar(45) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  `departamento` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_id_contato` (`id`),
  KEY `fk_hbrd_desk_contato_assunto` (`id_assunto`),
  CONSTRAINT `fk_hbrd_desk_contato_assunto` FOREIGN KEY (`id_assunto`) REFERENCES `hbrd_desk_assunto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_desk_contato_situacao_historico`;
CREATE TABLE `hbrd_desk_contato_situacao_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contato` int(11) DEFAULT NULL,
  `id_situacao` int(11) DEFAULT NULL,
  `comentario` text COLLATE utf8_unicode_ci,
  `usuario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_desk_contatos_situacoes_historico_hbrd_desk_contato_idx` (`id_contato`),
  KEY `fk_hbrd_desk_contatos_situacoes_historico_hbrd_desk_situcao1` (`id_situacao`),
  CONSTRAINT `fk_hbrd_desk_contatos_situacoes_historico_hbrd_desk_contatos1` FOREIGN KEY (`id_contato`) REFERENCES `hbrd_desk_contato` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hbrd_desk_contatos_situacoes_historico_hbrd_desk_situcao1` FOREIGN KEY (`id_situacao`) REFERENCES `hbrd_desk_situacao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_emails`;
CREATE TABLE `hbrd_desk_emails` (
  `id_lista` int(11) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_lista`,`id_user`),
  KEY `fk_hbrd_desk_emails_user` (`id_user`),
  CONSTRAINT `fk_hbrd_desk_emails_user` FOREIGN KEY (`id_user`) REFERENCES `hbrd_main_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_fale_conosco`;
CREATE TABLE `hbrd_desk_fale_conosco` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_situacao` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `celular` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modalidade` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `curriculo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8_unicode_ci,
  `cidade` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `departamento` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `termo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_desk_contatos_hbrd_desk_situacoes1_idx` (`id_situacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_fale_conosco_situacoes_historico`;
CREATE TABLE `hbrd_desk_fale_conosco_situacoes_historico` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cadastro` int(10) unsigned DEFAULT NULL,
  `situacao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comentario` text COLLATE utf8_unicode_ci,
  `usuario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_seja_consultor`;
CREATE TABLE `hbrd_desk_seja_consultor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_situacao` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `celular` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `modalidade` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `curriculo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8_unicode_ci,
  `cidade` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `termo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_desk_contatos_hbrd_desk_situacoes1_idx` (`id_situacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_seja_consultor_situacoes_historico`;
CREATE TABLE `hbrd_desk_seja_consultor_situacoes_historico` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cadastro` int(10) unsigned DEFAULT NULL,
  `situacao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comentario` text COLLATE utf8_unicode_ci,
  `usuario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_desk_contatos_situacoes_historico_hbrd_desk_contato_idx` (`id_cadastro`),
  CONSTRAINT `fk_hbrd_desk_contatos_situacoes_historico_hbrd_desk_contatos11` FOREIGN KEY (`id_cadastro`) REFERENCES `hbrd_desk_seja_consultor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_situacao`;
CREATE TABLE `hbrd_desk_situacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `delete_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_desk_situacoes`;
CREATE TABLE `hbrd_desk_situacoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `id_formulario` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_backup`;
CREATE TABLE `hbrd_main_backup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `arquivo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_banners`;
CREATE TABLE `hbrd_main_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stats` int(11) DEFAULT '1',
  `ordem` int(11) DEFAULT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `descricao` text,
  `imagem_desktop` varchar(255) DEFAULT NULL,
  `imagem_mobile` varchar(255) DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `target` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `agendar_entrada` datetime DEFAULT NULL,
  `agendar_saida` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_blacklist`;
CREATE TABLE `hbrd_main_blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` longtext COLLATE utf8_unicode_ci NOT NULL,
  `update_at` datetime NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_bug`;
CREATE TABLE `hbrd_main_bug` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `erro` text COLLATE utf8_unicode_ci,
  `service` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `module` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usuario` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_categorias`;
CREATE TABLE `hbrd_main_categorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_cliente`;
CREATE TABLE `hbrd_main_cliente` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome_fantasia` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `empresa` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `cnpj` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `celular` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `telefone` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `cep` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `logradouro` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `numero` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `bairro` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `complemento` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `id_estado` int(10) unsigned DEFAULT NULL,
  `id_cidade` int(10) unsigned DEFAULT NULL,
  `nickname` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `senha` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `stats` tinyint(1) DEFAULT '1',
  `bi_usuario` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `bi_senha` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `anotacoes` text CHARACTER SET utf8,
  `registrado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `latitude` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `longitude` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_estado` (`id_estado`),
  KEY `id_cidade` (`id_cidade`),
  CONSTRAINT `hbrd_main_cliente_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `hbrd_main_util_state` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `hbrd_main_cliente_ibfk_2` FOREIGN KEY (`id_cidade`) REFERENCES `hbrd_main_util_city` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_cliente_membros`;
CREATE TABLE `hbrd_main_cliente_membros` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cliente` int(10) unsigned NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `departamento` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `telefone` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `hbrd_main_cliente_membros_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `hbrd_main_cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_cliente_modulo`;
CREATE TABLE `hbrd_main_cliente_modulo` (
  `id_cliente` int(10) unsigned NOT NULL,
  `id_modulo` int(10) unsigned NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `data_aviso_renovacao` date DEFAULT NULL,
  KEY `id_cliente` (`id_cliente`),
  KEY `id_modulo` (`id_modulo`),
  CONSTRAINT `hbrd_main_cliente_modulo_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `hbrd_main_cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_main_cliente_modulo_ibfk_2` FOREIGN KEY (`id_modulo`) REFERENCES `hbrd_main_modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_contato`;
CREATE TABLE `hbrd_main_contato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(45) NOT NULL,
  `celular` varchar(45) DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `assunto` varchar(255) DEFAULT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `index_id_contato` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_main_funcoes`;
CREATE TABLE `hbrd_main_funcoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_servico` int(10) unsigned NOT NULL,
  `nome` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `identificador` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`,`id_servico`),
  KEY `fk_tb_admin_funcoes_for_tb_admin_servicos_idx` (`id_servico`),
  CONSTRAINT `fk_tb_admin_funcoes_for_tb_admin_servicos` FOREIGN KEY (`id_servico`) REFERENCES `hbrd_main_servicos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_function_permission`;
CREATE TABLE `hbrd_main_function_permission` (
  `id_grupo` int(10) unsigned NOT NULL,
  `id_funcao` int(10) unsigned NOT NULL,
  `ler` int(11) DEFAULT NULL,
  `gravar` int(11) DEFAULT NULL,
  `excluir` int(11) DEFAULT NULL,
  `editar` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_grupo`,`id_funcao`),
  KEY `fk_tb_admin_grupos_has_tb_admin_funcoes_tb_admin_grupos_idx` (`id_grupo`),
  CONSTRAINT `fk_hbrd_main_function_permission_1` FOREIGN KEY (`id_grupo`) REFERENCES `hbrd_main_grupos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_grupos`;
CREATE TABLE `hbrd_main_grupos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stats` int(11) DEFAULT NULL,
  `nome` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_grupos_main_servicos`;
CREATE TABLE `hbrd_main_grupos_main_servicos` (
  `id_grupo` int(10) unsigned NOT NULL,
  `id_servico` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_grupo`,`id_servico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_grups_main_services`;
CREATE TABLE `hbrd_main_grups_main_services` (
  `id_grupo` int(10) unsigned NOT NULL,
  `id_servico` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_grupo`,`id_servico`),
  KEY `fk_tb_admin_grupos_has_tb_admin_servicos_tb_admin_servicos1_idx` (`id_servico`),
  CONSTRAINT `fk_tb_admin_grupos_has_tb_admin_servicos_tb_admin_grupos1` FOREIGN KEY (`id_grupo`) REFERENCES `hbrd_main_grupos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tb_admin_grupos_has_tb_admin_servicos_tb_admin_servicos1` FOREIGN KEY (`id_servico`) REFERENCES `hbrd_main_servicos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_index`;
CREATE TABLE `hbrd_main_index` (
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `grupo` int(11) DEFAULT NULL,
  `ordem` int(11) DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_integracao`;
CREATE TABLE `hbrd_main_integracao` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned DEFAULT NULL,
  `integrador_nome` varchar(500) DEFAULT NULL,
  `integrador_expira` date DEFAULT NULL,
  `token` text,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_main_integracao_1_idx` (`cliente_id`),
  CONSTRAINT `fk_hbrd_main_integracao_1` FOREIGN KEY (`cliente_id`) REFERENCES `hbrd_main_cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `hbrd_main_lancamentos`;
CREATE TABLE `hbrd_main_lancamentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_cliente` int(10) unsigned NOT NULL,
  `descricao` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo` enum('R','P') COLLATE utf8_unicode_ci NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `data_cancelamento` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_hbrd_main_lancamentos_1_idx` (`id_cliente`),
  CONSTRAINT `fk_hbrd_main_lancamentos_1` FOREIGN KEY (`id_cliente`) REFERENCES `hbrd_main_cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_lancamentos_historico`;
CREATE TABLE `hbrd_main_lancamentos_historico` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL,
  `id_lancamento` int(10) unsigned NOT NULL,
  `descricao` varchar(255) CHARACTER SET utf8 NOT NULL,
  `registrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_lancamento` (`id_lancamento`),
  CONSTRAINT `hbrd_main_lancamentos_historico_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `hbrd_main_usuarios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `hbrd_main_lancamentos_historico_ibfk_3` FOREIGN KEY (`id_lancamento`) REFERENCES `hbrd_main_lancamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_lancamento_anotacoes`;
CREATE TABLE `hbrd_main_lancamento_anotacoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_lancamento` int(10) unsigned NOT NULL,
  `id_usuario` int(10) unsigned NOT NULL,
  `conteudo` tinytext CHARACTER SET utf8 NOT NULL,
  `registrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_lancamento` (`id_lancamento`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `hbrd_main_lancamento_anotacoes_ibfk_1` FOREIGN KEY (`id_lancamento`) REFERENCES `hbrd_main_lancamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_main_lancamento_anotacoes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `hbrd_main_usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_level`;
CREATE TABLE `hbrd_main_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nivel` int(11) DEFAULT NULL,
  `acesso` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nivel_UNIQUE` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_level_permission`;
CREATE TABLE `hbrd_main_level_permission` (
  `id_nivel` int(10) unsigned NOT NULL,
  `id_funcao` int(10) unsigned NOT NULL,
  `ler` int(11) DEFAULT NULL,
  `gravar` int(11) DEFAULT NULL,
  `excluir` int(11) DEFAULT NULL,
  `editar` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_nivel`,`id_funcao`),
  KEY `fk_level_permission_for_function_idx` (`id_funcao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_log`;
CREATE TABLE `hbrd_main_log` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `usuario` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `atividade` text COLLATE utf8_unicode_ci,
  `date` datetime DEFAULT NULL,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_modulos`;
CREATE TABLE `hbrd_main_modulos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_notificacao_cliente`;
CREATE TABLE `hbrd_main_notificacao_cliente` (
  `id_notificacao` int(10) unsigned NOT NULL,
  `id_cliente` int(10) unsigned NOT NULL,
  KEY `id_notificacao` (`id_notificacao`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `hbrd_main_notificacao_cliente_ibfk_1` FOREIGN KEY (`id_notificacao`) REFERENCES `hbrd_main_notificacoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `hbrd_main_notificacao_cliente_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `hbrd_main_cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_notificacoes`;
CREATE TABLE `hbrd_main_notificacoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_categoria` int(10) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `publicado_em` date DEFAULT NULL,
  `conteudo` text,
  `agendar_entrada` datetime DEFAULT NULL,
  `agendar_saida` datetime DEFAULT NULL,
  `pop_up` tinyint(1) DEFAULT '0',
  `stats` tinyint(1) NOT NULL DEFAULT '0',
  `registrado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_permissoes`;
CREATE TABLE `hbrd_main_permissoes` (
  `id_grupo` int(10) unsigned NOT NULL,
  `id_funcao` int(10) unsigned NOT NULL,
  `ler` int(11) DEFAULT NULL,
  `gravar` int(11) DEFAULT NULL,
  `excluir` int(11) DEFAULT NULL,
  `editar` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_grupo`,`id_funcao`),
  KEY `fk_tb_admin_grupos_has_tb_admin_funcoes_tb_admin_funcoes1_idx` (`id_funcao`),
  KEY `fk_tb_admin_grupos_has_tb_admin_funcoes_tb_admin_grupos_idx` (`id_grupo`),
  CONSTRAINT `fk_tb_admin_grupos_has_tb_admin_funcoes_tb_admin_funcoes1` FOREIGN KEY (`id_funcao`) REFERENCES `hbrd_main_funcoes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_tb_admin_grupos_has_tb_admin_funcoes_tb_admin_grupos` FOREIGN KEY (`id_grupo`) REFERENCES `hbrd_main_grupos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_servicos`;
CREATE TABLE `hbrd_main_servicos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  `identificador` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_smtp`;
CREATE TABLE `hbrd_main_smtp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `autenticado` int(11) DEFAULT '0',
  `email_host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_port` int(11) DEFAULT NULL,
  `email_user` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_password` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_padrao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_mkt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token_mkt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_user_has_custom_permission`;
CREATE TABLE `hbrd_main_user_has_custom_permission` (
  `id_custom_permission` int(10) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_custom_permission`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hbrd_main_usuarios`;
CREATE TABLE `hbrd_main_usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_grupo` int(10) unsigned DEFAULT NULL,
  `id_cidade` int(10) unsigned DEFAULT NULL,
  `id_estado` int(10) unsigned DEFAULT NULL,
  `nome` varchar(155) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefone` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `usuario` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `anotacoes` text COLLATE utf8_unicode_ci,
  `code` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stats` int(11) DEFAULT NULL,
  `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_sga` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `senha_sga` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delete_at` datetime DEFAULT NULL,
  `status_sga` tinyint(1) DEFAULT '1',
  `id_nivel` int(10) unsigned DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rua` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complemento` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numero` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bairro` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apelido` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cep` varchar(455) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_usuarios_main_servicos`;
CREATE TABLE `hbrd_main_usuarios_main_servicos` (
  `id_usuario` int(10) unsigned NOT NULL,
  `id_servico` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_servico`),
  KEY `fk_admin_users_has_admin_servicos_for_tb_admin_servicos_idx` (`id_servico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_usuarios_permissoes`;
CREATE TABLE `hbrd_main_usuarios_permissoes` (
  `id_usuario` int(10) unsigned NOT NULL,
  `id_funcao` int(10) unsigned NOT NULL,
  `ler` int(11) DEFAULT NULL,
  `gravar` int(11) DEFAULT NULL,
  `excluir` int(11) DEFAULT NULL,
  `editar` int(11) DEFAULT NULL,
  `custom` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id_usuario`,`id_funcao`),
  KEY `fk_table1_tb_admin_funcoes1_idx` (`id_funcao`),
  KEY `fk_table1_tb_admin_users1_idx` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_util_city`;
CREATE TABLE `hbrd_main_util_city` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_estado` int(10) unsigned NOT NULL,
  `cidade` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`id_estado`),
  KEY `fk_tb_utils_cidades_tb_utils_estados1_idx` (`id_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `hbrd_main_util_state`;
CREATE TABLE `hbrd_main_util_state` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `estado` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `uf` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 2024-03-11 12:27:40
