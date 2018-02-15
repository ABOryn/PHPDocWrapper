<?php

/**
 * Класс для работы с командной строкой (консолью)
 *
 * @todo добавить вывод предупреждений (warnings)
 */
class Console
{
    public static function fatalError($message)
    {
        exit("\33[1m\33[31mКРИТИЧЕСКАЯ ОШИБКА!\33[m\n$message\n\n\n");
    }

    public static function error($message)
    {
        echo "\33[1m\33[31mОШИБКА:\33[m $message\n";
    }

    public static function info($message)
    {
        echo "\33[1m\33[34mИНФО:\33[m $message\n";
    }
}
