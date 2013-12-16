  DROP TABLE IF EXISTS `phone_details`;
    CREATE TABLE `phone_details` (
      `phone_number` VARCHAR(20) NOT NULL,
      `status` TINYINT(1) DEFAULT NULL,
      `telco` VARCHAR(20) DEFAULT NULL,
      `customer_name` VARCHAR(200) DEFAULT NULL,
      `last_payment_date` DATE DEFAULT NULL,
      `last_payment_amount` DECIMAL(5,2) DEFAULT NULL,
      PRIMARY KEY (`phone_number`)
    );
    DELETE FROM `phone_details`;
    INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('60-165586780',        100,    'DIGI', 'Benjamin Law', '2013-09-18',   100.5555555765);
    INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('60-123691200',        101,    'MAXIS',        'Peter Tan',    '2013-09-18',   25);
    INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('60-198550000',        100,    'CELCOM',       'Abdullah Hukum',       '2013-09-18',   50);
    INSERT INTO `phone_details` (`phone_number`, `status`, `telco`, `customer_name`, `last_payment_date`, `last_payment_amount`) VALUES('65-27508888', 300000000,    'SINGTEL',      'Richard Ooi',  '2013-09-18',   60);