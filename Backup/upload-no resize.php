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

    $saveResult = move_uploaded_file($tempFilePath, $targetFilePath);

    if (!$saveResult) {
        $errLog[] = $originalFileName;
    }
}

if (empty($errLog)) {
    echo json_encode(['result' => true, 'msg' => 'all success', 'data' => []]);
    exit;
}

echo json_encode(['result' => false, 'msg' => 'occur err', 'data' => $errLog]);
exit;
?>