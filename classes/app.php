<?php
/**
 * Основной функционал приложения
 *
 * @author ABOryn
 */

/**
 * Класс App
 */
class App
{
    /** Константа с параметрами по умолчанию */
    const DEFAULT_CONFIG = [
        'title' => 'Документация PHP проекта',
        'encoding' => 'utf8',
        'extensions' => 'php', // перечисление через запятую
        'templates' => ROOT_DIR . 'templates/ru-responsive-twig/',
//        'templates' => 'responsive-twig', // возможно использовать сразу несколько, перечислив через запятую
        'include' => [ // все пути считаются относительными, относительно директории документируемого проекта
            'files' => [],
            'dirs' => [],
        ],
        'exclude' => [ // все пути считаются относительными, относительно директории документируемого проекта
            'files' => [],
            'dirs' => [],
        ],
    ];

    /** @var array $config параметры */
    private $config;

    /** @var bool|string $source_path директория с документируемыми скриптами */
    private $source_path;

    /** @var bool|string $destination_path директория назначения, куда будут добавлены результаты работы */
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
            $config = include "$this->source_path/config/doc.php";
        }
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
    }

    /**
     * Запуск процесса генерации документации
     */
    public function run()
    {
        $config_file_name = $this->prepareConfigFile();

        $cmd = ROOT_DIR . "vendor/bin/phpdoc -c \"$config_file_name\"";

        Console::info("Запуск phpDoc:");
        passthru($cmd);
        Console::info("Завершение phpDoc.");

        if (file_exists($config_file_name)) {
//            unlink($config_file_name);
            Console::info("Временный файл конфигурации \33[33m$config_file_name\33[m успешно удалён.");
        }
    }

    /**
     * Создаёт xml-файл с конфигурацией для phpDocumentor
     *
     * @return string имя xml-файла с конфигурацией
     *
     * @todo разобраться с логированием работы phpDoc
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
//        $xml->formatOutput = true; // без этой настройки будет создан xml-документ в одну строку

        // корневой элемент xml-документа
        $root = $xml->appendChild($xml->createElement('phpdoc'));

        // заголовок (логотип/текст), определяющий документируемый проект
        $title = $root->appendChild($xml->createElement('title'));
        $title->nodeValue = $this->config['title'];

        // параметры парсера
        $parser = $root->appendChild($xml->createElement('parser'));
        $encoding = $parser->appendChild($xml->createElement('encoding'));
        $encoding->nodeValue = $this->config['encoding'];
        $extensions = $parser->appendChild($xml->createElement('extensions'));
        foreach (explode(',', $this->config['extensions']) as $extension_item) {
            $extension = $extensions->appendChild($xml->createElement('extension'));
            $extension->nodeValue = $extension_item;
        }

        // директория назначения
        $transformer = $root->appendChild($xml->createElement('transformer'));
        $target = $transformer->appendChild($xml->createElement('target'));
        $target->nodeValue = trim($this->destination_path);

        // шаблон
        $transformations = $root->appendChild($xml->createElement('transformations'));
        foreach (explode(',', $this->config['templates']) as $template_name) {
            $template = $xml->createElement('template');
            $transformations->appendChild($template);
            $template->setAttribute('name', trim($template_name));
        }

//        // вывод в консоль более подробного лога действий phpDoc
//        $logging = $root->appendChild($xml->createElement('logging'));
//        $level = $logging->appendChild($xml->createElement('level'));
//        $level->nodeValue = "debug";

        // уточнение конкретных документируемых файл-скриптов (директорий)
        $files = $root->appendChild($xml->createElement('files'));
        if ((
                !empty($this->config['include'])
                && (!empty($this->config['include']['files']) || !empty($this->config['include']['dirs']))
            ) || (
                !empty($this->config['exclude'])
                && (!empty($this->config['exclude']['files']) || !empty($this->config['exclude']['dirs']))
            )
        ) {
//            Console::info("!!!");
            if (!empty($this->config['include'])) {
                if (isset($this->config['include']['files']) && (is_array($this->config['include']['files']))) {
                    foreach ($this->config['include']['files'] as $file_name) {
                        $file = $files->appendChild($xml->createElement('file'));
                        $file->nodeValue = $this->source_path . DIRECTORY_SEPARATOR . trim($file_name);
                    }
                }
                if (isset($this->config['include']['dirs']) && (is_array($this->config['include']['dirs']))) {
                    foreach ($this->config['include']['dirs'] as $dir_name) {
                        $dir = $files->appendChild($xml->createElement('directory'));
                        $dir->nodeValue = $this->source_path . DIRECTORY_SEPARATOR . trim($dir_name);
                    }
                }
            }
            if (!empty($this->config['exclude'])) {
                if (isset($this->config['exclude']['files']) && (is_array($this->config['exclude']['files']))) {
                    foreach ($this->config['exclude']['files'] as $file_name) {
                        $file = $files->appendChild($xml->createElement('ignore'));
                        $file->nodeValue = $this->source_path . DIRECTORY_SEPARATOR . trim($file_name);
                    }
                }
                if (isset($this->config['exclude']['dirs']) && (is_array($this->config['exclude']['dirs']))) {
                    foreach ($this->config['exclude']['dirs'] as $dir_name) {
                        $dir = $files->appendChild($xml->createElement('ignore'));
                        $dir->nodeValue = $this->source_path . DIRECTORY_SEPARATOR . trim($dir_name) . "/*";
                    }
                }
            }
        } else {
            $dir = $files->appendChild($xml->createElement('directory'));
            $dir->nodeValue = $this->source_path;
        }


//        // убиваем коментарии
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
