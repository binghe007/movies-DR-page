<?php
session_start();

// 设置会话参数，防止重定向循环
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// 检查是否已登录
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // 添加额外的检查防止重定向循环
    $current_script = basename($_SERVER['PHP_SELF']);
    if ($current_script !== 'dashboard.php') {
        header('Location: dashboard.php');
        exit;
    }
}

// 读取管理员账户信息
$adminAccount = @file('config/admin_account.txt');
if (!$adminAccount || count($adminAccount) < 2) {
    // 如果账户文件不存在或格式错误，创建默认账户
    file_put_contents('config/admin_account.txt', "admin\npassword123");
    $adminAccount = ["admin", "password123"];
}
$username = trim($adminAccount[0]);
$password = trim($adminAccount[1]);

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';
    
    if ($inputUsername === $username && $inputPassword === $password) {
        $_SESSION['admin_logged_in'] = true;
        // 清除可能存在的旧会话数据
        session_regenerate_id(true);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "用户名或密码错误";
    }
}

// 检查是否有退出请求
if (isset($_GET['logout'])) {
    session_destroy();
    // 重定向到登录页时添加参数避免循环
    header('Location: admin.php?logged_out=true');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2>管理员登录</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['logged_out'])): ?>
                <div class="message">您已成功退出登录</div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">用户名:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">登录</button>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <h4>默认登录信息：</h4>
                <p>用户名: <strong>admin</strong></p>
                <p>密码: <strong>password123</strong></p>
            </div>
        </div>
    </div>
</body>
</html>