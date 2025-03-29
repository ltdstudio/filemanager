<!DOCTYPE html><html><head><title>DZG的文件管理调试系统</title><style>
    body { background-color: #e6f0fa; font-family: Arial, sans-serif; margin: 20px; }
    h1, h3 { color: #4682b4; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; background-color: #f0f8ff; }
    th, td { padding: 10px; border: 1px solid #b0c4de; text-align: left; }
    th { background-color: #87ceeb; color: #fff; }
    tr:nth-child(even) { background-color: #f5faff; }
    a { color: #4682b4; text-decoration: none; }
    a:hover { text-decoration: underline; }
    input[type="text"], input[type="file"], textarea { padding: 8px; border: 1px solid #b0c4de; border-radius: 5px; margin: 5px 0; }
    input[type="submit"], button { padding: 10px 20px; background: linear-gradient(#87ceeb, #4682b4); color: white; border: none; border-radius: 5px; cursor: pointer; }
    input[type="submit"]:hover, button:hover { background: linear-gradient(#b0e0e6, #5f9ea0); }
    .batch-btn { padding: 10px 20px; background: linear-gradient(#b0e0e6, #87ceeb); color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; }
    .batch-btn:hover { background: linear-gradient(#d6e6f5, #b0e0e6); }
    .delete-btn { padding: 8px 16px; background: #ff4444; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 0 5px; }
    .delete-btn:hover { background: #cc0000; }
    .cancel-btn { padding: 8px 16px; background: #cccccc; color: black; border: none; border-radius: 5px; cursor: pointer; margin: 0 5px; }
    .cancel-btn:hover { background: #999999; }
    .info-icon { cursor: pointer; color: #4682b4; font-size: 16px; margin-left: 5px; }
    progress { width: 100%; margin: 5px 0; }
    .github-editor { max-width: 900px; margin: 20px auto; }
    .tabs { border-bottom: 1px solid #b0c4de; margin-bottom: 10px; }
    .tab { display: inline-block; padding: 10px 20px; cursor: pointer; color: #4682b4; }
    .tab.active { border-bottom: 2px solid #4682b4; color: #4682b4; }
    .content { border: 1px solid #b0c4de; padding: 10px; background: #f0f8ff; }
    #editor { width: 100%; height: 400px; border: none; padding: 10px; font-family: monospace; }
    #preview { white-space: pre-wrap; }
    .commit-section { margin-top: 20px; padding: 10px; border: 1px solid #b0c4de; background: #fff; text-align: right; }
    .commit-section input[type="submit"] { margin-left: 15px; }
    .commit-section .save-backup { margin-left: 15px; }
    .back-btn, .logout-btn { padding: 10px 20px; background: linear-gradient(#556B2F, #2F4F4F); color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 15px; text-decoration: none; display: inline-block; }
    .back-btn:hover, .logout-btn:hover { background: linear-gradient(#6B8E23, #4A6868); }
    .CodeMirror { height: 400px; border: 1px solid #b0c4de; }
    #dirTreeModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
    #dirTreeContent { background: #fff; margin: 15% auto; padding: 20px; width: 50%; max-height: 70%; overflow-y: auto; border-radius: 5px; }
</style></head><body><?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Set timezone to UTC+8
date_default_timezone_set('Asia/Shanghai');

// Root directory for file manager
$rootDir = '/var/www/html'; // 请根据你的服务器路径调整
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $rootDir;
$currentDir = realpath($currentDir);

// Ensure currentDir is within rootDir
if (strpos($currentDir, realpath($rootDir)) !== 0) {
    $currentDir = $rootDir;
}

// Language handling
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'] === 'en' ? 'en' : 'zh';
    header('Location: ?action=list&dir=' . urlencode($currentDir));
    exit;
}
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'zh';
$translations = [
    'zh' => [
        'title' => 'DZG的文件管理调试系统',
        'current_dir' => '当前目录: ',
        'parent_dir' => '.. (父目录)',
        'create_folder' => '创建新文件夹',
        'folder_name' => '文件夹名称',
        'create' => '创建',
        'file_list' => '文件列表',
        'select' => '选择',
        'name' => '名称',
        'permissions' => '权限',
        'owner' => '所有者',
        'size' => '大小',
        'created' => '创建时间',
        'modified' => '修改时间',
        'actions' => '操作',
        'delete' => '删除',
        'edit' => '编辑',
        'rename' => '重命名',
        'upload_file' => '上传文件',
        'max_size' => '最大文件大小: ',
        'max_files' => '最大文件数量: ',
        'upload' => '上传',
        'batch_delete' => '删除所选',
        'batch_move' => '移动到',
        'batch_copy' => '复制到',
        'confirm_batch_delete' => '确认删除所选文件吗？',
        'select_target_dir' => '选择目标目录',
        'login' => '登录',
        'username' => '用户名',
        'password' => '密码',
        'save_password' => '保存密码',
        'submit' => '提交',
        'invalid_action' => '无效操作',
        'power_by' => 'Power By DZG | Write by AI | V3.5',
        'save' => '提交',
        'save_backup' => '提交并保存备份',
        'confirm_folder_delete' => '确认删除此文件夹及其所有内容吗？',
        'new_name' => '新名称',
        'yes' => '是',
        'no' => '否',
        'edit_tab' => '编辑',
        'preview_tab' => '预览',
        'visit' => '访问',
        'no_files_selected' => '未选择任何文件',
        'timezone' => '时区: UTC+8',
        'server_time' => '服务器时间：',
        'edit_title' => 'DZG的文件简易编辑器 - 正在编辑 ',
        'back_to_list' => '放弃回前页',
        'lang_zh' => '中文',
        'lang_en' => 'English',
        'logout' => '登出',
        'cancel' => '取消',
        'change_bg_color' => '更改背景颜色',
        'compatibility_note' => '兼容性提示: 支持现代浏览器（Chrome, Firefox, Edge）',
        'lock' => '加锁',
        'unlock' => '解锁',
    ],
    'en' => [
        'title' => 'DZG File Management Debugging System',
        'current_dir' => 'Current Directory: ',
        'parent_dir' => '.. (Parent Directory)',
        'create_folder' => 'Create New Folder',
        'folder_name' => 'Folder Name',
        'create' => 'Create',
        'file_list' => 'File List',
        'select' => 'Select',
        'name' => 'Name',
        'permissions' => 'Permissions',
        'owner' => 'Owner',
        'size' => 'Size',
        'created' => 'Created',
        'modified' => 'Modified',
        'actions' => 'Actions',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'rename' => 'Rename',
        'upload_file' => 'Upload Files',
        'max_size' => 'Maximum File Size: ',
        'max_files' => 'Maximum File Count: ',
        'upload' => 'Upload',
        'batch_delete' => 'Delete Selected',
        'batch_move' => 'Move To',
        'batch_copy' => 'Copy To',
        'confirm_batch_delete' => 'Are you sure to delete the selected files?',
        'select_target_dir' => 'Select Target Directory',
        'login' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'save_password' => 'Save Password',
        'submit' => 'Submit',
        'invalid_action' => 'Invalid Action',
        'power_by' => 'Power By DZG | Write by AI | V3.5',
        'save' => 'Save',
        'save_backup' => 'Commit and Backup',
        'confirm_folder_delete' => 'Are you sure to delete this folder and all its contents?',
        'new_name' => 'New Name',
        'yes' => 'Yes',
        'no' => 'No',
        'edit_tab' => 'Edit',
        'preview_tab' => 'Preview',
        'visit' => 'Visit',
        'no_files_selected' => 'No files selected',
        'timezone' => 'Timezone: UTC+8',
        'server_time' => 'Server Time: ',
        'edit_title' => 'DZG Simple File Editor - Editing ',
        'back_to_list' => 'Discard and Return',
        'lang_zh' => 'Chinese',
        'lang_en' => 'English',
        'logout' => 'Logout',
        'cancel' => 'Cancel',
        'change_bg_color' => 'Change Background Color',
        'compatibility_note' => 'Compatibility Note: Supports modern browsers (Chrome, Firefox, Edge)',
        'lock' => 'Lock',
        'unlock' => 'Unlock',
    ],
];
$t = $translations[$language];

// Helper functions
function permissionsToString($file) {
    $perms = fileperms($file);
    $info = '';
    $info .= (is_dir($file)) ? 'd' : '-';
    $info .= ($perms & 00400) ? 'r' : '-';
    $info .= ($perms & 00200) ? 'w' : '-';
    $info .= ($perms & 00100) ? 'x' : '-';
    $info .= ($perms & 00040) ? 'r' : '-';
    $info .= ($perms & 00020) ? 'w' : '-';
    $info .= ($perms & 00010) ? 'x' : '-';
    $info .= ($perms & 00004) ? 'r' : '-';
    $info .= ($perms & 00002) ? 'w' : '-';
    $info .= ($perms & 00001) ? 'x' : '-';
    return $info;
}

function get_owner($path) {
    $uid = fileowner($path);
    $user = posix_getpwuid($uid);
    return $user['name'];
}

function format_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 0) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 0) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 0) . ' KB';
    } else {
        return number_format($bytes, 0) . ' bytes';
    }
}

function get_dir_size($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : get_dir_size($each);
    }
    return $size;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . '/' . $item)) return false;
    }
    return rmdir($dir);
}

function getDirectoryTree($dir, $prefix = '', $isRoot = true) {
    $tree = '';
    if ($isRoot) {
        $tree .= '<a href="#" onclick="selectDir(\'' . urlencode($dir) . '\')">' . htmlspecialchars(basename($dir)) . '</a><br>';
    }
    $entries = array_diff(scandir($dir), array('.', '..'));
    $dirs = array_filter($entries, function($e) use ($dir) { return is_dir($dir . '/' . $e); });
    natsort($dirs);
    foreach ($dirs as $index => $folder) {
        $fullpath = $dir . '/' . $folder;
        $isLast = $index === count($dirs) - 1;
        $tree .= $prefix . ($isLast ? '└── ' : '├── ') . '<a href="#" onclick="selectDir(\'' . urlencode($fullpath) . '\')">' . htmlspecialchars($folder) . '</a><br>';
        $tree .= getDirectoryTree($fullpath, $prefix . ($isLast ? '    ' : '│   '), false);
    }
    return $tree;
}

function isLocked($file) {
    $perms = fileperms($file);
    return ($perms & 0222) == 0; // 检查是否有写权限，没有则视为加锁
}

// Logout handling
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ?');
    exit;
}

// Login handling
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if ($username == 'admin' && $password == 'admin') {
            $_SESSION['logged_in'] = true;
            if (isset($_POST['save_password'])) {
                setcookie('username', $username, time() + 30 * 24 * 3600);
                setcookie('password', $password, time() + 30 * 24 * 3600);
            } else {
                setcookie('username', '', time() - 3600);
                setcookie('password', '', time() - 3600);
            }
            header('Location: ?action=list');
            exit;
        } else {
            $error = $t['login'] . ' Failed';
        }
    }
    echo '<!DOCTYPE html><html><head><title>' . $t['title'] . '</title>';
    echo '<style>
        body { background-color: #e6f0fa; font-family: Arial, sans-serif; margin: 20px; text-align: center; }
        h1 { color: #4682b4; }
        h2 { color: #4682b4; }
        .login-box { background-color: #d6e6f5; padding: 20px; border-radius: 10px; display: inline-block; margin-top: 20px; }
        input[type="text"], input[type="password"] { padding: 8px; border: 1px solid #b0c4de; border-radius: 5px; margin: 5px 0; width: 200px; }
        input[type="submit"] { padding: 10px 20px; background: linear-gradient(#87ceeb, #4682b4); color: white; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background: linear-gradient(#b0e0e6, #5f9ea0); }
        a { color: #4682b4; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style></head><body>';
    echo '<h1>' . $t['title'] . '</h1>';
    echo '<div class="login-box">';
    echo '<h2>' . $t['login'] . '</h2>';
    if (isset($error)) {
        echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
    }
    echo '<form method="post" action="">';
    echo '<label for="username">' . $t['username'] . ':</label><br>';
    echo '<input type="text" id="username" name="username" value="' . (isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : '') . '"><br>';
    echo '<label for="password">' . $t['password'] . ':</label><br>';
    echo '<input type="password" id="password" name="password" value="' . (isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : '') . '"><br>';
    echo '<input type="checkbox" id="savePassword" name="save_password" ' . (isset($_COOKIE['username']) ? 'checked' : '') . '> ' . $t['save_password'] . '<br>';
    echo '<input type="submit" name="login" value="' . $t['submit'] . '">';
    echo '</form>';
    echo '</div>';
    echo '<div style="text-align: center; margin-top: 20px;">';
    echo '<a href="?lang=zh">🇨🇳 ' . $t['lang_zh'] . '</a> | <a href="?lang=en">🇬🇧 ' . $t['lang_en'] . '</a>';
    echo '</div>';
    echo '<div style="text-align: center; margin-top: 20px; color: #4682b4;">';
    echo $t['power_by'];
    echo '</div>';
    echo '<div style="text-align: center; margin-top: 10px; color: #4682b4;">';
    echo $t['timezone'] . ' ' . $t['server_time'] . ' <span id="serverTime">' . date('Y-m-d H:i:s') . '</span>';
    echo '</div>';
    echo '<script>
        function updateServerTime() {
            var now = new Date();
            var offset = 8 * 60 * 60 * 1000;
            var localTime = now.getTime() + offset;
            var date = new Date(localTime);
            var timeStr = date.getUTCFullYear() + "-" + 
                          ("0" + (date.getUTCMonth() + 1)).slice(-2) + "-" + 
                          ("0" + date.getUTCDate()).slice(-2) + " " + 
                          ("0" + date.getUTCHours()).slice(-2) + ":" + 
                          ("0" + date.getUTCMinutes()).slice(-2) + ":" + 
                          ("0" + date.getUTCSeconds()).slice(-2);
            document.getElementById("serverTime").innerText = timeStr;
        }
        setInterval(updateServerTime, 1000);
    </script>';
    echo '</body></html>';
    exit;
}

// Start output buffering
ob_start();

echo '<!DOCTYPE html><html><head><title>' . $t['title'] . '</title>';
echo '<style>
    body { background-color: #e6f0fa; font-family: Arial, sans-serif; margin: 20px; }
    h1, h3 { color: #4682b4; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; background-color: #f0f8ff; }
    th, td { padding: 10px; border: 1px solid #b0c4de; text-align: left; }
    th { background-color: #87ceeb; color: #fff; }
    tr:nth-child(even) { background-color: #f5faff; }
    a { color: #4682b4; text-decoration: none; }
    a:hover { text-decoration: underline; }
    input[type="text"], input[type="file"], textarea { padding: 8px; border: 1px solid #b0c4de; border-radius: 5px; margin: 5px 0; }
    input[type="submit"], button { padding: 10px 20px; background: linear-gradient(#87ceeb, #4682b4); color: white; border: none; border-radius: 5px; cursor: pointer; }
    input[type="submit"]:hover, button:hover { background: linear-gradient(#b0e0e6, #5f9ea0); }
    .batch-btn { padding: 10px 20px; background: linear-gradient(#b0e0e6, #87ceeb); color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; }
    .batch-btn:hover { background: linear-gradient(#d6e6f5, #b0e0e6); }
    .delete-btn { padding: 8px 16px; background: #ff4444; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 0 5px; }
    .delete-btn:hover { background: #cc0000; }
    .cancel-btn { padding: 8px 16px; background: #cccccc; color: black; border: none; border-radius: 5px; cursor: pointer; margin: 0 5px; }
    .cancel-btn:hover { background: #999999; }
    .info-icon { cursor: pointer; color: #4682b4; font-size: 16px; margin-left: 5px; }
    progress { width: 100%; margin: 5px 0; }
    .github-editor { max-width: 900px; margin: 20px auto; }
    .tabs { border-bottom: 1px solid #b0c4de; margin-bottom: 10px; }
    .tab { display: inline-block; padding: 10px 20px; cursor: pointer; color: #4682b4; }
    .tab.active { border-bottom: 2px solid #4682b4; color: #4682b4; }
    .content { border: 1px solid #b0c4de; padding: 10px; background: #f0f8ff; }
    #editor { width: 100%; height: 400px; border: none; padding: 10px; font-family: monospace; }
    #preview { white-space: pre-wrap; }
    .commit-section { margin-top: 20px; padding: 10px; border: 1px solid #b0c4de; background: #fff; text-align: right; }
    .commit-section input[type="submit"] { margin-left: 15px; }
    .commit-section .save-backup { margin-left: 15px; }
    .back-btn, .logout-btn { padding: 10px 20px; background: linear-gradient(#556B2F, #2F4F4F); color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 15px; text-decoration: none; display: inline-block; }
    .back-btn:hover, .logout-btn:hover { background: linear-gradient(#6B8E23, #4A6868); }
    .CodeMirror { height: 400px; border: 1px solid #b0c4de; }
    #dirTreeModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
    #dirTreeContent { background: #fff; margin: 15% auto; padding: 20px; width: 50%; max-height: 70%; overflow-y: auto; border-radius: 5px; }
</style></head><body>';

// File manager logic
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        echo '<h1>' . $t['title'] . '</h1>';
        
        // Clickable current directory with logout link
        $pathParts = explode(DIRECTORY_SEPARATOR, $currentDir);
        $pathLinks = [];
        $accumulatedPath = '';
        foreach ($pathParts as $part) {
            if (!empty($part)) {
                $accumulatedPath .= DIRECTORY_SEPARATOR . $part;
                if (in_array($part, ['var', 'www'])) {
                    $pathLinks[] = htmlspecialchars($part);
                } else {
                    $pathLinks[] = '<a href="?action=list&dir=' . urlencode($accumulatedPath) . '" style="text-decoration: underline;">' . htmlspecialchars($part) . '</a>';
                }
            }
        }
        echo '<h3>' . $t['current_dir'] . implode(' / ', $pathLinks) . ' <a href="?action=logout" class="logout-btn">' . $t['logout'] . '</a></h3>';
        
        // Create Folder form with info icon
        echo '<h3>' . $t['create_folder'] . ' <span class="info-icon" onclick="showFolderInfo()">❓</span></h3>';
        echo '<form method="post" action="?action=create_folder&dir=' . urlencode($currentDir) . '">';
        echo '<input type="text" name="folder_name" placeholder="' . $t['folder_name'] . '">';
        echo '<input type="submit" value="' . $t['create'] . '">';
        echo '</form>';

        // Batch operation form with table
        echo '<form id="batchForm" method="post" action="?action=batch&dir=' . urlencode($currentDir) . '">';
        echo '<div id="fileTable">';
        echo '<table>';
        echo '<tr><th>' . $t['select'] . '</th><th>' . $t['name'] . '</th><th>' . $t['permissions'] . '</th><th>' . $t['owner'] . '</th><th>' . $t['size'] . '</th><th>' . $t['created'] . '</th><th>' . $t['modified'] . '</th><th>' . $t['actions'] . '</th></tr>';

        // Parent directory as first row
        if ($currentDir != realpath($rootDir)) {
            $parentDir = dirname($currentDir);
            echo '<tr>';
            echo '<td></td>';
            echo '<td><strong><a href="?action=list&dir=' . urlencode($parentDir) . '">' . $t['parent_dir'] . '</a></strong></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '</tr>';
        }

        $entries = array_diff(scandir($currentDir), array('.', '..'));
        $dirs = array_filter($entries, function($e) use ($currentDir) { return is_dir($currentDir . '/' . $e); });
        $files = array_filter($entries, function($e) use ($currentDir) { return is_file($currentDir . '/' . $e); });
        natsort($dirs);
        natsort($files);

        // Display directories
        foreach ($dirs as $file) {
            $fullpath = $currentDir . '/' . $file;
            $dirSize = get_dir_size($fullpath);
            $isLocked = isLocked($fullpath);
            echo '<tr>';
            echo '<td></td>';
            echo '<td><strong><a href="?action=list&dir=' . urlencode($fullpath) . '">' . ($isLocked ? '🔒 ' : '') . htmlspecialchars($file) . '</a></strong></td>';
            echo '<td>' . permissionsToString($fullpath) . '</td>';
            echo '<td>' . get_owner($fullpath) . '</td>';
            echo '<td>' . format_size($dirSize) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filectime($fullpath)) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filemtime($fullpath)) . '</td>';
            echo '<td>';
            echo $isLocked ?
                '<a href="#" onclick="unlockFile(\'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\', true)">' . $t['unlock'] . '</a> | ' :
                '<a href="#" onclick="lockFile(\'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\', true)">' . $t['lock'] . '</a> | ';
            if (!$isLocked) {
                echo '<a href="#" onclick="showRename(event, this, \'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\', \'' . htmlspecialchars($file) . '\')">' . $t['rename'] . '</a> | ';
                echo '<a href="#" onclick="if(confirm(\'' . $t['confirm_folder_delete'] . '\')) deleteFolder(\'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\')">' . $t['delete'] . '</a>';
            }
            echo '</td>';
            echo '</tr>';
        }

        // Display files
        foreach ($files as $file) {
            $fullpath = $currentDir . '/' . $file;
            $fileSize = filesize($fullpath);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $isLocked = isLocked($fullpath);
            echo '<tr>';
            echo '<td><input type="checkbox" name="files[]" value="' . urlencode($fullpath) . '" ' . ($isLocked ? 'disabled' : '') . '></td>';
            echo '<td><a href="?action=download&dir=' . urlencode($currentDir) . '&file=' . urlencode($file) . '">' . ($isLocked ? '🔒 ' : '') . htmlspecialchars($file) . '</a></td>';
            echo '<td>' . permissionsToString($fullpath) . '</td>';
            echo '<td>' . get_owner($fullpath) . '</td>';
            echo '<td>' . format_size($fileSize) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filectime($fullpath)) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filemtime($fullpath)) . '</td>';
            echo '<td>';
            echo $isLocked ?
                '<a href="#" onclick="unlockFile(\'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\', false)">' . $t['unlock'] . '</a> | ' :
                '<a href="#" onclick="lockFile(\'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\', false)">' . $t['lock'] . '</a> | ';
            if (!$isLocked) {
                echo '<a href="#" onclick="showDeleteConfirm(event, this, \'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\')">' . $t['delete'] . '</a> | ';
                echo '<a href="#" onclick="showRename(event, this, \'' . urlencode($currentDir) . '\', \'' . urlencode($file) . '\', \'' . htmlspecialchars($file) . '\')">' . $t['rename'] . '</a>';
                if ($fileSize < 500 * 1024) {
                    echo ' | <a href="?action=edit&dir=' . urlencode($currentDir) . '&file=' . urlencode($file) . '">' . $t['edit'] . '</a>';
                }
            }
            if (in_array($ext, ['html', 'htm', 'php'])) {
                $visitUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullpath);
                $style = (stripos($file, 'index') !== false) ? 'color: red; font-weight: bold;' : '';
                echo ' | <a href="' . htmlspecialchars($visitUrl) . '" target="_blank" style="' . $style . '">' . $t['visit'] . '</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '<input type="submit" id="batchDeleteBtn" class="batch-btn" value="' . $t['batch_delete'] . '">';
        echo '<input type="submit" name="moveBtn" class="batch-btn" value="' . $t['batch_move'] . '">';
        echo '<input type="submit" name="copyBtn" class="batch-btn" value="' . $t['batch_copy'] . '">';
        echo '<input type="hidden" name="operation" id="operationInput">';
        echo '<input type="hidden" name="target_dir" id="targetDir">';
        echo '</form>';

        // Upload files form with info icon
        echo '<p>' . $t['max_size'] . ini_get('upload_max_filesize') . ' | ' . $t['max_files'] . ini_get('max_file_uploads') . ' <span class="info-icon" onclick="showUploadInfo()">❓</span></p>';
        echo '<form id="uploadFilesForm" action="?action=upload&dir=' . urlencode($currentDir) . '" method="post" enctype="multipart/form-data">';
        echo '<input type="file" name="files[]" multiple id="fileInput">';
        echo '<input type="submit" value="' . $t['upload'] . '">';
        echo '<progress id="uploadFilesProgress" value="0" max="100"></progress>';
        echo '<span id="uploadFilesSpeed"></span>';
        echo '</form>';

        // Directory tree modal
        echo '<div id="dirTreeModal">';
        echo '<div id="dirTreeContent">';
        echo '<h3>' . $t['select_target_dir'] . '</h3>';
        echo '<div id="dirTree">' . getDirectoryTree($rootDir) . '</div>';
        echo '<button onclick="closeDirTree()">关闭</button>';
        echo '</div>';
        echo '</div>';

        // JavaScript
        echo '<script>
        function handleUpload(formId, progressId, speedId) {
            document.getElementById(formId).addEventListener("submit", function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                var xhr = new XMLHttpRequest();
                xhr.open("POST", this.action, true);
                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        var percentComplete = (event.loaded / event.total) * 100;
                        document.getElementById(progressId).value = percentComplete;
                        var speed = event.loaded / (event.timeStamp / 1000);
                        speed = (speed / (1024 * 1024)).toFixed(2);
                        document.getElementById(speedId).textContent = speed + " MB/s";
                    }
                };
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alert(response.message);
                                refreshFileList("' . urlencode($currentDir) . '");
                            } else {
                                alert("上传失败: " + response.message);
                            }
                        } catch (e) {
                            alert("上传失败: 服务器响应错误 - " + xhr.responseText);
                        }
                    } else {
                        alert("上传请求失败，状态码: " + xhr.status);
                    }
                };
                xhr.onerror = function() {
                    alert("上传请求失败");
                };
                xhr.send(formData);
            });
        }
        handleUpload("uploadFilesForm", "uploadFilesProgress", "uploadFilesSpeed");

        function showRename(event, link, dir, file, oldName) {
            event.preventDefault();
            var td = link.parentElement;
            td.innerHTML = \'<input type="text" id="renameInput" value="\' + oldName + \'"> <button onclick="saveRename(this, \\\'\' + dir + \'\\\', \\\'\' + file + \'\\\')">\' + \'' . $t['save'] . '\' + \'</button> <button class="cancel-btn" onclick="cancelRename(this, \\\'\' + dir + \'\\\')">\' + \'' . $t['cancel'] . '\' + \'</button>\';
            document.getElementById("renameInput").focus();
        }

        function saveRename(button, dir, file) {
            var newName = document.getElementById("renameInput").value;
            if (newName && newName !== decodeURIComponent(file)) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "?action=rename&dir=" + dir, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                refreshFileList(dir);
                            } else {
                                alert("重命名失败: " + response.message);
                            }
                        } catch (e) {
                            alert("重命名失败: 服务器响应错误 - " + xhr.responseText);
                        }
                    } else {
                        alert("重命名请求失败，状态码: " + xhr.status);
                    }
                };
                xhr.onerror = function() {
                    alert("重命名请求失败");
                };
                xhr.send("file=" + file + "&new_name=" + encodeURIComponent(newName));
            } else {
                cancelRename(button, dir);
            }
        }

        function cancelRename(button, dir) {
            refreshFileList(dir);
        }

        function showDeleteConfirm(event, link, dir, file) {
            event.preventDefault();
            var td = link.parentElement;
            td.innerHTML = \'' . $t['delete'] . '? <button class="delete-btn" onclick="deleteFile(\\\'' . addslashes($t['yes']) . '\\\', \\\'\' + dir + \'\\\', \\\'\' + file + \'\\\')">\' + \'' . $t['yes'] . '\' + \'</button> | <button class="cancel-btn" onclick="deleteFile(\\\'' . addslashes($t['no']) . '\\\', \\\'\' + dir + \'\\\', \\\'\' + file + \'\\\')">\' + \'' . $t['no'] . '\' + \'</button>\';
        }

        function deleteFile(action, dir, file) {
            if (action === "' . addslashes($t['yes']) . '") {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "?action=delete&dir=" + dir + "&file=" + file, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                refreshFileList(dir);
                            } else {
                                alert("删除失败: " + response.message);
                            }
                        } catch (e) {
                            alert("删除失败: 服务器响应错误 - " + xhr.responseText);
                        }
                    } else {
                        alert("删除请求失败，状态码: " + xhr.status);
                    }
                };
                xhr.onerror = function() {
                    alert("删除请求失败");
                };
                xhr.send();
            } else {
                refreshFileList(dir);
            }
        }

        function deleteFolder(dir, folder) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "?action=delete_folder&dir=" + dir + "&folder=" + folder, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            refreshFileList(dir);
                        } else {
                            alert("文件夹删除失败: " + response.message);
                        }
                    } catch (e) {
                        alert("文件夹删除失败: 服务器响应错误 - " + xhr.responseText);
                    }
                } else {
                    alert("文件夹删除请求失败，状态码: " + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert("文件夹删除请求失败");
            };
            xhr.send();
        }

        function lockFile(dir, file, isDir) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "?action=lock&dir=" + dir + "&file=" + file + "&is_dir=" + isDir, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    refreshFileList(dir);
                } else {
                    alert("加锁失败: " + xhr.responseText);
                }
            };
            xhr.onerror = function() {
                alert("加锁请求失败");
            };
            xhr.send();
        }

        function unlockFile(dir, file, isDir) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "?action=unlock&dir=" + dir + "&file=" + file + "&is_dir=" + isDir, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    refreshFileList(dir);
                } else {
                    alert("解锁失败: " + xhr.responseText);
                }
            };
            xhr.onerror = function() {
                alert("解锁请求失败");
            };
            xhr.send();
        }

        document.getElementById("batchForm").addEventListener("submit", function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var operation = formData.get("operation");
            var checked = this.querySelectorAll("input[name=\'files[]\']:checked");

            if (checked.length === 0 && event.submitter.id === "batchDeleteBtn") {
                alert("' . $t['no_files_selected'] . '");
                return;
            }

            if (event.submitter.id === "batchDeleteBtn") {
                if (!confirm("' . addslashes($t['confirm_batch_delete']) . '")) return;
                formData.append("operation", "delete");
            } else if (!operation) {
                return; // 等待选择目录
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "?action=batch&dir=' . urlencode($currentDir) . '", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            refreshFileList("' . urlencode($currentDir) . '");
                            closeDirTree();
                        } else {
                            alert("批量操作失败: " + response.message);
                        }
                    } catch (e) {
                        alert("批量操作失败: 服务器响应错误 - " + xhr.responseText);
                    }
                } else {
                    alert("批量操作请求失败，状态码: " + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert("批量操作请求失败");
            };
            xhr.send(formData);
        });

        function refreshFileList(dir) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "?action=refresh&dir=" + dir, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById("fileTable").innerHTML = xhr.responseText;
                } else {
                    alert("刷新文件列表失败: " + xhr.responseText);
                }
            };
            xhr.onerror = function() {
                alert("刷新请求失败");
            };
            xhr.send();
        }

        let currentOperation = "";
        function showDirTree(event, operation) {
            event.preventDefault();
            currentOperation = operation;
            var checked = document.querySelectorAll("input[name=\'files[]\']:checked");
            if (checked.length === 0) {
                alert("' . $t['no_files_selected'] . '");
                return;
            }
            document.getElementById("dirTreeModal").style.display = "block";
        }

        document.querySelector("input[name=\'moveBtn\']").addEventListener("click", function(event) {
            showDirTree(event, "move");
        });

        document.querySelector("input[name=\'copyBtn\']").addEventListener("click", function(event) {
            showDirTree(event, "copy");
        });

        function selectDir(dir) {
            document.getElementById("targetDir").value = dir;
            document.getElementById("operationInput").value = currentOperation === "move" ? "move" : "copy";
            document.getElementById("batchForm").dispatchEvent(new Event("submit"));
        }

        function closeDirTree() {
            document.getElementById("dirTreeModal").style.display = "none";
        }

        function showUploadInfo() {
            alert("用SSH登录服务器然后修改php.ini，位置在哪里可以百度下\\n" +
                  "upload_max_filesize=8m ;望文生意,即允许上传文件大小的最大值。默认为2M\\n" +
                  "post_max_size = 8m ;指通过表单POST给PHP的所能接收的最大值,包括表单里的所有值。默认为8M\\n" +
                  "max_file_uploads = 5 ;表示每次上传最多可以上传5个 默认为20个");
        }

        function showFolderInfo() {
            alert("文件操作没有权限???\\n" +
                  "sudo chown -R www-data:www-data /var/www/html\\n" +
                  "如果网页上传文件 需要把目录增加到www用户可用");
        }

        function updateServerTime() {
            var now = new Date();
            var offset = 8 * 60 * 60 * 1000;
            var localTime = now.getTime() + offset;
            var date = new Date(localTime);
            var timeStr = date.getUTCFullYear() + "-" + 
                          ("0" + (date.getUTCMonth() + 1)).slice(-2) + "-" + 
                          ("0" + date.getUTCDate()).slice(-2) + " " + 
                          ("0" + date.getUTCHours()).slice(-2) + ":" + 
                          ("0" + date.getUTCMinutes()).slice(-2) + ":" + 
                          ("0" + date.getUTCSeconds()).slice(-2);
            document.getElementById("serverTime").innerText = timeStr;
        }
        setInterval(updateServerTime, 1000);
        </script>';
        break;

    case 'upload':
        ob_end_clean();
        header('Content-Type: application/json');
        $targetDir = $_GET['dir'] ?? $rootDir;
        if (isset($_FILES['files'])) {
            $files = $_FILES['files'];
            $success = true;
            $message = '';
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] == UPLOAD_ERR_OK) {
                    if (!is_writable($targetDir)) {
                        $success = false;
                        $message = '目标目录不可写';
                        break;
                    }
                    $uploadfile = $targetDir . '/' . basename($files['name'][$i]);
                    if (!move_uploaded_file($files['tmp_name'][$i], $uploadfile)) {
                        $success = false;
                        $message .= "移动文件失败: " . $files['name'][$i] . "; ";
                    }
                } else {
                    $success = false;
                    $messages = [
                        UPLOAD_ERR_INI_SIZE => '文件超出 upload_max_filesize 限制',
                        UPLOAD_ERR_FORM_SIZE => '文件超出 MAX_FILE_SIZE 限制',
                        UPLOAD_ERR_PARTIAL => '文件仅部分上传',
                        UPLOAD_ERR_NO_FILE => '未上传文件',
                        UPLOAD_ERR_NO_TMP_DIR => '缺少临时文件夹',
                        UPLOAD_ERR_CANT_WRITE => '无法写入磁盘',
                        UPLOAD_ERR_EXTENSION => 'PHP扩展阻止了上传'
                    ];
                    $message .= $messages[$files['error'][$i]] ?? '未知错误' . " 于 " . $files['name'][$i] . "; ";
                }
            }
            if ($success) {
                echo json_encode(['success' => true, 'message' => '文件上传成功']);
            } else {
                echo json_encode(['success' => false, 'message' => trim($message, '; ')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '未上传任何文件']);
        }
        exit;

    case 'delete':
        ob_end_clean();
        header('Content-Type: application/json');
        $file = $_GET['file'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        if (!$file) {
            echo json_encode(['success' => false, 'message' => '未指定要删除的文件']);
            exit;
        }
        $fullpath = $dir . '/' . urldecode($file);
        if (file_exists($fullpath) && is_file($fullpath) && is_writable($fullpath)) {
            unlink($fullpath);
            echo json_encode(['success' => true, 'message' => '文件删除成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '无法删除文件，可能已被锁定或不存在']);
        }
        exit;

    case 'delete_folder':
        ob_end_clean();
        header('Content-Type: application/json');
        $folder = $_GET['folder'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        if (!$folder) {
            echo json_encode(['success' => false, 'message' => '未指定要删除的文件夹']);
            exit;
        }
        $fullpath = $dir . '/' . $folder;
        if (file_exists($fullpath) && is_dir($fullpath) && is_writable($fullpath)) {
            deleteDirectory($fullpath);
            echo json_encode(['success' => true, 'message' => '文件夹删除成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '无法删除文件夹，可能已被锁定或不存在']);
        }
        exit;

    case 'download':
        $file = $_GET['file'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        $fullpath = $dir . '/' . $file;
        if (file_exists($fullpath) && is_file($fullpath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fullpath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fullpath));
            readfile($fullpath);
            exit;
        } else {
            echo "文件未找到";
        }
        break;

    case 'edit':
        $file = $_GET['file'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        $fullpath = $dir . '/' . $file;
        if (file_exists($fullpath) && is_file($fullpath) && filesize($fullpath) < 500 * 1024 && is_writable($fullpath)) {
            if (isset($_POST['save']) || isset($_POST['save_backup'])) {
                $content = $_POST['content'] ?? '';
                if (isset($_POST['save_backup'])) {
                    $backupPath = $fullpath . '.bak';
                    $counter = 0;
                    while (file_exists($backupPath . $counter)) {
                        $counter++;
                    }
                    copy($fullpath, $backupPath . $counter);
                }
                file_put_contents($fullpath, $content);
                header('Location: ?action=list&dir=' . urlencode($dir));
                exit;
            }
            $content = file_get_contents($fullpath);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mode = 'text/plain';
            if (in_array($ext, ['html', 'htm'])) $mode = 'htmlmixed';
            elseif ($ext == 'php') $mode = 'application/x-httpd-php';
            elseif ($ext == 'js') $mode = 'javascript';
            elseif ($ext == 'css') $mode = 'css';

            echo '<div class="github-editor">';
            echo '<h1>' . $t['edit_title'] . htmlspecialchars($file) . '</h1>';
            echo '<div class="tabs">';
            echo '<span class="tab active" onclick="showTab(\'edit\')">' . $t['edit_tab'] . '</span>';
            echo '<span class="tab" onclick="showTab(\'preview\')">' . $t['preview_tab'] . '</span>';
            echo '</div>';
            echo '<form method="post" action="">';
            echo '<div class="content" id="edit" style="display: block;">';
            echo '<textarea id="editor" name="content">' . htmlspecialchars($content) . '</textarea>';
            echo '<label for="bgColor">' . $t['change_bg_color'] . ':</label>';
            echo '<input type="color" id="bgColor" onchange="changeBackgroundColor(this.value)">';
            echo '</div>';
            echo '<div class="content" id="preview" style="display: none;">' . htmlspecialchars($content) . '</div>';
            echo '<div class="commit-section">';
            echo '<input type="submit" name="save" value="' . $t['save'] . '">';
            echo '<input type="submit" name="save_backup" value="' . $t['save_backup'] . '" class="save-backup">';
            echo '<a href="?action=list&dir=' . urlencode($dir) . '" class="back-btn">' . $t['back_to_list'] . '</a>';
            echo '</div>';
            echo '<p style="color: #4682b4; font-size: 12px;">' . $t['compatibility_note'] . '</p>';
            echo '</form>';
            echo '</div>';

            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.js"></script>';
            echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/codemirror.min.css">';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/htmlmixed/htmlmixed.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/xml/xml.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/javascript/javascript.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/css/css.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/mode/php/php.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldcode.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldgutter.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/brace-fold.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/xml-fold.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/indent-fold.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/comment-fold.min.js"></script>';
            echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/fold/foldgutter.min.css">';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/edit/closebrackets.min.js"></script>';
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.17/addon/selection/active-line.min.js"></script>';

            echo '<script>
                var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
                    lineNumbers: true,
                    mode: "' . $mode . '",
                    theme: "default",
                    foldGutter: true,
                    gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                    autoCloseBrackets: true,
                    styleActiveLine: true
                });

                function showTab(tab) {
                    document.getElementById("edit").style.display = tab === "edit" ? "block" : "none";
                    document.getElementById("preview").style.display = tab === "preview" ? "block" : "none";
                    document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
                    event.target.classList.add("active");
                    if (tab === "preview") {
                        document.getElementById("preview").innerText = editor.getValue();
                    }
                }

                function changeBackgroundColor(color) {
                    editor.getWrapperElement().style.backgroundColor = color;
                }
            </script>';
        } else {
            echo "文件无法编辑";
        }
        break;

    case 'rename':
        ob_end_clean();
        header('Content-Type: application/json');
        $file = $_POST['file'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        $newName = $_POST['new_name'] ?? '';
        if (!$file || !$newName) {
            echo json_encode(['success' => false, 'message' => '未指定文件或新名称']);
            exit;
        }
        $fullpath = $dir . '/' . urldecode($file);
        $newPath = $dir . '/' . $newName;
        if (file_exists($fullpath) && is_writable($fullpath) && $newName && !file_exists($newPath) && !strpos($newName, '/') && !strpos($newName, '\\')) {
            rename($fullpath, $newPath);
            echo json_encode(['success' => true, 'message' => '重命名成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '无效名称、文件被锁定或目标已存在']);
        }
        exit;

    case 'lock':
        ob_end_clean();
        header('Content-Type: application/json');
        $file = $_GET['file'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        $isDir = filter_var($_GET['is_dir'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $fullpath = $dir . '/' . urldecode($file);
        if (file_exists($fullpath)) {
            chmod($fullpath, $isDir ? 0555 : 0444);
            echo json_encode(['success' => true, 'message' => '加锁成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '无法加锁文件/文件夹']);
        }
        exit;

    case 'unlock':
        ob_end_clean();
        header('Content-Type: application/json');
        $file = $_GET['file'] ?? '';
        $dir = $_GET['dir'] ?? $rootDir;
        $isDir = filter_var($_GET['is_dir'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $fullpath = $dir . '/' . urldecode($file);
        if (file_exists($fullpath)) {
            chmod($fullpath, $isDir ? 0755 : 0644);
            echo json_encode(['success' => true, 'message' => '解锁成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '无法解锁文件/文件夹']);
        }
        exit;

    case 'batch':
        ob_end_clean();
        header('Content-Type: application/json');
        if (!isset($_POST['operation'])) {
            echo json_encode(['success' => false, 'message' => '未指定操作类型']);
            exit;
        }
        if (!isset($_POST['files']) || !is_array($_POST['files']) || empty($_POST['files'])) {
            echo json_encode(['success' => false, 'message' => $t['no_files_selected']]);
            exit;
        }
        $files = $_POST['files'];
        $dir = $_GET['dir'] ?? $rootDir;
        $operation = $_POST['operation'];

        switch ($operation) {
            case 'delete':
                foreach ($files as $fullpath) {
                    $fullpath = urldecode($fullpath);
                    if (file_exists($fullpath) && is_file($fullpath) && is_writable($fullpath)) {
                        unlink($fullpath);
                    }
                }
                echo json_encode(['success' => true, 'message' => '批量删除成功']);
                exit;
            case 'move':
                $targetDir = isset($_POST['target_dir']) ? urldecode($_POST['target_dir']) : '';
                if ($targetDir && is_dir($targetDir) && is_writable($targetDir)) {
                    foreach ($files as $fullpath) {
                        $fullpath = urldecode($fullpath);
                        $filename = basename($fullpath);
                        if (file_exists($fullpath) && is_file($fullpath) && is_writable($fullpath)) {
                            if (!rename($fullpath, $targetDir . '/' . $filename)) {
                                echo json_encode(['success' => false, 'message' => '移动失败: ' . htmlspecialchars($filename)]);
                                exit;
                            }
                        }
                    }
                    echo json_encode(['success' => true, 'message' => '移动成功']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => '目标目录无效或不可写: ' . htmlspecialchars($targetDir)]);
                    exit;
                }
                break;
            case 'copy':
                $targetDir = isset($_POST['target_dir']) ? urldecode($_POST['target_dir']) : '';
                if ($targetDir && is_dir($targetDir) && is_writable($targetDir)) {
                    foreach ($files as $fullpath) {
                        $fullpath = urldecode($fullpath);
                        $filename = basename($fullpath);
                        if (file_exists($fullpath) && is_file($fullpath)) {
                            if (!copy($fullpath, $targetDir . '/' . $filename)) {
                                echo json_encode(['success' => false, 'message' => '复制失败: ' . htmlspecialchars($filename)]);
                                exit;
                            }
                        }
                    }
                    echo json_encode(['success' => true, 'message' => '复制成功']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => '目标目录无效或不可写: ' . htmlspecialchars($targetDir)]);
                    exit;
                }
                break;
            default:
                echo json_encode(['success' => false, 'message' => '未知操作: ' . htmlspecialchars($operation)]);
                exit;
        }
        break;

    case 'create_folder':
        if (isset($_POST['folder_name'])) {
            $name = trim($_POST['folder_name']);
            $dir = $_GET['dir'] ?? $rootDir;
            if (strpos($name, '/') !== false || strpos($name, '\\') !== false) {
                echo "文件夹名称不能包含 / 或 \\";
            } elseif ($name != '' && !file_exists($dir . '/' . $name)) {
                mkdir($dir . '/' . $name, 0755);
                header('Location: ?action=list&dir=' . urlencode($dir));
                exit;
            } else {
                echo "文件夹名称不能为空或已存在";
            }
        }
        break;

    case 'refresh':
        ob_end_clean();
        $dir = $_GET['dir'] ?? $rootDir;
        echo '<table>';
        echo '<tr><th>' . $t['select'] . '</th><th>' . $t['name'] . '</th><th>' . $t['permissions'] . '</th><th>' . $t['owner'] . '</th><th>' . $t['size'] . '</th><th>' . $t['created'] . '</th><th>' . $t['modified'] . '</th><th>' . $t['actions'] . '</th></tr>';

        if ($dir != realpath($rootDir)) {
            $parentDir = dirname($dir);
            echo '<tr>';
            echo '<td></td>';
            echo '<td><strong><a href="?action=list&dir=' . urlencode($parentDir) . '">' . $t['parent_dir'] . '</a></strong></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '</tr>';
        }

        $entries = array_diff(scandir($dir), array('.', '..'));
        $dirs = array_filter($entries, function($e) use ($dir) { return is_dir($dir . '/' . $e); });
        $files = array_filter($entries, function($e) use ($dir) { return is_file($dir . '/' . $e); });
        natsort($dirs);
        natsort($files);

        foreach ($dirs as $file) {
            $fullpath = $dir . '/' . $file;
            $dirSize = get_dir_size($fullpath);
            $isLocked = isLocked($fullpath);
            echo '<tr>';
            echo '<td></td>';
            echo '<td><strong><a href="?action=list&dir=' . urlencode($fullpath) . '">' . ($isLocked ? '🔒 ' : '') . htmlspecialchars($file) . '</a></strong></td>';
            echo '<td>' . permissionsToString($fullpath) . '</td>';
            echo '<td>' . get_owner($fullpath) . '</td>';
            echo '<td>' . format_size($dirSize) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filectime($fullpath)) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', filemtime($fullpath)) . '</td>';
            echo '<td>';
            echo $isLocked ?
                '<a href="#" onclick="unlockFile(\'' . urlencode($dir) . '\', \'' . urlencode($file) . '\', true)">' . $t['unlock'] . '</a> | ' :
                '<a href="#" onclick="lockFile(\'' . urlencode($dir) . '\', \'' . urlencode($file) . '\', true)">' . $t['lock'] . '</a> | ';
            if (!$isLocked) {
                echo '<a href="#" onclick="showRename(event, this, \'' . urlencode($dir) . '\', \'' . urlencode($file) . '\', \'' . htmlspecialchars($file) . '\')">' . $t['rename'] . '</a> | ';
                echo '<a href="#" onclick="if(confirm(\'' . $t['confirm_folder_delete'] . '\')) deleteFolder(\'' . urlencode($dir) . '\', \'' . urlencode($file) . '\')">' . $t['delete'] . '</a>';
            }
            echo '</td>';
            echo '</tr>';
        }

        foreach ($files as $file) {
            $fullpath = $dir . '/' . $file;
            $fileSize = filesize($fullpath);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $isLocked = isLocked($fullpath);
            echo '<tr>';
            echo '<td><input type="checkbox" name="files[]" value="' . urlencode($fullpath) . '" ' . ($isLocked ? 'disabled' : '') . '></td>';
            echo '<td><a href="?action=download&dir=' . urlencode($dir) . '&file=' . urlencode($file) . '">' . ($isLocked ? '🔒 ' : '') . htmlspecialchars($file) . '</a></td>';
            echo '<td>' . permissio
