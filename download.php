<?php
$photoDirectory = 'pic/';  // 照片目录路径，替换为实际的目录路径

// 获取pic文件夹内的所有照片文件
$photos = [];
$files = scandir($photoDirectory);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $photos[] = $file;
    }
}

// 创建ZIP文件
$zip = new ZipArchive();
$zipName = 'photos.zip';
$zip->open($zipName, ZipArchive::CREATE);

// 添加照片到ZIP文件中，并将照片名称中的下划线替换为分号
foreach ($photos as $photo) {
    $modifiedName = str_replace('_', ';', $photo);
    $zip->addFile($photoDirectory . $photo, $modifiedName);
}

// 关闭ZIP文件
$zip->close();

// 设置响应头，以便浏览器下载ZIP文件
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipName));

// 输出ZIP文件内容
readfile($zipName);

// 删除服务器上的ZIP文件
unlink($zipName);
?>