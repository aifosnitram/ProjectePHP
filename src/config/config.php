<?php
/**
 * Configuració general de l'aplicació.
 *
 * BASE_PATH: ruta absoluta del projecte al servidor.
 * BASE_URL:  ruta relativa del projecte respecte del document root web.
 *
 * BASE_URL es calcula a partir de la URL de la petició (SCRIPT_NAME) i no de
 * les rutes del disc: si el projecte s'hi arriba per un enllaç simbòlic (per
 * exemple htdocs/projecte -> ~/Documents/SER/Projecte), realpath() retorna la
 * ruta real i la comparació amb el document root fallaria.
 */
define('BASE_PATH', dirname(__DIR__, 2));

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

// Els fitxers públics viuen sota /src/…, així que el que hi ha abans és l'arrel.
$srcPosition = strpos($scriptName, '/src/');

if ($srcPosition === false) {
    // Sense petició web (CLI, cron) no hi ha URL base.
    define('BASE_URL', '');
} else {
    define('BASE_URL', rtrim(substr($scriptName, 0, $srcPosition), '/'));
}
