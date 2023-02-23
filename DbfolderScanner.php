<?php

//namespace Recursion\utils;
/*       \|||/            +++            /@@@\             
        @(o o)@          (0_0)          /(^ ^)\
  ,--ooO--(_)---------ooo-----ooo---------\-/---Ooo----,
  |                        	                       |
  | class     DbFolderScanner                          |
  | @author   geert                                    |
  | @date     28.12.2022                               |
  |  	                                               |
  '-------------Ooo--------------------ooO-------------'
        |__|__|        ooO Ooo         /_______\
  %      || ||      %   %    %   %       || ||     %
  \|/____ooO Ooo____\|/_\|/__\|/_\|/_____ooO Ooo___\|/ */
require_once 'FileWalker.php';
require_once 'folderDAO.php';
require_once 'folderRights.php';
class DbFolderScanner extends FileWalker
{
    protected FolderDAO $folderdao;
    protected FolderRights $folderrights;
    protected array $rights = [];

//==============================================================================	
    public function __construct()
    {
        $this->folderdao = new FolderDAO();
        $this->folderrights = new FolderRights();
    } 
//==============================================================================	
    public function useSomeForce(string $folder) : bool
    {
        $this->rights = [];
        $this->depth = -1;
        $this->startAgain();
        $root_id = $this->folderdao->getRootFolderByName(realpath($folder));
        if ($root_id)
        {    
            $this->folderdao->deleteFolderByID($root_id);
        }
        return parent::useSomeForce($folder);
    }    
//==============================================================================	
    	public function startAgain()
    	{
    		$this->folderdao->setAllFoldersNonActive();
    	}
//==============================================================================	
    protected function handleFile(int $parent, string $fn) :  int|false
    {
        echo '<li>INSERT FILE parent=['.$parent.'] id['.$this->filecount.'] file=['.$fn.']</li>'.PHP_EOL;
        return $this->folderdao->addFile(
                $parent, 
                $fn, 
                $this->rights[$this->depth][$this->folderrights::VIEW],
                $this->rights[$this->depth][$this->folderrights::DEL_FILES]
            );
    }
//==============================================================================	
    protected function openFolder(int $parent, string $fn) :  int|false
    {
        $this->rights[$this->depth] = $this->folderrights->loadRights($this->currentRights());
        echo '<li>INSERT FOLDER parent=['.$parent.'] id['.$this->foldercount.'] folder=['.$fn.']'
            .' VIEW=['.$this->rights[$this->depth][$this->folderrights::VIEW].']'
            .' ADD FOLDERS=['.$this->rights[$this->depth][$this->folderrights::DEL_FOLDERS].']'
            .' ADD FILES=['.$this->rights[$this->depth][$this->folderrights::DEL_FILES].']'
            .' DEL FOLDERS=['.$this->rights[$this->depth][$this->folderrights::ADD_FOLDERS].']'
            .' DEL FILES=['.$this->rights[$this->depth][$this->folderrights::ADD_FILES].']'
            . '</li>'.PHP_EOL;
        echo '<ol>'.PHP_EOL;
        return $this->folderdao->addFolder(
                $parent, 
                $fn, 
                $this->rights[$this->depth][$this->folderrights::VIEW],
                $this->rights[$this->depth][$this->folderrights::DEL_FOLDERS],
                $this->rights[$this->depth][$this->folderrights::DEL_FILES],  
                $this->rights[$this->depth][$this->folderrights::ADD_FOLDERS],
                $this->rights[$this->depth][$this->folderrights::ADD_FILES],
        );
    }
//==============================================================================
    	//
//==============================================================================


    protected function currentRights() : array|null
    {
        return ($this->depth===0)?null:$this->rights[$this->depth-1];
    }    
//==============================================================================	
}
