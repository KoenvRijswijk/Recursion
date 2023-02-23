<?php
require_once 'folderDAO.php';
require_once 'folderRights.php';
class FileWalker
{
    protected int $foldercount = 0;
    protected int $filecount = 0;
    protected int $depth=-1;
    protected FolderDAO $folderdao;

//==============================================================================    
    public function __construct()
    {
        $this->folderdao = new FolderDAO();
        $this->folderrights = new FolderRights();

    } 
//==============================================================================	
    public function useSomeForce(string $folder) : bool
    {
        
        $this->foldercount = 0;
        $this->startAgain();
        $this->filecount = 0;
        $this->rights = [];
        $this->depth = -1;
        $this->rootfolder = $folder;
        return $this->scanFolder(0, realpath($folder));
    }
//==============================================================================	

        public function startAgain()
        {
            $this->folderdao->setAllFoldersNonActive();
            $this->folderdao->setAllFilesNonActive();
        }   
//==============================================================================    
    protected function scanFolder(int $parent, string $folder) : bool
    {
        if (chdir($folder))
        {
            $result = true;
            $this->depth++;
            $this->foldercount++;
            $folder_id = $this->openFolder($parent, realpath($folder), $folder);
            $files = $this->getFiles();
            if ($files)
            {    
                sort($files);
                foreach ($files as $file)
                {
                    $this->filecount++;
                    $this->handleFile($folder_id[1], $file);
                }
            }    
            $folders = $this->getFolders();
            if ($folders)
            {    
                sort($folders);
                foreach ($folders as $folder)
                {
                    if ($this->scanFolder($folder_id[1], $folder)===false)
                    {
                        $result = false;
                        break;
                    }        
                }
            }    
            $this->closeFolder();
            chdir("..");	
            $this->depth--;
            return $result;
        }
        return false;
    }
//==============================================================================	
    protected function handleFile(int $parent, string $fn) :  int|false
    {
        //check if foldername is in database, add if not and activate them.. keep the not found unactive
        if($this->folderdao->checkFileNamebyName($fn, $parent))
        {
            //activate
            $this->folderdao->activateFile($fn, $parent);
        }
        else 
        {
           
           $this->folderdao->addFile($parent, $fn, 128, 128 );
        }   
        echo '<li>INSERT FILE name=['.$fn.'] parent=['.$parent.'] id['.$this->filecount.'] file=['.$fn.']</li>'.PHP_EOL;
        return $this->filecount;
    }
//==============================================================================	
    protected function openFolder(int $parent, string $fn, string $foldername) //:  int|false
    {
        $this->UIDS = $this->getUID(realpath($fn), $parent, $foldername);
        $this->rights[$this->depth] = $this->folderrights->loadRights($this->currentRights());
        echo '<li>INSERT FOLDER parent=['.$this->UIDS[0].'] id['.$this->UIDS[1].'] folder=['.$foldername.']'
            .' VIEW=['.$this->rights[$this->depth][$this->folderrights::VIEW].']'
            .' ADD FOLDERS=['.$this->rights[$this->depth][$this->folderrights::DEL_FOLDERS].']'
            .' ADD FILES=['.$this->rights[$this->depth][$this->folderrights::DEL_FILES].']'
            .' DEL FOLDERS=['.$this->rights[$this->depth][$this->folderrights::ADD_FOLDERS].']'
            .' DEL FILES=['.$this->rights[$this->depth][$this->folderrights::ADD_FILES].']'
            . '</li>'.PHP_EOL;
        
        /*echo '<li>INSERT FOLDER name = ['.$foldername.'] parent=['.$this->UIDS[0].'] id['.$this->UIDS[1].'] 
              folder=['.realpath($fn).']</li>'.PHP_EOL;
        echo '<ol>'.PHP_EOL;
        */
        return $this->UIDS;
    }
//==============================================================================	
    protected function closeFolder() :  void
    {
        echo '</ol>'.PHP_EOL;
    }
//==============================================================================	
    protected function getFiles() : array|false
    {
        return array_filter(glob('*.*'), 'is_file');
    }
//==============================================================================	
    protected function getFolders() : array|false
    {
        return array_filter(glob('*'), 'is_dir');
    }
//==============================================================================
 protected function currentRights() : array|null
    {
        return ($this->depth===0)?null:$this->rights[$this->depth-1];
    }    	
//==============================================================================
}	
