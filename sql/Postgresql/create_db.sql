CREATE ROLE projetcir2 WITH LOGIN PASSWORD 'isen';

CREATE DATABASE "projetcir2"
    WITH OWNER = "projetcir2"
    ENCODING = 'UTF8'
    LC_COLLATE = 'C.utf8'
    LC_CTYPE = 'C.utf8'
    TEMPLATE = template0;

GRANT ALL PRIVILEGES ON DATABASE "projetcir2" TO "projetcir2";
