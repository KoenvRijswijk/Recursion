<?php
//namespace Recursion\utils;
/*       \|||/            +++            /@@@\             
        @(o o)@          (0_0)          /(^ ^)\
  ,--ooO--(_)---------ooo-----ooo---------\-/---Ooo----,
  |                        	                       |
  | class     FolderRights                             |
  | @author   geert                                    |
  | @date     28.12.2022                               |
  |  	                                               |
  '-------------Ooo--------------------ooO-------------'
        |__|__|        ooO Ooo         /_______\
  %      || ||      %   %    %   %       || ||     %
  \|/____ooO Ooo____\|/_\|/__\|/_\|/_____ooO Ooo___\|/ */
class FolderRights
{
    const RIGHTS_FN    = '.rights';
    const VIEW         = 'VIEW';
    const ADD_FOLDERS  = 'ADD_FOLDERS';
    const ADD_FILES    = 'ADD_FILES';
    const DEL_FOLDERS  = 'DEL_FOLDERS';
    const DEL_FILES    = 'DEL_FILES';
    
//==============================================================================
// Checks if .rights exitsts in current Folder, if so, load and handle file    
// combining new loaded rights with current inherited rights 
//==============================================================================
    public function loadRights(?array $inherited) : array
    {
        $result = [
            self::VIEW        => $inherited[self::VIEW]       ??0b10000000,
            self::ADD_FOLDERS => $inherited[self::ADD_FOLDERS]??0b10000000,
            self::ADD_FILES   => $inherited[self::ADD_FILES]  ??0b10000000,
            self::DEL_FOLDERS => $inherited[self::DEL_FOLDERS]??0b10000000,
            self::DEL_FILES   => $inherited[self::DEL_FILES]  ??0b10000000
        ];
        //var_dump(is_file(self::RIGHTS_FN));
        if (is_file(self::RIGHTS_FN))foreach ($this->readRights() as $rl)
        {
            list($name, $value) = explode('=', trim($rl));
            var_dump(decbin($result[self::VIEW]) & decbin(intval($value)));
            echo '-->';
            //var_dump(isset($result[self::VIEW]));
            switch ($name)
            {
// IF inherited view [group-bit] is zero, its stays zero !!!
                case self::VIEW :
                    isset($result[self::VIEW]) 
                    ? $result[self::VIEW] = decbin($result[self::VIEW]) & decbin(intval($value))
                    : $result[self::VIEW] = intval($value);    
                case self::ADD_FOLDERS:
                case self::ADD_FILES:
                case self::DEL_FOLDERS:
                case self::DEL_FILES:
                    $result[$name] = $value;    
            
            }
            var_dump(intval($result[self::VIEW]));
        }
        //var_dump($result[self::VIEW]);
        return $result;
    }    
//==============================================================================
    public function saveRights(array $rights) : bool 
    {
	$file = @fopen(self::RIGHTS_FN, 'w');
        if ($file)
        {
            foreach ($rights as $name => $value)
            {
                fwrite($file, $name.'='.$value.PHP_EOL);
            }    
            fclose($file);
            return true;
        }    
        return false;
    }
//==============================================================================
    protected function readRights() 
    {
	$file = @fopen(self::RIGHTS_FN, 'r');
	while (($line = $file?@fgets($file):false) !== false) 
	{
            yield $line;
	}
	if ($file)fclose($file);
    }		
}
