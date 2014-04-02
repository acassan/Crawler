-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Serveur: db518842993.db.1and1.com
-- Généré le : Mercredi 02 Avril 2014 à 12:27
-- Version du serveur: 5.1.73
-- Version de PHP: 5.3.3-7+squeeze19
-- 
-- Base de données: `db518842993`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `dictionary`
-- 

CREATE TABLE `dictionary` (
  `word` varchar(30) CHARACTER SET latin1 NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  `websites` text CHARACTER SET latin1 NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `directory`
-- 

CREATE TABLE `directory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET latin1 NOT NULL,
  `url` text CHARACTER SET latin1 NOT NULL,
  `crawler_id` varchar(32) NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `directory_page`
-- 

CREATE TABLE `directory_page` (
  `directory_id` int(10) unsigned NOT NULL,
  `page` varchar(32) CHARACTER SET latin1 NOT NULL,
  `url` varchar(120) CHARACTER SET latin1 NOT NULL,
  `links_found` int(10) unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`directory_id`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `website`
-- 

CREATE TABLE `website` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) NOT NULL,
  `url` varchar(120) NOT NULL,
  `game` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `forum` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `directories` text NOT NULL,
  `ranking_jac` smallint(5) unsigned DEFAULT NULL,
  `jac_id` smallint(5) unsigned DEFAULT NULL,
  `jac_description` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=959 DEFAULT CHARSET=utf8 AUTO_INCREMENT=959 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `website_dictionary`
-- 

CREATE TABLE `website_dictionary` (
  `website_id` int(10) unsigned NOT NULL,
  `word` varchar(30) CHARACTER SET latin1 NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`website_id`,`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `website_to_verify`
-- 

CREATE TABLE `website_to_verify` (
  `id` varchar(32) CHARACTER SET latin1 NOT NULL,
  `url` varchar(150) CHARACTER SET latin1 NOT NULL,
  `verified` int(1) NOT NULL DEFAULT '0',
  `directory` int(10) unsigned NOT NULL,
  `updatedAt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
