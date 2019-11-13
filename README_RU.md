# yii2-module-backup

[![Build Status](https://travis-ci.org/floor12/yii2-module-files.svg?branch=master)](https://travis-ci.org/floor12/yii2-module-backup)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/floor12/yii2-module-backup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/floor12/yii2-module-backup/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/floor12/yii2-module-backup/v/stable)](https://packagist.org/packages/floor12/yii2-module-backup)
[![Latest Unstable Version](https://poser.pugx.org/floor12/yii2-module-backup/v/unstable)](https://packagist.org/packages/floor12/yii2-module-backup)
[![Total Downloads](https://poser.pugx.org/floor12/yii2-module-backup/downloads)](https://packagist.org/packages/floor12/yii2-module-backup)
[![License](https://poser.pugx.org/floor12/yii2-module-backup/license)](https://packagist.org/packages/floor12/yii2-module-backup)

## Функции модуля

Это модуль разработан чтобы организовать работу с бекапами вашего приложения силами самого Yii2
 фреймворка. С помощью этого модуля возможно создавать, восстанавливать, удалять и скачивать бекапы файлов и баз данных 
 при помощь веб-интерфейса, консольного интерфейса и REST-API. Для того, чтобы не зависить от базы данных приложения, модуль использует свою
 sqlite базу, размещенную в папке для хранения бекапов.

### i18n
На данный момент модуль поддерживает русский и английский языки.
 
![Yii backup module](https://floor12.net/files/default/get?hash=4895685e3392ade4e0e2a40a762bc4fe)

## Установка

Чтобы добавить модуль в свое приложение выполните команду:
 ```bash
 $ composer require floor12/yii2-module-backup
 ```
или добавьте эту строку в секцию `require` вашего composer.json.
 ```json
 "floor12/yii2-module-backup": "dev-master"
 ```
 
 После этого необходимо зарегистрировать модуль с необходимыми параметрами в секции `modules` конфига приложения:
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

Параметры работы модуля, которые можно задать через конфиг приложения:
- `administratorRoleName` - роль, которой разрешен доступ в web-контроллер модуля
- `backupFolder` - alias к папке для хранения бекапов (по умолчанию @app/backups)
- `chmod` -  если этот параметр задан, команда chmod с параметрами из значения этого поля будет выполнена после создания бекапа
- `authTokens` - массив токенов для доступа к REST-контроллеру
- `ionice` - значение этого параметра будет прописано перед командой запуска бекапа (например `iotince -c3
` помещенное в этот параметр позволит запускать бекап с приоритетом IDLE для IO диска)
 - `adminLayout` - смена дефотного  `main` лейаута на любой другой по необходимости
 
И главный и необходимый параметр конфигурации это`configs` - в этом массиве перечислены объекты для бекапа (базы данных и папки).
 Каждый элемент должен содержать в себе следующие поля:
 - `id` - индификатор бекапа без пробелов (например main_db, files_folder, user_images_data и т.д.)
 - `type` - тип бекапа: база данных или папка на диска
 - `title` - читаемое название для бекапа для отображения в веб-интерфейсе
 - `limit` - кол-во сохраненных копий (`0` - никогда не удалять старые копии)
 - `connection` - в случае бекапа базы данных здесь необходимо указать имя соединения базы данных в Yii2 приложении (по дефолту это 'db')
 - `path` - алиас пути для бекапа, в случае бекапа папки на диске
 
    
## Использование

### WEB-интерфейс

Модуль иметт веб-интерфейс для работы с бекапами. Но доступен по адресу `backup/admin` or `backup/admin/index`.
С его помощью воможно создавать, восстанавливать, удалять и скачивать бекапы.

 ### Консольный интерфейс
 
*Для просмотра списка всех существубщих бекапов выполните*
 ```bash
$ ./yii backup/console/index>
```

*Для создания нового бекапа выполните*
 ```bash
$ ./yii backup/console/create <backup_config_id>
```
`backup_config_id` это индификатор конфигурации бекапа.


*Для восстановления из бекапа выполните*
 ```bash
$ ./yii backup/console/restore <backup_id>
```
`backup_id` это индификатор существующего бекапа

### REST-api

По умолчанию REST-контроллер доступен по адресу `/backup/api`.
Для доступа к нему необходимо доабвить к запросу заголовок`Backup-Auth-Token
` содержащий один из токенов, указанных в конфиге модуля (параметр `authTokens`);


#### Список существующих бекапов
`GET /backup/api/index` 

Пример ответа
```json
[
  {
    "id": 8,
    "date": "2019-11-11 07:02:23",
    "status": 1,
    "type": 1,
    "config_id": "main_storage",
    "config_name": "TMP folder",
    "filename": "main_storage_2019-11-11_07-02-23.zip",
    "size": 4183
  },
  {
    "id": 7,
    "date": "2019-11-11 06:56:36",
    "status": 1,
    "type": 0,
    "config_id": "main_db",
    "config_name": "Main database",
    "filename": "main_db_2019-11-11_06-56-36.gz",
    "size": 753
  },

]
```


#### Создание нового бекапа
`POST /backup/api/backup?config_id=<backup_config_id>` 

Успешный ответ:
```json
{"result":"success"}
```

#### Восстановление из бекапа
`POST /backup/api/restore?id=<backup_id>` 

Успешный ответ:
```json
{"result":"success"}
```

#### Удаление бекапа
`DELETE /backup/api/delete?id=<backup_id>` 

Успешный ответ:
```json
{"result":"success"}
```

#### Получение файла бекапа
`GET /backup/api/get?id=<backup_id>` 

В ответ будет отдан файл с бекапом.
