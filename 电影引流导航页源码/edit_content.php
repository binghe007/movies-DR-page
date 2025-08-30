<?php
session_start();

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// 获取要编辑的页面名称
$page = isset($_GET['page']) ? $_GET['page'] : 'default';
$filePath = "content/pages/{$page}.html";

// 如果文件不存在，创建它
if (!file_exists($filePath)) {
    // 确保目录存在
    if (!file_exists('content/pages/')) {
        mkdir('content/pages/', 0777, true);
    }
    file_put_contents($filePath, "<h1>" . htmlspecialchars($page) . "</h1>\n<p>这是" . htmlspecialchars($page) . "页面的内容。</p>");
}

// 读取当前内容
$currentContent = file_get_contents($filePath);

// 处理内容保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    file_put_contents($filePath, $_POST['content']);
    $message = "内容已保存";
    $currentContent = $_POST['content'];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑内容 - <?php echo htmlspecialchars($page); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .editor-actions {
            margin: 15px 0;
            display: flex;
            gap: 10px;
        }
        .editor-actions a {
            padding: 8px 15px;
            background: #eee;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .editor-actions a:hover {
            background: #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>编辑内容: <?php echo htmlspecialchars($page); ?></h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="editor-actions">
            <a href="dashboard.php">返回管理面板</a>
            <a href="content/pages/<?php echo htmlspecialchars($page); ?>.html" target="_blank">查看页面</a>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="content">页面内容 (支持HTML):</label>
                <textarea id="content" name="content" rows="20" style="width: 100%; font-family: monospace;" required><?php echo htmlspecialchars($currentContent); ?></textarea>
            </div>
            <button type="submit">保存内容</button>
        </form>
    </div>
</body>
</html>