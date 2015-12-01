-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mar 01 Décembre 2015 à 22:57
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `galerieimagesbd`
--

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Comment` varchar(150) NOT NULL,
  `IdPicture` int(11) NOT NULL,
  `IdAuthor` int(11) NOT NULL,
  `TimeOfPost` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IdPicture` (`IdPicture`),
  KEY `IdAuthor` (`IdAuthor`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `journalconnexions`
--

CREATE TABLE IF NOT EXISTS `journalconnexions` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `connectionTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IdUser` int(11) NOT NULL,
  `IpAddress` varchar(50) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `IdUser` (`IdUser`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Contenu de la table `journalconnexions`
--

INSERT INTO `journalconnexions` (`Id`, `connectionTime`, `IdUser`, `IpAddress`) VALUES
(2, '2015-12-01 18:34:29', 5, '127.0.0.1'),
(4, '2015-12-01 18:41:56', 5, '127.0.0.1'),
(11, '2015-12-01 21:54:28', 5, '127.0.0.1'),
(13, '2015-12-01 21:56:24', 5, '127.0.0.1'),
(15, '2015-12-01 21:56:32', 5, '127.0.0.1');

-- --------------------------------------------------------

--
-- Structure de la table `pictures`
--

CREATE TABLE IF NOT EXISTS `pictures` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `GUID` varchar(400) NOT NULL,
  `IdOwner` int(11) NOT NULL,
  `Titre` varchar(20) NOT NULL,
  `datePublication` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`),
  KEY `IdOwner` (`IdOwner`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(15) NOT NULL,
  `Password` varchar(30) NOT NULL,
  `IsAdmin` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`Id`, `Username`, `Password`, `IsAdmin`) VALUES
(5, 'admin', 'admin', 1);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `FKCommentsAuthor` FOREIGN KEY (`IdAuthor`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FKCommentsPictures` FOREIGN KEY (`IdPicture`) REFERENCES `pictures` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `journalconnexions`
--
ALTER TABLE `journalconnexions`
  ADD CONSTRAINT `journalconnexions_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `pictures`
--
ALTER TABLE `pictures`
  ADD CONSTRAINT `FKPictures` FOREIGN KEY (`IdOwner`) REFERENCES `users` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
