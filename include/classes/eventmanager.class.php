<?php

class EventManager extends SComponent {

	protected $events = array(),
			  $maxEventCode;

	public function __construct($config) {
		parent::__construct($config);
	}

	

/**
 * Добавление обработчика события в бота.
 *
 * @param int $event_type  код события
 * @param mixed $func  функция-обработчик
 * @param int $priority приоритет обработчика
 */
public function RegisterEventHandler($event_type, $func, $priority = 50) {    
    if ( isset($this->events[$event_type][$priority])) {
        $this->events[$event_type][$priority][] = $func ;    
    } else {
        $this->events[$event_type][$priority] = array( $func );
    }
    return $this;    
    
}

/**
 * Удаление обработчика события.
 *
 * @param int $event_type код события
 * @param string $func функция-обработчик
 * @return bool
 */
public function UnregisterEventHandler($event_type, $func) {
    if (array_key_exists($event_type, $this->events)) {
        foreach ($this->events[$event_type] as $prior=> $funcs) {
            foreach ($funcs as $k=>$v) {
                if ($v == $func) {
                    unset($this->events[$event_type][$prior][$k]);
                    return true;
                }
            }
        }
    } else {
        trigger_error("Unknown event: $event_type", E_USER_WARNING);
        return false;
    }
}

/**
 * Возвращает массив всех обработчиков заданного события.
 *
 * @param int $event_type код события
 * @return array 
 */
public function GetEventHandlers($event_type) {
    if (array_key_exists($event_type, $this->events)) {
        return $this->events[$event_type];
    } else {
        trigger_error("Unknown event: $event_type", E_USER_WARNING);
        return false;
    }
}

/**
 * Создает новый тип события в системе.
 *
 * @param string $name имя константы, идентифицирующей событие.
 */
public function AddEventType($name) {
    if (!defined($name)) {
        define($name, $this->maxEventCode);
        $this->maxEventCode++;
    }
    return $this;
}

/**
 * Генерация события.
 *
 * @param Event $event
 * @param bool $handledrop - ловить drop-исключение
 * @return Event
 */
public function EventRun($event, $handledrop = true) {
    if (!$event instanceof Event) {
        throw new BotException("First parameter must be an Event object",0);
    }
    $code = $event->GetCode();
    if (array_key_exists($code, $this->events)) {        
        try {
            $keys = array_keys($this->events[$code]);            
            natsort($keys);
            while ($key = array_pop($keys)) {              
                foreach ($this->events[$code][$key] as $func) {
                    if (is_callable($func)) {
                        call_user_func($func, $event);
                    } else {
                        trigger_error("Event #$code: \"".func2str($func).'" is not a callable func', E_USER_WARNING);
                    }                
                }                
            }
        } catch (EventDrop $ed) {
            if ( $handledrop ) {
                ##slog::add('core', $ed->getMessage(), LOG_DROPPED_EVENT);
            } else {
                throw $ed;
            }
        }
    }    
    return $event;
}

	

}
