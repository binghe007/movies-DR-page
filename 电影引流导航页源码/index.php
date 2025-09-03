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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['site_title'] ?? ''); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* 统一橙色按钮，文本白色；竖向单列布局；背景尽量与全局背景协调 */
        :root {
            --orange: #ff7e5f;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue",
                         Arial, "Noto Sans", sans-serif;
            background: #ffffff; /* 全局背景白色，确保一致性 */
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
            padding: 16px;
        }

        header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 0 20px;
            border-bottom: 1px solid #e5e5e5;
            background: #fff;
            border-radius: 8px;
        }
        .logo {
            font-size: 40px;
            line-height: 1;
            margin-bottom: 6px;
        }
        header h1 {
            margin: 0;
            font-size: clamp(1.4rem, 2.5vw, 2rem);
        }
        header p {
            margin: 4px 0 0;
            color: #666;
        }

        /* 竖直排布：单列网格，行高适中，按钮占满整列宽度 */
        .content-list {
            display: grid;
            grid-template-columns: 1fr; /* 单列，竖直排列 */
            gap: 12px;
            padding: 12px 0 0;
        }
        .content-item-link {
            text-decoration: none;
            color: inherit;
        }
        /* 按钮：橙色背景，白色文字，圆角，整列宽高自适应 */
        .content-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            border-radius: 12px;
            background: var(--orange);
            color: #fff;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .content-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(0,0,0,.08);
        }
        .item-title {
            font-weight: 600;
            color: #fff;
        }
        .arrow { color: #fff; }

        footer {
            text-align: center;
            padding: 14px 0;
            color: #666;
            font-size: 14px;
        }

        /* 适配：桌面端也保持单列，确保“竖版”效果 */
        @media (min-width: 600px) {
            /* 仍然保持单列，不再分两列 */
            .content-list { grid-template-columns: 1fr; }
        }

        @media (min-width: 1024px) {
            .content-item { padding: 16px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo" aria-label="Logo">🎬</div>
            <h1><?php echo htmlspecialchars($config['site_title'] ?? ''); ?></h1>
            <p><?php echo htmlspecialchars($config['subtitle'] ?? ''); ?></p>
        </header>

        <main class="content-list" aria-label="内容列表">
            <?php if (is_array($buttons)) foreach ($buttons as $button): ?>
            <a href="content/pages/<?php echo htmlspecialchars($button['link'] ?? ''); ?>" class="content-item-link">
                <div class="content-item">
                    <span class="item-title"><?php echo htmlspecialchars($button['text'] ?? ''); ?></span>
                    <span class="arrow">→</span>
                </div>
            </a>
            <?php endforeach; ?>
        </main>

        <footer>
            <p><?php echo htmlspecialchars($config['footer_text'] ?? ''); ?></p>
        </footer>
    </div>
</body>
</html>
