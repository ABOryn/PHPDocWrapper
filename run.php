#!/usr/bin/php
<?php
/**
 * Для запуска в ОС Linux, FreeBSD, MacOS и т.п. (в ОС семейства Windows такой возможности нет) можно использовать,
 * например из корня проекта, команду "./run.php".
 *
 * Обязательные условия:
 * 0. должен быть установлен пкет php-cli (дял PHP5.6)
 * 1. в первой строке настоящего скрипта должен быть текст "#!/usr/bin/php" или "#!/usr/local/bin/php"
 * 2. настоящему файл-скрипту должны быть присвоены права на исполнение (например 775)
 *
 * @todo Добавить обработчик предупреждений (warnings)
 */


echo "\n";
require 'vendor/autoload.php';


if (php_sapi_name() != 'cli') {
    exit("Скрипт может быть исполнен только из командной строки (cmd, bash, sh и т.д.)\n\n");
}

if (!extension_loaded('xml')) {
    Console::fatalError("Не загружено php-расширение \33[33mxml\33[m, необходимое для работы \33[33mSimpleXML\33[m.");
}

if (!isset($argc)) {
    Console::fatalError("Неверные настройки PHP - для корректной работы скрипта необходимо, чтобы значение опции "
        . "\33[33mregister_argc_argv\33[0m в \33[4mphp.ini\33[0m было установлено как \33[33m1\33[0m.");
}

if ($argc < 2) {
    Console::fatalError("Не задана директория документирумого проекта.");
}

if ($argc > 3) {
    Console::fatalError("Параметры командной строки заданы не верно.");
}


define('ROOT_DIR', realpath(__DIR__) . DIRECTORY_SEPARATOR);
//echo ROOT_DIR;

$src_path = $argv[1];
$destination_path = ($argc < 3) ? '' : $argv[2];
$app = new App($src_path, $destination_path);
$app->run();

echo "\n\n";
