<?php
//require_once SRC.'interfaces\iSingleton.php';
//require_once SRC.'interfaces\iCrud.php';
class Crud //implements iSingleton, iCrud
{
    const PARAM_VALUE = 0;
    const PARAM_TYPE = 1;
    protected PDO $db;
    protected PDOStatement $stmt;
    protected string $_lasterror = '';
    private static $_instance = null;
//=============================================================================
    public static function getInstance() : object
    {
        $class = get_called_class(); 
        if (self::$_instance===null)
        {
                self::$_instance = new $class;
        }			
        return self::$_instance;
    }
//=============================================================================
    public function __clone() 
	{
        throw new Error('Cloning '. get_called_class() .' is not allowed.');
    }
//=============================================================================
    public function __wakeup() 
    {
        throw new Error('Unserializing '. get_called_class() .' is not allowed.');
    }
//==============================================================================
    public function isConnected() : bool
    {
        return is_object($this->db);
    }
//==============================================================================
    public function getLastError() : string
    {
        return $this->_lasterror;
    }
//==============================================================================	
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }
//==============================================================================	
    public function rollBack()
    {
        $this->db->rollBack();
    }
//==============================================================================	
    public function commit()
    {
        $this->db->commit();
    }
    
//=============================================================================
    public function selectOne(string $sql, array $params=[], string $class='') : array|object|false
    {
        if ($this->_execute($sql,$params))
        {
            if ($class)
            {
                $this->stmt->setFetchMode(
                    PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 
                    $class,array_keys(get_class_vars($class))
                );
            }    
            else
            {
                $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
            }
            return $this->stmt->fetch();
        }				
        return false;
    }
//==============================================================================
    public function selectMore(string $sql, array $params=[], string $class='') : array|false
    {
        if ($this->_execute($sql,$params)) 
        {	
            if ($class)
            {
                $this->stmt->setFetchMode(
                    PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 
                    $class,array_keys(get_class_vars($class))
                );
            }    
            else
            {
                $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
            }
            return $this->stmt->fetchAll();
        }	
        return false;
    }
//==============================================================================
    public function doInsert(string $sql, array $params=[]) : int|false
    {
        if ($this->_execute($sql,$params)) 
        {	
            return $this->db->lastInsertId();
        }	
        return false;
    }
//==============================================================================
    public function doUpdate(string $sql, array $params=[]) : int|false
    {
        if ($this->_execute($sql,$params)) 
        {	
            return $this->stmt->rowCount();
        }	
        return false;
    }
//==============================================================================
    public function doDelete(string $sql, array $params=[]): int|false
    {
        Tools::dump($sql);
        if ($this->_execute($sql,$params)) 
        {	
            return $this->stmt->rowCount();
        }	
        return false;
    }
//==============================================================================
    protected function _setLastError(string $error)
    {
        $this->_lasterror = $error;
    }
//==============================================================================
    private function _execute(string $sql, array $params) : bool
    {
        try
        {
            $this->stmt = $this->db->prepare($sql);
            foreach ($params as $name => $info)
            {
                $this->stmt->bindValue(
                        ":".$name, 
                        $info[self::PARAM_VALUE], 
                        $info[self::PARAM_TYPE] ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }
            return $this->stmt->execute();
        }    
        catch(PDOException $e)
        {
            $this->_setLastError($e->getCode().":".$e->getMessage());
            return false;
        }
    }		
//==============================================================================
    private function __construct() 
    {
        $pass = defined('PDOPASSUNSAFE')?PDOPASSUNSAFE:Tools::garble(PDOpass, MYKEY);
        $this->db = new PDO(PDOdriver.":host=".PDOhost.";dbname=".PDOdatabase,PDOuser,$pass);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
//==============================================================================
}
