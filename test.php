<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//найти и убить файлы от удаленных товаров (или от старого сайта),  которые лежат вместе с картинками товаров в одной директории на диске
//
//Есть сайт на битриксе, в каталоге сто тысяч товаров
//у каждого есть картинки две родные (PREVIEW_PICTURE и DETAIL_PICTURE)
//и дополнительно могут быть поля типа файл (в том числе множественные)
//задача:
//вычистить все файлы, у которых нет никаких связей с элементами инфоблоков  (по факту обычно это файлы от удаленных товаров или от старого сайта)
//1) нужно написать алгоритм как будешь решать
//2) написать такое решение
//Задачу можно реализовывать как standalone, так и используя Bitrix API

set_time_limit(0);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
global $DB;

$arFilesCache = array();
$result = $DB->Query('SELECT FILE_NAME, SUBDIR FROM b_file WHERE MODULE_ID = "iblock"');
while ($row = $result->Fetch()) {
    $arFilesCache[ $row['FILE_NAME'] ] = $row['SUBDIR'];
}

$rootDirPath = $_SERVER['DOCUMENT_ROOT'] . "/upload/iblock";
$hRootDir = opendir($rootDirPath);
$count = 0;

$hSubDir = opendir($rootDirPath);
while (false !== ($fileName = readdir($hSubDir))) {
    if ($fileName == '.' || $fileName == '..')
        continue;
    if (array_key_exists($fileName, $arFilesCache)) {
        continue;
    }
    $fullPath = "$rootDirPath";
    //   if (unlink($fullPath)) { тестовый режим
    echo 'Для удаления: ' . $fullPath . PHP_EOL;
    $count++;
    //   }
}
closedir($hSubDir);

while (false !== ($subDirName = readdir($hRootDir))) {
    if ($subDirName == '.' || $subDirName == '..')
        continue;
    $subDirPath = "$rootDirPath/$subDirName";
    $hSubDir = opendir($subDirPath);
    while (false !== ($fileName = readdir($hSubDir))) {
        if ($fileName == '.' || $fileName == '..')
            continue;
        if (array_key_exists($fileName, $arFilesCache)) {
            continue;
        }
        $fullPath = "$subDirPath/$fileName";
        //   if (unlink($fullPath)) { тестовый режим
        echo 'Для удаления: ' . $fullPath . PHP_EOL;
        $count++;
        //   }
    }
    closedir($hSubDir);
}
closedir($hRootDir);

echo 'Всего: ' . $count;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
