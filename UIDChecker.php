<?php
require_once 'FolderDAO.php';
require_once 'Filewalker.php';
Class UIDChecker extends Filewalker
{
//==============================================================================
//Checks for .UID FILE, if Exist Activate Folder and update name if changed
//					    else insert folder an DB save UID.file 
// 				Return UID 
//==============================================================================
	
	public function getUID(string $folderpath, int $parent, string $name) : array
	{

		/*if($name  === 'C:\Bitnami\wampstack-8.1.9-0\apache2\htdocs\schatkist')
			{
				$name = 'De Schatkist';
			}*/
		//Search for Unique ID (and parent) file and load UID's	
		if(file_exists($folderpath.'\.UID'))
		{
			$UID = file_get_contents($folderpath.'\.UID');
			$ids = explode('|', $UID);
		//Transform string to integer to be sure nothing can go wrong in this step ! ;-)
			$UIDParent = $ids[0];
			$UIDFolder = $ids[1];		
		//Get the name of folder based on Unique ID
			$folderdao = new FolderDAO();
			$folderDbInfo = $folderdao->getFolderByID($UIDFolder);
		//check if name of folder is equal to name in database
			if(empty($folderDbInfo['name']))
			{
				echo 'Folder is not in database, update DB and UID file';
				$folderUID = $this->setUID($folderpath, $name, $parent);
				return $folderUID;
			}
			elseif($folderDbInfo['name'] !== $name)
			{
				echo "Name is changed for".$name;
				$this->folderdao->updateFolderName($name, $UIDFolder);
				return $ids;
			}
			else
			{
				echo "Name is matching Database";
			}
		$folderdao->activateFolder($UIDFolder);
		return $ids;
		}
		else
	//folder is new, so let's save it in the database ! :-) 
		{
			echo 'folder is new! name:'.var_dump($name).' :-) parent : ';
			var_dump($parent);
			$folderUID = $this->setUID($folderpath, $name, $parent);
			return $folderUID;
		}
	}
//==============================================================================
	public function setUID(string $folderpath, string $name, int $parent)  : array|false
	{
		$this->rights[$this->depth] = $this->folderrights->loadRights($this->currentRights());
		$folderdao = new FolderDAO();
		$id = $folderdao->addFolder($parent, $name, 'A', 
					 				$this->rights[$this->depth][$this->folderrights::VIEW],
									$this->rights[$this->depth][$this->folderrights::ADD_FOLDERS],
					                $this->rights[$this->depth][$this->folderrights::ADD_FILES],
					                $this->rights[$this->depth][$this->folderrights::DEL_FOLDERS],
					                $this->rights[$this->depth][$this->folderrights::DEL_FILES]);

		if($id)
		{
			$UIDfile = fopen($folderpath.'\.UID', 'w');
			ftruncate($UIDfile, 0);
			$txt 	= $parent.'|'.$id;
			fwrite($UIDfile, $txt);
			fclose($UIDfile);
			$ids = [$parent, $id];
			return $ids;
		}
		else
		{
			echo ' Oops, something went wrong during add folder;';
			return false;
		}
	}
//==============================================================================
  
}