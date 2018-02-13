<?php

class App
{
    const DEFAULT_CONFIG = [
        'templates' => [
            'clear',
        ],
        'include' => [
            'files' => [],
            'dirs' => [],
        ],
        'exclude' => [
            'files' => [],
            'dirs' => [],
        ],
    ];

    public static function run($src_path, $destination_path)
    {
        if (!is_readable($src_path) || !is_dir($src_path)) {
            Console::fatalError("Директория \33[33m$src_path\33[0m не найдена или установлен запрет на чтение");
        }

        ($destination_path != '') ?: $destination_path = 'output/' . time() . "_" . basename($src_path);
        if (!file_exists($destination_path) || !is_dir($destination_path)) {
            if (!mkdir($destination_path, 0775, true)) {
                Console::fatalError("Директория \33[33m$destination_path\33[0m не может быть создана");
            }
            Console::info("Директория \33[33m" . realpath($destination_path) . "\33[0m успешно создана");
        }

        $src_path = realpath($src_path);
        $config = [];
        if (file_exists("$src_path/config_doc.php")) {
            /** @noinspection PhpIncludeInspection */
            $config = include "$src_path/config_doc.php";
        } elseif (file_exists("$src_path/config/doc.php")) {
            /** @noinspection PhpIncludeInspection */
            $config = include "$src_path/config_doc.php";
        }

        $cmd = "phpdoc";

        // todo нет варианта, где может быть не задан шаблон !!! В ФУНКЦИЮ !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $cmd .= " --template=\"" . implode(',',
            (isset($config['template'])) ? $config['template'] : self::DEFAULT_CONFIG['template']
        ) . "\"";

    }
}
