# Projecte PHP CRUD (DWES) — CO

CRUD en POO sobre la base de dades `CO`: clients, botigues i enviaments, amb login per sessió. El codi
segueix l'enunciat; aquí només s'anoten les diferències que hi ha hagut.

## Diferències respecte de l'enunciat

| # | A l'enunciat | En aquest projecte | Per què |
| --- | --- | --- | --- |
| 1 | `c:\temp\config.db` | `config.db` a l'arrel del projecte | La ruta és de Windows. La ubicació es passa a `Database::connectDB()`, així que n'hi ha prou que sigui al costat del projecte. |
| 2 | Base de dades `co` | Base de dades `CO` | MariaDB no pot tenir `co` i `CO` alhora: InnoDB compara els noms de les claus foranes sense distingir majúscules (error 121). Amb la majúscula coincideix amb els scripts. |
| 3 | — | `"config": { "platform": { "php": "8.2.12" } }` a `composer.json` | Sense el pin, Composer resol dependències per al PHP amb què s'executa i no per al que després serveix l'aplicació. Va passar amb Composer a 8.5 resolent `symfony/*` 8.1 i l'aplicació caient a l'arrencar sobre el PHP 8.2 de XAMPP. Amb el pin, el mateix `vendor/` funciona en 8.2 i en versions posteriors. |
| 4 | `require` de cada classe | Autoload PSR-4 de Composer (`models\` → `src/models/`, `config\` → `src/config/`) | És l'extra de Composer que demana l'enunciat. |

El pin és en 8.2 perquè és el sostre real de XAMPP: la seva darrera versió encara porta 8.2.12, encara
que PHP vagi per 8.4 i 8.5. Tot el projecte compila i s'executa en aquesta versió.

## Altres decisions menors

- Els camps opcionals de la base de dades (`state_province`, telèfon, adreça) es tracten com a `null`,
  no com a cadena buida, perquè la fitxa del client no mostri cel·les amb espais.
- Les províncies es guarden amb el codi de dues lletres; la fitxa fa el lookup a `Country::options()`
  per ensenyar-ne el nom.
- El model no tria el missatge d'error de la vista: fa `rollback()` i relança l'excepció, i la pàgina
  decideix què ensenya. El cas 1451 (client amb comandes o enviaments) es tracta a part perquè és
  habitual i no ha de trencar la pàgina.

`README.antic.md` és la versió llarga anterior, amb els passos de posada en marxa i les notes per a
Laragon.

## Estructura

```
Projecte/
├── index.php                  menú principal
├── composer.json              PSR-4 + faker + carbon
├── config.db                  configuració de conexió (no versionar)
├── config.db.example          plantilla
├── src/
│   ├── config/
│   │   ├── config.php         BASE_PATH / BASE_URL
│   │   └── Database.php       conexió mysqli + transacció
│   ├── models/
│   │   ├── Model.php          capa base: all(), save(), destroy()
│   │   ├── Customer.php       model de customers
│   │   ├── Country.php        model de countries
        ├── Store.php          model de stores
│   │   └── Shipment.php       model de shipments
│   └── html/
│       ├── includes/          header, nav, footer, auth
│       ├── Login/             formulari de sessió
│       ├── Customers/         vistes del CRUD
│       ├── Shipments/
│       └── Stores/
└── vendor/                    dependencies (no versionar)
```