<?php
/**
 * Работа с консолью
 *
 * @author ABOryn
 */

/**
 * Класс для работы с командной строкой (консолью)
 *
 * @todo добавить вывод предупреждений (warnings)
 */
class Console
{
    /**
     * Вывод сообщения и прекращение работы
     *
     * @param string $message Сообщение к выводу в консоль
     */
    public static function fatalError($message)
    {
        exit("\33[1m\33[31mКРИТИЧЕСКАЯ ОШИБКА!\33[m\n$message\n\n\n");
    }

    /**
     * Вывод сообщения
     *
     * @param string $message Сообщение к выводу в консоль
     */
    public static function error($message)
    {
        echo "\33[1m\33[31mОШИБКА:\33[m $message\n";
    }

    /**
     * Вывод сообщения
     *
     * @param string $message Сообщение к выводу в консоль
     */
    public static function info($message)
    {
        echo "\33[1m\33[34mИНФО:\33[m $message\n";
    }
}
