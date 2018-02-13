<?php

class Console
{
    public static function fatalError($message)
    {
        exit("\n\33[1m\33[31mКРИТИЧЕСКАЯ ОШИБКА!\33[0m\n$message\n\n");
    }

    public static function error($message)
    {
        echo "\n\33[1m\33[31mОШИБКА:\33[0m $message";
    }

    public static function info($message)
    {
        echo "\n\33[1m\33[34mИНФО:\33[0m $message";
    }
}
