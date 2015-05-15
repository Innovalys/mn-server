-- MySQL Script generated by MySQL Workbench
-- 04/07/15 10:51:05
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema manga-network
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema manga-network
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `manga-network` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `manga-network` ;

-- -----------------------------------------------------
-- Table `manga-network`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(45) NOT NULL,
  `password` VARCHAR(45) NOT NULL,
  `mail` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NULL,
  `credentials` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`manga`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`manga` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(45) NULL,
  `chapter_nb` INT NULL,
  `source_API` VARCHAR(45) NOT NULL,
  `source_URL` VARCHAR(255) NOT NULL,
  `source_ID` VARCHAR(45) NOT NULL,
  `update_date` DATETIME NULL,
  `release_date` DATETIME NULL,
  `completed` TINYINT(1) NULL,
  `description` TEXT NULL,
  `cover` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`manga_chapter`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`manga_chapter` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source_ID` VARCHAR(255) NULL,
  `page_start` INT NULL,
  `page_nb` INT NULL,
  `title` VARCHAR(45) NULL,
  `manga_id` INT NOT NULL,
  `loaded` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  INDEX `fk_manga_chapter_manga1_idx` (`manga_id` ASC),
  CONSTRAINT `fk_manga_chapter_manga1`
    FOREIGN KEY (`manga_id`)
    REFERENCES `manga-network`.`manga` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`manga_page`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`manga_page` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `page_nb` INT NULL,
  `link` VARCHAR(512) NULL,
  `manga_chapter_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_manga_page_manga_idx` (`manga_chapter_id` ASC),
  CONSTRAINT `fk_manga_page_manga`
    FOREIGN KEY (`manga_chapter_id`)
    REFERENCES `manga-network`.`manga_chapter` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`genre`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`genre` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`user_has_manga`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`user_has_manga` (
  `user_id` INT NOT NULL,
  `manga_id` INT NOT NULL,
  `favoris` TINYINT(1) NULL,
  `update_date` DATETIME NULL,
  `note` SMALLINT NULL,
  `page_cur` INT NULL,
  PRIMARY KEY (`user_id`, `manga_id`),
  INDEX `fk_user_has_manga_manga1_idx` (`manga_id` ASC),
  INDEX `fk_user_has_manga_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_has_manga_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `manga-network`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_has_manga_manga1`
    FOREIGN KEY (`manga_id`)
    REFERENCES `manga-network`.`manga` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`genre_has_manga`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`genre_has_manga` (
  `genre_id` INT NOT NULL,
  `manga_id` INT NOT NULL,
  PRIMARY KEY (`genre_id`, `manga_id`),
  INDEX `fk_genre_has_manga_manga1_idx` (`manga_id` ASC),
  INDEX `fk_genre_has_manga_genre1_idx` (`genre_id` ASC),
  CONSTRAINT `fk_genre_has_manga_genre1`
    FOREIGN KEY (`genre_id`)
    REFERENCES `manga-network`.`genre` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_genre_has_manga_manga1`
    FOREIGN KEY (`manga_id`)
    REFERENCES `manga-network`.`manga` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`author`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`author` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`author_has_manga`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`author_has_manga` (
  `author_id` INT NOT NULL,
  `manga_id` INT NOT NULL,
  PRIMARY KEY (`author_id`, `manga_id`),
  INDEX `fk_author_has_manga_manga1_idx` (`manga_id` ASC),
  INDEX `fk_author_has_manga_author1_idx` (`author_id` ASC),
  CONSTRAINT `fk_author_has_manga_author1`
    FOREIGN KEY (`author_id`)
    REFERENCES `manga-network`.`author` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_author_has_manga_manga1`
    FOREIGN KEY (`manga_id`)
    REFERENCES `manga-network`.`manga` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manga-network`.`user_has_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manga-network`.`user_has_user` (
  `user_id_following` INT NOT NULL,
  `user_id_followed` INT NOT NULL,
  PRIMARY KEY (`user_id_following`, `user_id_followed`),
  INDEX `fk_user_has_user_user2_idx` (`user_id_followed` ASC),
  INDEX `fk_user_has_user_user1_idx` (`user_id_following` ASC),
  CONSTRAINT `fk_user_has_user_user1`
    FOREIGN KEY (`user_id_following`)
    REFERENCES `manga-network`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_has_user_user2`
    FOREIGN KEY (`user_id_followed`)
    REFERENCES `manga-network`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
