CREATE TABLE cities
(
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Egyedi rekordazonosító',
    countryName    VARCHAR(100)   NOT NULL COMMENT 'Ország neve',
    countryOsmId   INT UNSIGNED DEFAULT NULL COMMENT 'Ország OpenStreetMap azonosítója',
    cityName       VARCHAR(100)   NOT NULL COMMENT 'Város neve',
    cityOsmId      INT UNSIGNED DEFAULT NULL COMMENT 'Város OpenStreetMap azonosítója',
    latitude       DECIMAL(10, 7) NOT NULL COMMENT 'Szélességi koordináta',
    longitude      DECIMAL(10, 7) NOT NULL COMMENT 'Hosszúsági koordináta',
    cronExpression VARCHAR(100) DEFAULT NULL COMMENT 'Cron kifejezés az ütemezéshez',
    createdAt      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP COMMENT 'Rekord létrehozásának ideje',
    updatedAt      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Rekord utolsó módosításának ideje',
    INDEX          idx_country (countryName),
    INDEX          idx_city (cityName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Városok adatai';

CREATE TABLE weather_data
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Egyedi rekordazonosító',
    city_id     INT   NOT NULL COMMENT 'A város azonosítója',
    temperature FLOAT NOT NULL COMMENT 'Rögzített hőmérséklet Celsius fokban',
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Rekord létrehozásának ideje'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT = 'Időjárási adatok';

