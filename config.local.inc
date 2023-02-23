<?php
ini_set('display_errors', 1);
define("DEBUG"          , FALSE); 
define('TITLE',         'Bieb'); 
define('AUTHOR',        'Koen');
define('AUTHOR_URL','www.koen.nl');
define('NOP',           'NOP');
define('USERID',        'UID');
define('USEREMAIL',     'UEMAIL');
define('USERNAME',      'UNAME');
define('USERROLE',      'UROLE');
define('SYSMSG',        'SYSMSG');
define('SYSERR',        'SYSERR');
define('MYKEY',         'x');
define("URL"            ,"localhost/project_x".$_SERVER["PHP_SELF"]);
define("PDOdriver"      ,'mysql');
define("PDOdatabase"    ,'zijnenworden');
define("PDOhost"        ,'127.0.0.1');
define("PDOuser"        ,'root');
define("PDOpass"        ,'');
define('PDOPASSUNSAFE'  ,'');
define('LINKBASE'       ,'index.php?page=');
define('WEBIMG_FOLDER'  ,'.\assets\img\\');
define("UPLOADURL"      ,'./uploads/');
define("UPLOADPATH"     , realpath('./uploads').DIRECTORY_SEPARATOR); // HIER VOLLEDIG PAD VOOR WINDOWS IIS!!
define('SRC'            ,'.\_src\\');
define('CLASSPATH',		  '.\_src\classes\\');

define("LOG_DIR"        , realpath('./logs/').DIRECTORY_SEPARATOR);
