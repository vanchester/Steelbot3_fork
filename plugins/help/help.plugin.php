<?php

/**
 * help - SteelBot plugin
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * 
 */

S::bot()->RegisterCmd( SteelBotHelp::$helpAlias, array('SteelBotHelp', 'help'), 1, 'help - вывести помощь');
S::bot()->eventManager->RegisterEventHandler(EVENT_MSG_UNHANDLED, array('SteelBotHelp', 'notfound'));

class SteelBotHelp {

    public static $helpAlias = 'help';
    
    public static function help($params) {
        $cm = S::bot()->commandManager;
        if (empty($params)) {
            $helpstr = array();
            $lastLabel = null;
            foreach ($cm->getAliases() as $alias) {
                $cmd = $cm->getCommandByAlias($alias);
                $cmdaccess = $cmd->GetAccess();

                // Показываем команду, только если она подходит пользователю по уровню доступа,
                // и не является администраторской (для администраторских команд свой хелпер)
                if ( ($cmdaccess <= S::bot()->getUserAccess()) && ($cmdaccess != 100) ) {
                    //выведем названия групп, если они заданы
                    if (!isset(CommandManager::$commandGroups[$alias])) {
                        if (!empty($lastLabel)) {
                            $helpstr[] = "\n";
                        }
                    } else if (CommandManager::$commandGroups[$alias] != $lastLabel) {
                        $helpstr[] = "\n".CommandManager::$commandGroups[$alias].':';
                    }
                    
                    $lastLabel = !empty(CommandManager::$commandGroups[$alias]) ? CommandManager::$commandGroups[$alias] : null;
                    
                    //$msg = $cmd->GetHelpShort($alias);
                    $msg = $cmd->GetHelpFull($alias);
                    
                    $i = false;
                    foreach(SteelBot::$aliases as $index => $command) {
                        if ($command == $alias) {
                            $i = $index;
                            break;
                        }
                    }

                    if ($i !== false) {
                        $msg = "{$i}. {$msg}";
                    }
                    
                    $helpstr[] = $msg;
                }
            }
            S::bot()->Msg( "Доступные команды: \n".implode("\n",$helpstr) );
        } else {
            self::CmdHelp($params); 
        }
    }

    /**
     * @desc Отправляет сообщение со справкой по указанной команде $cmd
     *
     * @param string $cmd - имя команды
     */
    public static function CmdHelp($alias) {
        if (isset(S::bot()->commandManager[$alias])) {
            if ( S::bot()->commandManager[$alias] ->GetAccess() <= S::bot()->GetUserAccess() ) {
                $msg = S::bot()->commandManager[$alias]->getHelpFull($alias);
                S::bot()->Msg( $msg );
            } else {
                    S::bot()->Msg( "Недостаточно прав для чтения $alias" );
            }
        } else {
            S::bot()->Msg( "Команда $alias не найдена" );
        }
    }

    public static function notfound($event) {
        $alias = $event->alias;
        S::bot()->Msg("Команда $alias не найдена. Для получения помощи отправьте ".SteelBotHelp::$helpAlias);
    }
}

function plg_SendMsgToAdm($message) {
    $admins = S::bot()->config['proto']['master_accounts'];
    
    if (empty($admins)) {
        S::bot()->Msg("Admins not setted");
        return;
    }
    
    foreach($admins as $ID) {
        echo $ID."\n";
        S::bot()->Msg("Message from UID ".S::bot()->msgEvent->sender.":\n{$message}", $ID);
        sleep(1);
    }
}

S::bot()->RegisterCmd("send2adm", "plg_SendMsgToAdm", 1, "{alias} <message> - отправить сообщение Администрации");

