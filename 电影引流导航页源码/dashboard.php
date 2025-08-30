<?php
session_start();

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// 读取配置和浏览量
$config = json_decode(file_get_contents('config/site_config.json'), true);
$pageViews = file_get_contents('data/page_views.txt');
$buttons = json_decode(file_get_contents('data/buttons.json'), true);

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_site'])) {
        // 更新网站标题
        $config['site_title'] = $_POST['site_title'];
        $config['subtitle'] = $_POST['subtitle'];
        $config['footer_text'] = $_POST['footer_text'];
        file_put_contents('config极速影视/site_config.json', json_encode($config));
        $message = "网站信息已更新";
    } elseif (isset($_POST['update_account'])) {
        // 更新管理员账户
        $newUsername = $_POST['new_username'];
        $newPassword = $_POST['new_password'];
        file_put_contents('config/admin_account.txt', $newUsername . "\n" . $newPassword);
        $message = "管理员账户已更新";
    } elseif (isset($_POST['add_button'])) {
        // 添加新按钮
        $newButton = [
            'id' => uniqid(),
            'text' => $_POST['button_text'],
            'link' => $_POST['button_link'],
            'order' => count($buttons) + 1
        ];
        $buttons[] = $newButton;
        file_put_contents('data/buttons.json', json_encode($buttons));
        $message = "按钮已添加";
    } elseif (isset($_POST['update_buttons'])) {
        // 更新按钮顺序
        $updatedButtons = [];
        foreach ($_POST['button_order'] as $id => $order) {
            foreach ($buttons as $button) {
                if ($button['id'] == $id) {
                    $button['order'] = $order;
                    $updatedButtons[] = $button;
                    break;
                }
            }
        }
        // 按order排序
        usort($updatedButtons, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        file_put_contents('data/buttons.json', json_encode($updatedButtons));
        $buttons = $updatedButtons;
        $message = "按钮顺序已更新";
    } elseif (isset($_POST['delete_button'])) {
        // 删除按钮
        $buttonId = $_POST['button_id'];
        $updatedButtons = array_filter($buttons, function($button) use ($buttonId) {
            return $button['id'] != $buttonId;
        });
        file_put_contents('data/buttons.json', json_encode(array_values($updatedButtons)));
        $buttons = array_values($updatedButtons);
        $message = "按钮已删除";
    } elseif (isset($_POST['update_button'])) {
        // 更新按钮
        $buttonId = $_POST['button_id'];
        $updatedButtons = [];
        foreach ($buttons as $button) {
            if ($button['id'] == $buttonId) {
                $button['text'] = $_POST['button_text'];
                $button['link'] = $_POST['button_link'];
            }
            $updatedButtons[] = $button;
        }
        file_put_contents('data/buttons.json', json_encode($updatedButtons));
        $buttons = $updatedButtons;
        $message = "按钮已更新";
    }
}

// 处理文件上传
if (isset($_POST['upload_file'])) {
    if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "content/pages/";
        // 确保目录存在
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["uploaded_file"]["name"]);
        
        if (move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $target_file)) {
            $message = "文件已上传: " . htmlspecialchars(basename($_FILES["uploaded_file"]["name"]));
        } else {
            $message = "文件上传失败，请检查目录权限";
        }
    } else {
        $message = "请选择要上传的文件";
    }
}

// 处理文件删除
if (isset($_POST['delete_file'])) {
    $fileName = $_POST['file_name'];
    $filePath = "content/pages/" . $fileName;
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $message = "文件已删除: " . htmlspecialchars($fileName);
        } else {
            $message = "文件删除失败";
        }
    } else {
        $message = "文件不存在";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button { 
            padding: 10px 15px; 
            margin-right: 5px; 
            background: #eee; 
            border: none; 
            cursor: pointer; 
        }
        .tab-button.active { 
            background: #ff7e5f; 
            color: white; 
        }
        .button-list { 
            list-style: none; 
            margin: 15px 0; 
        }
        .button-list li { 
            padding: 10px; 
            background: #f9f9f9; 
            margin-bottom: 5px; 
            border-radius: 4px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .button-actions { 
            display: flex; 
            gap: 5px; 
        }
        .button-actions form { 
            margin: 0; 
        }
        .sortable-placeholder {
            height: 40px;
            background: #eee;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        .file-list {
            list-style: none;
            margin: 15px 0;
        }
        .file-list li {
            padding: 10px;
            background: #f9f9f9;
            margin-bottom: 5px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-actions {
            display: flex;
            gap: 5px;
        }
        .delete-btn {
            background: #f44336;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #d32f2f;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.极速影视14.0/Sortable.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>后台管理系统</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="dashboard">
            <div class极速影视="stats">
                <h2>网站统计</h2>
                <极速影视p>总浏览量: <?php echo $pageViews; ?></p>
            </div>
            
            <div class="admin-actions">
                <h2>内容管理</h2>
                
                <div class="tabs">
                    <button class="tab-button active" data-tab="edit-home">编辑主页</button>
                    <button class="tab-button" data-tab="edit-buttons">列表按钮编辑</button>
                    <button class="tab-button" data-tab="upload-files">上传文件</button>
                </div>
                
                <div id="edit-home" class="tab-content active">
                    <h3>编辑主页内容</h3>
                    <form method="post">
                        <div class="form-group">
                            <label for="site_title">主标题:</label>
                            <input type="text" id="site_title" name="site_title" value="<?php echo $config['site_title']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="subtitle">副标题:</label>
                            <input type="text" id="subtitle" name="subtitle" value="<?php echo $config['subtitle']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="footer_text">底部文本:</label>
                            <input type="text" id="footer_text极速影视" name="footer_text" value="<?php echo $config['footer_text']; ?>">
                        </div>
                        <button type="submit" name="update极速影视_site">更新主页内容</button>
                    </form>
                </div>
                
                <div id="edit-buttons" class="tab-content">
                    <h3>管理列表按钮</h3>
                    
                    <h4>添加新按钮</h4>
                    <form method="post">
                        <div class="form-group">
                            <label for="button_text">按钮文字:</label>
                            <input type="text" id="button_text" name="button_text" required>
                        </div>
                        <div class="form-group">
                            <label for="button_link">跳转页面:</label>
                            <input type="text" id="button_link" name="button_link" placeholder="例如: page1.html" required>
                        </div>
                        <button type="submit" name="add_button">添加按钮</button>
                    </form>
                    
                    <h4>当前按钮列表</h4>
                    <form method="post" id="buttons-order-form">
                        <ul id="buttons-list" class="button-list">
                            <?php foreach ($buttons as $button): ?>
                            <li data-id="<?php echo $button['id']; ?>">
                                <div>
                                    <strong><?php echo $button['text']; ?></strong>
                                    <br>
                                    <small>跳转至: <?php echo $button['link']; ?></small>
                                    <input type="hidden" name="button_order[<?php echo $button['id']; ?>]" value="<?php echo $button['order']; ?>">
                                </div>
                                <div class="button-actions">
                                    <button type="button" onclick="editButton('<?php echo $button['id']; ?>', '<?php echo $button['text']; ?>', '<?php echo $button['link']; ?>')">编辑</button>
                                    <form method="post">
                                        <input type="hidden" name="button_id" value="<?php echo $button['id']; ?>">
                                        <button type="submit" name="delete_button">删除</button>
                                    </form>
                                    <?php
                                    $pageName = pathinfo($button['link'], PATHINFO_FILENAME);
                                    if (file_exists("content/pages/{$button['link']}")) {
                                        echo '<a href="edit_content.php?page=' . $pageName . '" class="edit-page-btn">编辑页面</a>';
                                    } else {
                                        echo '<a href="edit_content.php?page=' . $pageName . '" class="edit-page-btn">创建页面</a>';
                                    }
                                    ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="submit" name="update_buttons">更新按钮顺序</button>
                    </form>
                    
                    <!-- 编辑按钮模态框 -->
                    <div id="edit-modal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 1000;">
                        <h3>编辑按钮</h3>
                        <form method="post">
                            <input type="hidden" id="edit_button_id" name="button_id">
                            <div class="form-group">
                                <label for="edit_button_text">按钮文字:</label>
                                <input type="text" id="edit_button_text" name="button_text" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_button_link">跳转页面:</label>
                                <input type="text" id="edit_button_link" name="button_link" required>
                            </div>
                            <button type="submit" name="update_button">更新按钮</button>
                            <button type="button" onclick="document.getElementById('edit-modal').style.display = 'none'">取消</button>
                        </form>
                    </div>
                </div>
                
                <div id="upload-files" class="tab-content">
                    <h3>上传文件</h3>
                    <p>上传的文件将保存在 content/pages/ 目录下，可用于按钮跳转的页面。</p>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="uploaded_file">选择文件:</label>
                            <input type="file" id="uploaded_file" name="uploaded_file" required>
                        </div>
                        <button type="submit" name="upload_file">上传文件</button>
                    </form>
                    
                    <h4>现有文件</h4>
                    <ul class="file-list">
                        <?php
                        if (file_exists('content/pages/')) {
                            $files = scandir('content/pages/');
                            foreach ($files as $file) {
                                if ($file !== '.' && $file !== '..') {
                                    echo '<li>' . htmlspecialchars($file) . ' 
                                        <div class="file-actions">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="file_name" value="' . htmlspecialchars($file) . '">
                                                <button type="submit" name="delete_file" class="delete-btn" onclick="return confirm(\'确定要删除此文件吗？\')">删除</button>
                                            </form>
                                            <a href="content/pages/' . htmlspecialchars($file) . '" target="_blank">查看</a> | 
                                            ' . (pathinfo($file, PATHINFO_EXTENSION) === 'html' ? 
                                                '<a href="edit_content.php?page=' . pathinfo($file, PATHINFO_FILENAME) . '">编辑</a>' : 
                                                '<span style="color:#999;">编辑</span>') . '
                                        </div>
                                    </li>';
                                }
                            }
                        } else {
                            echo '<li>content/pages/目录不存在</li>';
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="action-item">
                    <a href="index.php" target="_blank">查看前台</a> | 
                    <a href="?logout=true">退出登录</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 标签页切换
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // 移除所有active类
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // 添加active类到当前按钮和内容
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
        
        // 初始化排序功能
        new Sortable(document.getElementById('buttons-list'), {
            animation: 150,
            ghostClass: 'sortable-placeholder',
            onUpdate: function() {
                // 更新隐藏字段的顺序值
                const items = document.querySelectorAll('#buttons-list li');
                items.forEach((item, index) => {
                    const input = item.querySelector('input[type="hidden"]');
                    input.value = index + 1;
                });
            }
        });
        
        // 编辑按钮函数
        function editButton(id, text, link) {
            document.getElementById('edit_button_id').value = id;
            document.getElementById('edit_button_text').value = text;
            document.getElementById('edit_button_link').value = link;
            document.getElementById('edit-modal').style.display = 'block';
        }
    </script>
    
    <?php
    // 处理退出登录
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: admin.php');
        exit;
    }
    ?>
</body>
</html>