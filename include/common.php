<?php

/**
 * Представление имени функции в виде строки
 * @param mixed $func callback функции
 */
function func2str($func) {
    if (is_array($func)) {
        if (is_object($func[0])) {
            return get_class($func[0]).'->'.$func[1];
        } else {
            return $func[0].'::'.$func[1];
        }
    } else {
        return $func;
    }
}

/**
 * Сравнивает два номера версии
 *
 * @param string $old исходная версия
 * @param string $new новая версия
 * @return true, если новая версия больше исходной
 *         false, если новая версия меньше исходной
 *         null, если новая версия равна исходной
 */
function CheckVersion($old, $new) {        
        $old = explode('.', $old);
        $new = explode('.', $new);
        foreach ($old as $k=>$v) {
            if ($old[$k] < $new[$k]) {            
                return true;
            } elseif ($old[$k] > $new[$k]) {
                return false;
            }
        }
        return null;
}

/**
 * Ошибка при попытке вызова функции
 */
define("ERR_FUNC", 0x01);

/*
 * Ошибка доступа к команде.
 */
define("ERR_CMD_ACCESS",   0x02);

/**
 * Значение не является числом
 */
define("ERR_NOT_NUMERIC",  0x03);

/**
 * Команда не найдена
 */
define("ERR_CMD_NOTFOUND", 0x04);

/**
 * Ошибка доступа к файлу
 */
define("ERR_FILE_ACCESS",  0x05);

/**
 * Не удовлетворена зависимость
 */
define("ERR_DEPENDENCY",   0x06);

/**
 * Указан недопустимый уровень доступа.
 */
define("ERR_ACCESS_OVERFLOW", 0x07);

/**
 * Попытка добавить уже существующую команду
 */
define("ERR_CMD_ALREADY_DEFINED", 0x08);
