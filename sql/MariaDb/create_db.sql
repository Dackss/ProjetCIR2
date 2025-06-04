CREATE USER 'projetcir2'@'localhost' IDENTIFIED BY 'isen';

CREATE DATABASE projetcir2 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

GRANT ALL PRIVILEGES ON projetcir2.* TO 'projetcir2'@'localhost';

FLUSH PRIVILEGES;
