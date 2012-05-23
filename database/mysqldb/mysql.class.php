<?php

/**
 * MySQL class
 *
 * http://steelbot.net
 *
 * @author N3x^0r
 * @package steelbot
 *
 */

class MySQL  {

    public $table_prefix,
           $dbname,
           $username,
           $error = false,
           $errno = false;

    public $dbhandle,
            $password,
            $host,
            $connected = false,
			$setnames,
            $options_table;

    const ER_DUP_ENTRY = 1062;
    const ER_EMPTY_QUERY = 1065;
    const CR_SERVER_GONE_ERROR = 2006;

    

    /**
     * Подключение к БД.
     * @return bool
     */
    public function Connect() {
   
        if (!function_exists('mysql_ping')) {
            throw new DBException("Fatal error: mysql extension must be loaded", 0);
        }
        
        if (is_resource($this->dbhandle) && mysql_ping($this->dbhandle)) {
            return true;
        }        
        
        $this->dbhandle = mysql_connect($this->host, $this->username, $this->password);
        if (!$this->dbhandle) {            
            throw new DBException(mysql_error(), mysql_errno());
        }
		
		if (!empty($this->setnames)) {
			mysql_query("SET NAMES '".$this->setnames."'");
		}
		
        $this->connected = true;            
    }

    public function GetDbInfo() {
        return array('name' => 'mysql');
    }

    public function selectDB($dbname) {
        return mysql_select_db($dbname);
    }
    
    /**
     * Отключение от БД.
     */
    public function Disconnect() {
        @mysql_close($this->dbhandle);
        $this->connected = false;
    }

    /**
     * Сделать запрос к БД и получить ссылку на mysql-результат.
     *
     * @param string $query
     * @return resource
     */
    public function Query($query) {
        $this->errno = $this->error = false;
        $return = mysql_query($query, $this->dbhandle);
        $this->errno = mysql_errno($this->dbhandle);
        $this->error = mysql_error($this->dbhandle);

        if ($this->errno == self::ER_EMPTY_QUERY) {
            return false;
        }
        
        if ($this->errno) {
            throw new DBException($this->error, $this->errno);
        }
        return $return;
    }

	/**
	 * Запрос, автоматически экранирующий пользовательские данные
	 *
	 * @param string $query
	 * @param array $data
	 */
	public function EscapedQuery($query, $data) {
        if (!is_null($data)) {    
            $keys = array_keys($data);
            $values = array_values($data);
            
            foreach ($keys as &$k) {
                $k = '{'.$k.'}';
            }
            foreach ($values as &$v) {
                $v = "'".mysql_real_escape_string($v, $this->dbhandle)."'";
            }

            $query = str_replace($keys, $values, $query);
        }
		return $this->query( $query );
	}

	public function FormatQuery($query, $data) {
		if (!is_null($data)) {
			$keys = array_keys($data);
			$values = array_values($data);
			
			foreach ($keys as &$k) {
				$k = '{'.$k.'}';
			}
			foreach ($values as &$v) {
				$v = "'".mysql_real_escape_string($v, $this->dbhandle)."'";
			}
            return str_replace( $keys, $values, $query);
		}
		return $query;
	}
	
	/**
	 * Извлечь строку из mysql результата в виде ассоциативного массива
	 */
	public function FetchAssoc($r) {
		return mysql_fetch_assoc($r);
	}
	
	/**
	 * Извлечь строку из mysql результата в виде неассоциативного массива
	 */
	public function FetchRow($r) {
		return mysql_fetch_row($r);
	}
	
	/**
	 * Получить количество строк в mysql результате
	 */
	public function NumRows($r) {
		return mysql_num_rows($r);
	}
	
    /**
     * Сделать запрос к БД, и получить результат в виде единственного значения.
     *
     * @param string $query
     * @return string
     */
    public function QueryValue($query) {
        $result = $this->Query($query);
        $result = mysql_fetch_array($result);
        return $result[0];
    }

    /**
     * Сделать запрос к БД и получить результат в виде двумерного массива
     * значений.
     *
     * @param string $query
     * @return array
     */
    public function QueryArray($query) {
        $result = $this->Query($query);        
		$return = array();
		while ($row = mysql_fetch_row($result)) {
			$return[] = $row;
		}
		return $return;
    }

    /**
     * Экранировать опасные для sql запроса символы в строке.
     *
     * @param string $str
     * @return string
     */
    public function EscapeString($str) {
        return mysql_real_escape_string($str, $this->dbhandle);
    }

    public function RowsAffected() {
        return mysql_affected_rows($this->dbhandle);
    }
}
