--  creates the user and database schema for SLF

CREATE USER 'slf'@'localhost' IDENTIFIED BY  'slfmdp';

GRANT USAGE ON * . * TO  'slf'@'localhost' IDENTIFIED BY  'slfmdp' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

CREATE DATABASE `slf` /*!40100 DEFAULT CHARACTER SET utf8 */ ;

GRANT ALL PRIVILEGES ON  `slf` . * TO  'slf'@'localhost';
