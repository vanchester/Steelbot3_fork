<?php

class SteelBotLng {
    private $primary_lang,
            $current_lang,
            $languages = array();
    
    
    public function __construct($primary_lang, $current_lang = false) {
        $this->primary_lang = $primary_lang;
        $this->current_lang = $current_lang?$current_lang:$primary_lang;
        
    }
    
    public function AddDict($file) {
        $lang_name = str_replace( '.php', '', basename($file) );
        if ( is_readable($file) ) {
            include $file;    
        } else {
            throw new Exception('Error opening language file: '.$file,0);
        }
        
        $this->ImportDict($lang_name, $lang);
        unset($lang);       
    }
    
    public function ResetDict($lang_name) {
        $this->languages[$lang_name] = array();
    }
    
    public function ImportDict($lang_name, $dict) {
        if (!array_key_exists($lang_name, $this->languages)) {
            $this->languages[ $lang_name ] = array();
        }
        $this->languages[$lang_name] = $this->languages[$lang_name] + $dict;
    }
    
	public function GetLanguagesList() {
		return array_keys($this->languages);
	}
	
	public function SetCurrentLang($lang) {
		$this->current_lang = $lang;
	}
	
	public function GetCurrentLang($lang) {
		return $this->current_lang;
	}
	
	public function RestorePrimaryLang() {
		$this->current_lang = $this->primary_lang;
	}
	
    public function GetTranslate($key, $lang =false ) {
        if (!$lang) {
            $lang = $this->current_lang;
        }
        if (array_key_exists($key, $this->languages[$lang])) {
            return $this->languages[$lang][$key];
        } elseif ( array_key_exists($key, $this->languages[$this->primary_lang]) ) {
            return $this->languages[$this->primary_lang][$key];			
		} else {
			return "#TRANSLATE ERROR:$key#";
        }
    }
       
}
