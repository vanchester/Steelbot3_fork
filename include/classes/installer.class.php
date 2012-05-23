<?php

class Installer  {

    public $config = array(
            'db' => array(
                'engine' => 'mysqldb'
             ),
            
            'plugins' => array(
                'inet-tools' => array(),
                'help' => array()
                )
            ),
           $steelbotPath = null,
           $destinationPath = null,
           $appTemplatePath = null,
           $protocol = null;
    
    protected $_protocols = array(
            1 => 'ICQ',
            2 => 'XMPP (Jabber)'
    ),

            $_protocol,
            $_destinationPath;
    

    public function Deploy() {
    
        $destinationPath = realpath($this->destinationPath);
        if (!is_dir($destinationPath) || !is_writeable($destinationPath)) {
            throw new Exception("$destionationPath is not correct directory path");
        }

        $appTemplatePath = realpath($this->appTemplatePath);
        if (!is_dir($appTemplatePath)) {
            throw new Exception("$appTemplatePath is not correct application template path");
        }

        $directories = array(
            'tmp'     => 0777,
            'plugins' => 0755,
            'logs'    => 0755,
        );

        $filesModes = array(
            'botctl' => 0755
        );

        $files = glob($appTemplatePath.DIRECTORY_SEPARATOR.'*');
        foreach ($files as $file)
        {
            $this->copyFile($file, $destinationPath.DIRECTORY_SEPARATOR.basename($file));
        }

        foreach ($directories as $dir=>$chmod) {
            $mkdir = $destinationPath.DIRECTORY_SEPARATOR.$dir;
            $this->mkdir($mkdir, $chmod, true);
        }

        foreach ($filesModes as $name=>$mode) {
            $filename = $destinationPath.DIRECTORY_SEPARATOR.$name;
            $this->chmod($filename, $mode);
        }

        $this->PopulateConfig($destinationPath.DIRECTORY_SEPARATOR.'run.php');                
    }

    public function getAvailableProtocols() {
        return $this->_protocols; 
    }

    public function setDestinationPath($path) {
        if (!is_writable($path)) {
            return false;
        } else {
            $this->destinationPath = realpath($path);
            return true;
        }
    }

    public function setProtocol($protocol) {
        if (!isset($this->_protocols[$protocol])) {
            return false;
        } else {
            $this->_protocol = $protocol;
            return true;
        }
    }

    public function setAppTemplatePath($path) {
        $this->appTemplatePath = $path;
    }

    public function populateConfig($filename) {
        echo "  Writing run.php\n";
        
        $content= file_get_contents($filename);
        $steelbotPath = $this->steelbotPath;
        $content = preg_replace('/define\(\'STEELBOT_DIR.*/',
            "define('STEELBOT_DIR', '$steelbotPath');",$content);

        $config = $this->config;

        switch ($this->protocol) {
            case 1:
                $config['proto'] = array(
                    'engine' => 'icq',
                    'uin' => 'your UIN here',
                    'password' => 'your password here',
                    'master_accounts' => array('admin UIN here', 'other admin UIN')
                );
                break;
            case 2:
                $config['proto'] = array(
                    'engine' => 'jabber',
                    'jid' => 'your@jid.dom',
                    'password' => 'your password here',
                    'master_accounts' => array('admin@accounts.here', 'other@admin.account')
                );
                break;
        }

        $export = var_export($config, true);
        $content = preg_replace('/\$config.*/',
            "\$config = $export;",$content);
            
        file_put_contents($filename, $content);
        
    }

    protected function copyFile($from, $to) {
        echo "  creating $to\n";
        copy($from, $to);
    }

    protected function mkdir($path, $chmod, $recursive = true) {
        echo "  creating $path\n";
        mkdir($path, $chmod, $recursive);
    }

    protected function chmod($path, $mode) {
        echo "  chmod $mode $path\n";
        chmod($path, $mode);
    }

}
