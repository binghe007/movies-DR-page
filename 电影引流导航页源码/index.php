<?php
// 读取配置
$config = json_decode(file_get_contents('config/site_config.json'), true);
// 读取按钮数据
$buttons = json_decode(file_get_contents('data/buttons.json'), true);
// 读取浏览量
$pageViews = file_get_contents('data/page_views.txt');
// 增加浏览量
file_put_contents('data/page_views.txt', $pageViews + 1);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport极速影视" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['site_title']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">🎬</div>
            <h1><?php echo $config['site_title']; ?></h1>
            <p><?php echo $config['subtitle']; ?></p>
        </header>
        
        <div class="content-list">
            <?php foreach ($buttons as $button): ?>
            <a href="content/pages/<?php echo $button['link']; ?>" class="content-item-link">
                <div class="content-item">
                    <span class="item-title"><?php echo $button['text']; ?></span>
                    <span class="arrow">→</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <footer>
            <p><?php echo $config['footer_text']; ?></p>
        </footer>
        
        <div class="admin-link">
            <a href="admin.php">管理员入口</a>
        </div>
    </div>
</body>
</html>