<?php
require_once 'crud.php';
class BaseDAO
{
    protected Crud $_crud;
//==============================================================================
    public function __construct()
    {
        //get intance of pdo/crud class
        $this->_crud = Crud::getInstance();
        //check if connection is made
        if ($this->_crud->isConnected()===false)
        {
            throw new Error("No database connection.");
        }			
    }
//==============================================================================
    public function getLastError() : string
    {
        return $this->_crud->getlastError();
    }
}
