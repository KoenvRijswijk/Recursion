<?php
//namespace Recursion\elements;
/**
 * Folder Content Element
 *
 * @author Geert Weggemans - geert@man-kind.nl
 * @date nov 9 2022
 */
require_once SRC.'views/BasePage.php';
require_once SRC.'dal/folderdao.php';
class FolderElement extends BasePageElement
{
    protected int $folder_id;
//==============================================================================
    public function __construct(int $order, int $folder_id)
    {
        parent::__construct($order);  
        $this->folder_id = $folder_id;
    }    
//==============================================================================
    protected function _displayContent() : string
    {
        
        $folderDAO = new FolderDAO();
        
        $folder = $this->folder_id===0
                ? ['fullname'=>'ROOTFOLDERS', 'parent' => -1]
                : $folderDAO->getFolderByID($this->folder_id);
        if ($folder)
        {   
            $ret = '<h3>&#x1F5C1;&nbsp;'.$folder['fullname'].'</h3>'.PHP_EOL
                 . '<ul class="folder_content">'.PHP_EOL;
            $foldercontent = $folderDAO->getFolderContentByID($this->folder_id);
            if ($folder['parent'] >= 0)       
            {    
                $ret .= '<li>'.$this->createFolderLink($folder['parent'],'&#x21E6;&nbsp;..').'</li>'.PHP_EOL;;
            }        
            if ($foldercontent)
            {
                foreach ($foldercontent as $item)
                {
                    $ret .= '<li>'
                          .(
                            $item['type']==='FOLDER'
                            ? $this->createFolderLink($item['id'],'&#x1F5C0;&nbsp;'.$item['name'])
                            : $this->createFileLink($item['id'],'&#x1F4C4;&nbsp;'.$item['name'])  
                            )
                          . '</li>';
                }    
            }    
            return $ret.'</ul>'.PHP_EOL; 
        }
        else
        {
            return '<p>No info for folder ['.$this->folder_id.']</p>';
        }    
    }
//==============================================================================
    protected function createFolderLink(int $id, string $name) : string
    {
        return  '<a class="folder_link" '
              . 'href="#" '
              . 'data-gw-folder-id="'.$id.'"'
              . '>'.$name.'</a>'.PHP_EOL;    
    }    
//==============================================================================
    protected function createFileLink(int $id, string $name) : string
    {
        return  '<a class="file_link" '
              . 'href="#" '
              . 'data-gw-file-id="'.$id.'"'
              . '>'.$name.'</a>'.PHP_EOL;    
    }    
// 21E8 = RIGHT ARROW    
    
}
