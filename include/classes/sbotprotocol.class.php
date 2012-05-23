<?php

/**
 * SBotProtocol class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author nexor
 * 
 */


abstract class SBotProtocol extends SComponent  {

    protected $_config = array();

    public function __construct($bot) {
        parent::__construct($bot);

        $events = array(
            'EVENT_CONNECTED',
            'EVENT_DISCONNECTED',
            'EVENT_MSG_RECEIVED',
            'EVENT_MSG_SENT',
            'EVENT_PRE_MSG_SEND',
            'EVENT_MSG_UNHANDLED',
            'EVENT_MSG_HANDLED',
            'EVENT_USR_STATUS',    
            'EVENT_AUTH_REQUEST',
            'EVENT_UNKNOWN_MESSAGE'
        );

        foreach ($events as $event) {
            $bot->eventManager->AddEventType($event);
        }
    }

    abstract function GetProtoInfo();

    /**
     * Подключиться к серверу
     *
     * 
     * @return bool
     */
    abstract function Connect();

    /**
     * Отключиться от сервера
     *
     */
    abstract function Disconnect();

    /**
     * Проверить, находится ли бот в онлайне
     *
     * @return bool
     */
    abstract function Connected();

    /**
     * Получить пришедшее сообщение
     *
     * 
     * @return bool
     */
    abstract function GetMessage();

    /**
     * Послать сообщение
     *
     * @param string $text - текст сообщения
     * @param string $to - ID человека в IM системе, на 
     * который надо отсылать сообщение.          
     */
    abstract function Msg($txt, $to);

    /**
     * Установить IM-статус бота.
     *
     * @param string $status
     */
    abstract function SetStatus($status);

    /**
     * Проверить, является ли строка записью IM-адреса в этом протоколе.
     *
     * @param string $string
     * @return bool
     */
    abstract function IsIMAccount($string);

    /**
     * Проверить, входит ли указанный аккаунт в 
     * список администраторских.
     *
     * @param string $account
     */
    abstract function IsAdmin($account);

    /**
     * Возвращает уникальный идентификатор бота в IM-системе
     * (например, UIN или JID, на котором он запущен)
     */
    abstract function BotId();

    public function beforeConnect(){}


}
