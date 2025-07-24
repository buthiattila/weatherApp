# Időjárás Adatgyűjtő Projekt

Ez a projekt városokhoz tartozó időjárási adatok lekérését és mentését végzi. Az adatok lekérdezhetők JSON és Prometheus
formátumban, valamint lehetőség van városok adatainak módosítására és törlésére.

---

## Telepítés

1. Telepítsd a szükséges PHP csomagokat a Composer segítségével:

```bash
composer install
```

2. Hozd létre az adatbázist az SQL script lefuttatásával:

```sql
source dbInstall.sql;
```

3. Végezd el az alapvető beállításokat:
   1. environments alatt végezd el az adatbázis és a szükséges logolási beállításokat
   2. az index.php fájlban állítsd megfelelőre az Environment::init paraméterét

## Cron job

A városokhoz tartozó időjárási adatok lekérését és mentését az alábbi cron végpont végzi:

```bash
cron/city/fetchAndStoreWeatherData
```

Ezt időzített feladatként (cron job) érdemes futtatni a kívánt gyakorisággal.

## API végpontok

Az API JSON formátumban kommunikál, az elérhető végpontok:

| Művelet                                                | HTTP metódus | URL                    | Leírás                                                  | Input                                  |
| ------------------------------------------------------ | ------------ | ---------------------- | ------------------------------------------------------- | -------------------------------------- |
| Városok időjárási adatainak lekérése                   | GET          | `api/city/data`        | A monitorozott városok időjárási adatainak lekérése     | -                                      |
| Legfrissebb hőmérsékleti adatok Prometheus formátumban | GET          | `api/city/metrics`     | Hőmérsékleti adatok Prometheus kompatibilis formátumban | -                                      |
| Város adatainak módosítása                             | PUT          | `api/city/update/{id}` | Egy város beállításainak frissítése                     | JSON: `{ "frequency": "*/5 * * * *" }` |
| Város törlése                                          | DELETE       | `api/city/delete/{id}` | Egy város törlése az adatbázisból                       | -                                      |

## Használati példa

Városok időjárási adatainak lekérése JSON-ban

```bash
curl -X GET https://yourdomain.com/api/city/data
```

Város adatainak módosítása:

```bash
curl -X PUT https://yourdomain.com/api/city/update/1 \
-H "Content-Type: application/json" \
-d '{"frequency": "*/5 * * * *"}'
```

Város törlése:

```bash
curl -X DELETE https://yourdomain.com/api/city/delete/1
```

## Licenc

Ez a projekt az MIT licenc alatt érhető el. Részletek a LICENSE fájlban.