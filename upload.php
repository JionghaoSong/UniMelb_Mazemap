<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['result' => false, 'msg' => 'Invalid request.', 'data' => []]);
    exit;
}

if (!isset($_FILES['photos'])) {
    echo json_encode(['result' => false, 'msg' => 'No files uploaded.', 'data' => []]);
    exit;
}

$targetDir = 'pic/';

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$uploadedFiles = $_FILES['photos'];
$numFiles = count($uploadedFiles['name']);

$errLog = [];

for ($i = 0; $i < $numFiles; $i++) {
    $tempFilePath = $uploadedFiles['tmp_name'][$i];
    $originalFileName = $uploadedFiles['name'][$i];
    
    // 更改文件名：将分号（;）替换为下划线（_）
    $newFileName = str_replace(';', '_', $originalFileName);

    $targetFilePath = $targetDir . $newFileName;

    // 获取文件扩展名
    $fileExtension = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // 如果是jpg或png且文件大小大于1MB，进行压缩
    if (($fileExtension == 'jpg' || $fileExtension == 'png') && filesize($tempFilePath) > 1000000) {
        compressImage($tempFilePath, $targetFilePath, $fileExtension);
    } else {
        // 否则直接保存
        move_uploaded_file($tempFilePath, $targetFilePath);
    }

    // 检查保存结果
    if (!file_exists($targetFilePath)) {
        $errLog[] = $originalFileName;
    }
}

if (empty($errLog)) {
    echo json_encode(['result' => true, 'msg' => 'all success', 'data' => []]);
    exit;
}

echo json_encode(['result' => false, 'msg' => 'occur err', 'data' => $errLog]);
exit;

// 函数用于压缩图片
function compressImage($source, $destination, $fileExtension) {
    list($width, $height) = getimagesize($source);

    // 创建一个新的图像资源
    $newWidth = 800; // 设置压缩后的宽度
    $newHeight = ($height / $width) * $newWidth;

    $image = imagecreatetruecolor($newWidth, $newHeight);

    // 根据文件扩展名选择图像创建函数
    switch ($fileExtension) {
        case 'jpg':
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case 'png':
            $sourceImage = imagecreatefrompng($source);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        // 图像加载失败
        return false;
    }

    // 将原图复制到新的图像资源中
    $result = imagecopyresampled($image, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (!$result) {
        // 图像处理失败
        return false;
    }

    // 根据文件扩展名保存压缩后的图像
    switch ($fileExtension) {
        case 'jpg':
            $result = imagejpeg($image, $destination, 90); // 保存为jpg，质量为90%
            break;
        case 'png':
            $result = imagepng($image, $destination, 5); // 保存为png，压缩级别为5
            break;
    }

    imagedestroy($image);

    return $result;
}

?>
