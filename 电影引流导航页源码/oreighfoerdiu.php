<?php
// 使用相对于当前脚本的路径
$file_path = __DIR__ . '/data/page_views.txt';
$message = '';
$content = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['content'])) {
        try {
            // 尝试写入文件
            $result = file_put_contents($file_path, $_POST['content']);
            if ($result !== false) {
                $message = '文件已成功保存！';
                $content = $_POST['content'];
            } else {
                $message = '错误：无法保存文件。请检查文件权限。';
            }
        } catch (Exception $e) {
            $message = '错误：保存文件时发生异常 - ' . $e->getMessage();
        }
    }
}

// 读取文件内容（如果是GET请求或保存后）
if (empty($content)) {
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        if ($content === false) {
            $message = '错误：无法读取文件。请检查文件是否存在和权限设置。';
            $content = '';
        }
    } else {
        $message = '注意：文件不存在，将创建新文件。';
        $content = '';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文本文件编辑器</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        textarea {
            width: 100%;
            height: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            resize: vertical;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>文本文件编辑器</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, '错误') !== false ? 'error' : (strpos($message, '成功') !== false ? 'success' : 'info'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <textarea name="content" placeholder="在此编辑文件内容..."><?php echo htmlspecialchars($content); ?></textarea>
            <br>
            <button type="submit">保存文件</button>
        </form>
    </div>
</body>
</html>