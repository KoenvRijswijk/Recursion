<?php
//namespace Recursion\dal;
/*       \|||/            +++            /@@@\             
        @(o o)@          (0_0)          /(^ ^)\
  ,--ooO--(_)---------ooo-----ooo---------\-/---Ooo----,
  |                                                    |
  | class     FolderDAO                                |
  | @author   geert                                    |
  | @date     24.12.2022                               |
  |  	                                               |
  '-------------Ooo--------------------ooO-------------'
        |__|__|	        ooO Ooo         /_______\
   %     || ||      %   %    %   %        || ||     %
  \|/___ooO Ooo____\|/_\|/__\|/_\|/______ooO Ooo___\|/ */
  require_once "BaseDAO.php";
  class FolderDAO extends BaseDAO
{
//==============================================================================   
    public function clearTables() : bool
    {
        return $this->_crud->truncateTables(['files','folders']);
    }    
//==============================================================================   
    public function addFolder(
            int $parent, 
            string $name, 
            string $active = 'F',
            int $view_rights, 
            int $add_folders_rights,
            int $add_files_rights,
            int $del_folders_rights, 
            int $del_files_rights) : int|false
    {
        return $this->_crud->doInsert(
            "INSERT INTO folders (parent, name, active, r_view, r_add_folders, r_add_files, r_del_folders, r_del_files)"
            ." VALUES (:parent, :name, :active, :r_view, :r_add_folders, :r_add_files, :r_del_folders, :r_del_files)",
            [
                'parent'        => [$parent, true],
                'name'          => [$name, false],
                'active'        => [$active, false],
                'r_view'        => [$view_rights, true], 
                'r_add_folders' => [$add_folders_rights, true], 
                'r_add_files'   => [$add_files_rights, true], 
                'r_del_folders' => [$del_folders_rights, true], 
                'r_del_files'   => [$del_files_rights, true] 
            ]
        );        
    }    
//==============================================================================   
    public function addFile(
            int $parent, 
            string $name, 
            int $view_rights, 
            int $delete_rights,
            string $active ='A') : int|false
    {
        return $this->_crud->doInsert(
            "INSERT INTO files (parent, name, active, r_view, r_delete) VALUES (:parent, :name, :active, :r_view, :r_delete)",
            [
                'parent'        => [$parent, true],
                'name'          => [$name, false],
                'r_view'        => [$view_rights, true], 
                'active'        => [$active, false],
                'r_delete'      => [$delete_rights, true]
            ]
        );     
    }    
//==============================================================================   
    public function setAllFoldersNonActive()
    {
        return $this->_crud->doUpdate("UPDATE folders SET active = 'F' where parent <> 0");     
    }
    
//==============================================================================   
    public function setAllFilesNonActive()
    {
        return $this->_crud->doUpdate("UPDATE files SET active = 'F' ");     
    }
//==============================================================================   
    public function activateFolder($folderID)
    {
        return $this->_crud->doUpdate("UPDATE folders SET active = 'A' WHERE id =:folderID",
                                        [
                                            'folderID' => [$folderID,true]
                                        ]
                                    );
    }

//==============================================================================   
    public function activateFile(string $fn, int $parent, int $level=128)
    {   
         return $this->_crud->doUpdate(
            'UPDATE files SET active =:active WHERE name =:filename AND parent =:parent',
                                        [
                                            'active' => ['A', false],
                                            'filename' => [$fn, false],
                                            'parent' => [$parent, true]
                                        ]
                                    );
    }
    
//==============================================================================
    public function checkFileNamebyName($filename, $parent, int $level=128)
    {
            $data = $this->_crud->selectOne(
            'SELECT name FROM files'
            .' WHERE parent=:parent AND name=:name AND (r_view&:level>0)',
            [
              'parent' => [$parent, true],  
              'name' => [$filename, false],
              'level'     => [$level, true]
            ]      
        );
        return $data?$data['name']:false;
    }
//==============================================================================      
    public function getFolderByID(int $id, int $level=128) : array|false
    {
        return $this->_crud->selectOne(
                "SELECT id, parent, FullPath(id) as fullname, name"
               ." FROM folders WHERE (id=:id) AND (r_view&:level>0)",
            [
                'id'        => [$id, true],
                'level'     => [$level, true]
            ]    
        );
    }    
//==============================================================================   
    public function getFileByID(int $id,int $level=128) : array|false
    {
        return $this->_crud->selectOne(
                "SELECT CONCAT(FullPath(parent), '\\\\', name) AS fullname"
               ." FROM files WHERE (id=:id) AND (r_view&:level>0)",
            [
                'id'        => [$id, true],
                'level'     => [$level, true]
                
            ]    
        );
    }    
//==============================================================================   
    public function getFolderContentByID(int $id, int $level=128) : array|false
    {
        return $this->_crud->selectMore(
            "SELECT id, 'FOLDER' AS type, name FROM folders"
            ." WHERE (parent=:id) AND (r_view&:level>0)"
            ." UNION"    
            ." SELECT id, 'FILE' AS type, name FROM files"
            ." WHERE (parent=:id) AND (r_view&:level>0)",
            [
                'id'        => [$id, true],
                'level'     => [$level, true]
            ]    
        );
        
    }        
//==============================================================================   
// As CONSTRAINT ONDELETE trigger won't work with parent=0 my own CASCADE   
//==============================================================================   
    public function deleteFolderByID(int $id, int $level=128) : bool
    {
        if ($this->_crud->doDelete(
                'DELETE FROM folders WHERE id=:id AND (r_del_folders&:level>0)',
                [
                    'id'        => [$id, true],
                    'level'     => [$level, true]
                ]
                ) !== false)
        {        
            $subfolders =  $this->_crud->selectMore(
                'SELECT id FROM folders WHERE parent=:id',
                [
                    'id'        => [$id, true]
                ]
            );
            foreach ($subfolders as $subfolder)
            {
                $this->deleteFolderByID($subfolder['id'], $level);
            }
            return true;
        }
        return false;
    }
//==============================================================================   
    public function getRootFolderByName(string $name, int $level=128) : int|false
    {
        $data = $this->_crud->selectOne(
            'SELECT id FROM folders'
            .' WHERE parent=0 AND name=:name AND (r_view&:level>0)',
            [
              'name' => [$name, false],
              'level'     => [$level, true]
            ]      
        );
        return $data?$data['id']:false;
    }    
    
//==============================================================================

    public function updateFolderName(string $name, int $id, $active = 'A')
    {
        return $this->_crud->doUpdate(
            "UPDATE folders SET name =:name, active=:active WHERE id =:id",
            [
                'name'  => [$name, false],
                'active'  => [$active, false],
                'id'     => [$id, true]
            ]    
        );
    }
}
