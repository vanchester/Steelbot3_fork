<?php
$phrase = self::$case_sens ? $event->content : mb_strtolower($event->content, 'UTF-8'); 
        if ($msg = self::$_store->FindMatch($phrase)) {
                S::bot()->Msg($msg, $event->sender);
                throw new EventDrop;
        }
