<?php
if ($argc < 3) {
    exit("Использование: php main.php <имя_файла> <директория_для_сохранения>\n");
}

$inputFile = $argv[1];
$outputDir = $argv[2];

if (!file_exists($inputFile)) {
    exit("Файл $inputFile не найден.\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$htmlFile = file_get_contents($inputFile);
$pattern = '/<img[^>]+src="([^"]+)"[^>]*>/i';

$index = 0;
$newHtmlFile = preg_replace_callback($pattern, function($matches) use ($inputFile, $outputDir, &$index) {
    $src = $matches[1];

    #Если путь абсолютный, то заменяю его на newdomain.com/images/
    #Если путь относительный, то копирую файл в новую директорию,
    #заменяю его название и заменяю путь в src
    if (preg_match('/^https?:\/\//', $src)) {
        $src = preg_replace('/^(https?:\/\/).*\//', '$1newdomain.com/images/', $src);
    } else {
        if (file_exists($src)) {
            $fileExtension = pathinfo($src, PATHINFO_EXTENSION);
            $newFileName = $index . '.' . $fileExtension;
            $newFilePath = $outputDir . '/' . $newFileName;
            copy($src, $newFilePath);
            $src = $newFilePath;
            $index++;
        } else {
            echo "Файл отсутствует в директории: " . $src . "\n";
        }
    }
    return str_replace($matches[1], $src, $matches[0]);

}, $htmlFile);

$outputFile = 'result_' . basename($inputFile);
file_put_contents($outputFile, $newHtmlFile);
echo "Результат сохранен в файл $outputFile\n";
?>