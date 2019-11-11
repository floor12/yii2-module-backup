## The module functions

This module helps to create and restore backups of databases and files stored in disk.
It has web interface, console commands and REST-api to remote control. It also supports `ionice` settings and has flexible configuration
 options.
 
![Yii backup module](https://floor12.net/files/default/get?hash=4895685e3392ade4e0e2a40a762bc4fe)

## Installation

To add this module to your app, just run:

 ```bash
 $ composer require floor12/yii2-module-backup
 ```
or add this to the `require` section of your composer.json.
 ```json
 "floor12/yii2-module-backup": "dev-master"
 ```
 
 
 After that, include minimal module configuration in `modules` section of application config:
 ```php  
 'modules' => [
             'backup' => [
                 'class' => 'floor12\backup\Module',
                 'administratorRoleName' => '@',
                 'configs' => [
                     [
                         'id' => 'main_db',
                         'type' => BackupType::DB,
                         'title' => 'Main database',
                         'connection' => 'db',
                         'limit' => 10
                     ],
                     [
                         'id' => 'main_storage',
                         'type' => BackupType::FILES,
                         'title' => 'TMP folder',
                         'path' => '@app/tmp',
                         'limit' => 2
                     ]
                 ]
             ]
             ],
         ],
     ...
 ```
