#!/usr/bin/php
<?php

/**
 * Installer class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author nexor
 * @package steelbot 3
 */

include dirname(dirname(__FILE__)).'/include/steelbotautoloader.php';

echo "Steelbot installer v. 0.1\n";
define('STEELBOT_DIR', dirname(dirname(__FILE__)));
//define('STDIN', fopen("php://stdin","r"));

function printUsage($die = true) {
    $self = basename(__FILE__);
    print "Usage: $self create <path>\n";
    if ($die) die;
}

function inputSelect($message, $values = array(), $tries = 3) {
    $inputOk = false;    
    while (!$inputOk && $tries-- > 0) {
        echo $message."\n";
        foreach ($values as $k=>$v) {
            echo "  $k - $v\n";
        }
        echo "> ";
        $input = trim(fgets(STDIN));
        $inputOk = in_array($input, array_keys($values));
        if (!$inputOk) $input = false;
    }
    return $input;
}

function inputText($message, $defaultValue = '') {
    if (empty($defaultValue)) {
        echo "  {$message}\n> ";
    } else {
        echo "  {$message} [{$defaultValue}]\n> ";
    }
    $input = trim(fgets(STDIN));
    if (!empty($input)) {
        return $input;
    } else {
        return $defaultValue;
    }
}

function error($error, $die = true) {
    echo "Error: $error\n";
    if ($die) die;
}

function actionCreate() {
    $path = realpath($_SERVER['argv'][2]);

    $installer = new Installer;

    $continue = inputSelect("Install application under $path?", array(
        'y' => 'Yes',
        'n' => 'No'
    ), 1);
    
    if ($continue != 'y')
        return;
        
    if (!$installer->setDestinationPath($path)) {
        error("\"$path\" is not a correct writeable path");
        die;
    }

    $protocols = $installer->getAvailableProtocols();
    
    $protocol = inputSelect("Please, choose IM protocol", $protocols);
    if (!$installer->setProtocol($protocol)) {
        error("Protocol must be specified for install");
        die;
    }
    
    echo $protocols[$protocol]." has been chosen\n";

    if ($protocol == 1) {
        $installer->config['proto']['engine'] = 'icq';
        $installer->config['proto']['uin'] = inputText("Enter bot UIN", '');
        $installer->config['proto']['password'] = inputText("Enter UIN password", '');

        $account = inputText("Enter bot administrator UIN", '');
        $installer->config['proto']['master_accounts'] = array($account);
    } elseif ($protocol == 2) {
        $installer->config['proto']['engine'] = 'jabber';
        $installer->config['proto']['jid'] = inputText("Enter bot JID", '');
        $installer->config['proto']['password'] = inputText("Enter JID password", '');

        $account = inputText("Enter bot administrator JID", '');
        $installer->config['proto']['master_accounts'] = array($account);
    }


    echo "MySQL settings\n";
    // mysql settings
    $installer->config['db']['host'] = inputText("Enter MySQL host", 'localhost');
    $installer->config['db']['user'] = inputText("Enter MySQL user", 'root');
    $installer->config['db']['database'] = inputText("Enter MySQL database", 'steelbot');
    $installer->config['db']['password'] = inputText("Enter MySQL password", '');
    
    

    $installer->setAppTemplatePath(dirname(__FILE__).DIRECTORY_SEPARATOR.'/app');
    
    echo "Creating application...\n";
    $installer->steelbotPath = STEELBOT_DIR;
    
    $installer->Deploy();

}

if (count($_SERVER['argv']) < 2) {
    printUsage();
}

switch ($_SERVER['argv'][1]) {
    case "create":
        actionCreate();
        break;
    default:
        printUsage();
}




