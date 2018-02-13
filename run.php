#!/usr/bin/php
<?php
/*
 * Для запуска в ОС Linux, FreeBSD, MacOS и т.п. (в ОС семейства Windows такой возможности нет) можно использовать,
 * например из корня проекта, команду "./run.php".
 *
 * Обязательные условия:
 * 0. должен быть установлен пкет php-cli (дял PHP5.6)
 * 1. в первой строке настоящего скрипта должен быть текст "#!/usr/bin/php" или "#!/usr/local/bin/php"
 * 2. настоящему файл-скрипту должны быть присвоены права на исполнение (например 775)
 */


require 'vendor/autoload.php';


if (php_sapi_name() != 'cli') {
    exit("Скрипт может быть исполнен только из командной строки (cmd, bash, sh и т.д.)\n\n");
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

$src_path = $argv[1];
$destination_path = ($argc < 3) ? '' : $argv[2];
App::run($src_path, $destination_path);

echo "\n\n";
