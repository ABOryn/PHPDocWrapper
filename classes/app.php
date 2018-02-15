<?php

class App
{
    const DEFAULT_CONFIG = [
        'title' => 'Документация PHP проекта', // поддерживаются html-теги
        'template' => 'clear,checkstyle',
        'include' => [
            'files' => [],
            'dirs' => [],
        ],
        'exclude' => [
            'files' => [],
            'dirs' => [],
        ],
    ];

    private $config;
    private $source_path;
    private $destination_path;

    /**
     * Конструктор класса.
     *
     * @param string $src_path Директория с документируемыми скриптами
     * @param string $destination_path Директория назначения
     *
     * @throws "Директория $src_path не найдена или установлен запрет на чтение"
     * @throws "Директория $destination_path не может быть создана"
     */
    public function __construct($src_path, $destination_path)
    {
        // проверка директории с документируемыми скриптами
        if (!is_readable($src_path) || !is_dir($src_path)) {
            Console::fatalError("Директория \33[33m$src_path\33[m не найдена или установлен запрет на чтение.");
        }
        $this->source_path = realpath($src_path);

        // проверка директории назначения
        ($destination_path != '') ?: $destination_path = 'output/' . time() . "_" . basename($this->source_path);
        if (!file_exists($destination_path) || !is_dir($destination_path)) {
            if (!mkdir($destination_path, 0775, true)) {
                Console::fatalError("Директория \33[33m$destination_path\33[m не может быть создана.");
            }
            Console::info("Директория \33[33m" . realpath($destination_path) . "\33[m успешно создана.");
        }
        $this->destination_path = realpath($destination_path);

        // подгрузка конфигов из директории с документируемыми скриптами
        $config = [];
        if (file_exists("$this->source_path/config_doc.php")) {
            /** @noinspection PhpIncludeInspection */
            $config = include "$this->source_path/config_doc.php";
        } elseif (file_exists("$this->source_path/config/doc.php")) {
            /** @noinspection PhpIncludeInspection */
            $config = include "$this->source_path/config_doc.php";
        }
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
    }

    /**
     * Запуск процесса генерации документации
     */
    public function run()
    {
        $config_file_name = $this->prepareConfigFile();

//        $cmd = "phpdoc";
//        if ($config_file_name != '') {
//            $cmd .= " -c \"$config_file_name\"";
//        }

//        $cmd .= " --template=\"" . implode(',',
//                (isset($config['template'])) ? $config['template'] : self::DEFAULT_CONFIG['template']
//            ) . "\"";
//

        if (file_exists($config_file_name)) {
//            unlink($config_file_name); ///////////////////////////////////////////////////////////////////////////////
            Console::info("Временный файл конфигурации \33[33m$config_file_name\33[m успешно удалён.");
        }
    }

    /**
     * Создаёт файл с конфигурацией для phpDocumentor
     *
     * @return string
     */
    private function prepareConfigFile()
    {
        $config_file_path = ROOT_DIR . "tmp" . DIRECTORY_SEPARATOR;
        $config_file_name = "tmp.xml";

        if (!is_readable($config_file_path) || !is_dir($config_file_path)) {
            if (!mkdir($config_file_path, 0775, true)) {
                Console::fatalError("Директория временных файлов \33[33m$config_file_path\33[m не может быть создана.");
            }
            Console::info("Директория \33[33m$config_file_path\33[m для временных файлов успешно создана");
        } elseif (file_exists($config_file_path . $config_file_name)) {
            unlink($config_file_path . $config_file_name);
            Console::info("Найденный временный файл конфигурации \33[33m$config_file_path$config_file_name\33[m, "
                . "оставшийся от предыдущего запуска, успешно удалён.");
        }


        $xml = new DOMDocument('1.0', 'UTF-8');
//        $xml->load(ROOT_DIR . "config_template.xml", LIBXML_NOBLANKS);
        $xml->formatOutput = true; // иначе будет в одну строку ////////////////////////////////////////////////////////

        // корневой элемент xml-документа
        $root = $xml->createElement('phpdoc');
        $xml->appendChild($root);
//        $root = $xml->getElementsByTagName('phpdoc');
//        if ($root->length !== 1) {
//            Console::fatalError("Ошибка в структуре шаблона файла конфигурации \33[33m"
//                . ROOT_DIR . "config_template.xml\33[m.");
//        }
//        $root = $root->item(0);

        // настройки логирования phpDoc (по умолчанию ничего не логируется)
        $logging = $xml->createElement('logging');
        $logging = $root->appendChild($logging);
        $level = $xml->createElement('level');
        $level->nodeValue = "debug";
        $logging->appendChild($level);
        $paths = $logging->appendChild($xml->createElement('paths'));
        $default = $xml->createElement('default');
        $default->nodeValue = "{APP_ROOT}/log/" . basename($this->destination_path) . ".log";
        $paths->appendChild($default);
        $errors = $xml->createElement('errors');
        $errors->nodeValue = "{APP_ROOT}/log/" . basename($this->destination_path) . "_errors.log";
        $paths->appendChild($errors);

        // шаблон
        $transformations = $xml->createElement('transformations');
        $root->appendChild($transformations);
        if (!isset($this->config['template']) || empty(trim($this->config['template']))) {
            $this->config['template'] = "clear";
        }
        foreach (explode(',', $this->config['template']) as $template_name) {
            $template = $xml->createElement('template');
            $template->setAttribute('name', $template_name);
            $transformations->appendChild($template);
        }


        // директория назначения
        $transformer = $xml->createElement('transformer');
        $root->appendChild($transformer);
        $target = $xml->createElement('target');
        $target->nodeValue = trim($this->destination_path);
        $transformer->appendChild($target);


//        // убиваем коментарии (не обязательное действие, можно закоментировать/удалить)
//        $xpath = new DOMXPath($xml);
//        foreach ($xpath->query('//comment()') as $comment) {
//            $comment->parentNode->removeChild($comment);
//        }

        $file_size = $xml->save($config_file_path . $config_file_name, LIBXML_NOBLANKS);
        Console::info("Временный файл конфигурации \33[33m$config_file_path$config_file_name\33[m успешно создан "
            . "(размер \33[33m$file_size\33[m байт).");

        return $config_file_path . $config_file_name;
    }
}
