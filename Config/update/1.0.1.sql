# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- customer_forbidden_password
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `customer_forbidden_password`;

CREATE TABLE `customer_forbidden_password`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `customer_id` INTEGER NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fi_customer_forbidden_password_customer_id` (`customer_id`),
    CONSTRAINT `fk_customer_forbidden_password_customer_id`
        FOREIGN KEY (`customer_id`)
            REFERENCES `customer` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
