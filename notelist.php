<?php
require_once 'modules/protect.php';
Protect\with('modules/protect_form.php', '45034489');  //设置列表查看密码

//  configuration settings, edit settings in config.php as appropriate
include('config.php');

// 处理删除请求
if (isset($_POST['delete_note']) && !empty($_POST['delete_note'])) {
    $note_to_delete = trim($_POST['delete_note']);
    
    // 安全检查：只允许字母数字和连字符
    if (!preg_match('/^[a-zA-Z0-9-]+$/', $note_to_delete)) {
        $delete_message = "无效的笔记名称";
        $delete_status = "error";
    } else {
        $note_path = $data_directory . $note_to_delete;
        
        // 安全检查：确保文件在notes目录内且存在
        if (file_exists($note_path) && is_file($note_path)) {
            $real_note_path = realpath($note_path);
            $real_data_dir = realpath($data_directory);
            
            if ($real_note_path && $real_data_dir && strpos($real_note_path, $real_data_dir) === 0) {
                if (unlink($note_path)) {
                    $delete_message = "笔记 '$note_to_delete' 已成功删除";
                    $delete_status = "success";
                } else {
                    $delete_message = "删除笔记 '$note_to_delete' 失败 - 权限不足";
                    $delete_status = "error";
                }
            } else {
                $delete_message = "无效的笔记路径";
                $delete_status = "error";
            }
        } else {
            $delete_message = "笔记 '$note_to_delete' 不存在";
            $delete_status = "error";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<?php

    // from https://stackoverflow.com/questions/16765158/date-it-is-not-safe-to-rely-on-the-systems-timezone-settings
    if (! ini_get('date.timezone')) {
        date_default_timezone_set('GMT');
    }

    // Directory to save user documents.
    // $data_directory = '_notes'; defined in config.php

    $serverTimezone = "";
    if (ini_get('date.timezone')) {
        $serverTimezone = ' TZ: ' . ini_get('date.timezone');
    }

    function ago($time)
    {
        //https://css-tricks.com/snippets/php/time-ago-function/
        $periods = array("秒", "分钟", "小时", "天", "周", "月", "年", "十年");
        $lengths = array("60","60","24","7","4.35","12","10");

        $now = time();

        $difference     = $now - $time;

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        return $difference . ' ' . $periods[$j] . '前 ';
    }

    function human_filesize($bytes, $decimals = 2)
    {
        // from user contribs on php filesize page
        $sz = 'bkMGTP';
        $szWords = array('字节','KB','MB','GB','TB','PB');
        $factor = floor((strlen($bytes) - 1) / 3);
        if (@$sz[$factor] == 'b') {
            $decimals = 0;
        }
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' .@$szWords[$factor];
    }

    ?>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>笔记列表 - 后台管理</title>
	<script src="js/notelist.min.js"></script>
	<link rel="shortcut icon" href="favicon.ico" />
	<style>
	body {
		margin-left:20px; margin-top:20px; font-family: sans-serif;}
	th {
		display: table-cell;
		vertical-align: inherit;
		font-weight: bold;
		text-align: left;
	}
	th, td {
		padding-right: 10px;
	}
	.delete-btn {
		background-color: #ff4444;
		color: white;
		border: none;
		padding: 5px 10px;
		border-radius: 3px;
		cursor: pointer;
		font-size: 12px;
	}
	.delete-btn:hover {
		background-color: #cc0000;
	}
	.message {
		padding: 10px;
		margin: 10px 0;
		border-radius: 3px;
	}
	.message.success {
		background-color: #d4edda;
		color: #155724;
		border: 1px solid #c3e6cb;
	}
	.message.error {
		background-color: #f8d7da;
		color: #721c24;
		border: 1px solid #f5c6cb;
	}
	.action-column {
		text-align: center;
		width: 80px;
	}
</style>
	<script>
	function confirmDelete(noteName) {
		if (confirm('确定要删除笔记 "' + noteName + '" 吗？此操作不可撤销。')) {
			// 创建表单并提交
			var form = document.createElement('form');
			form.method = 'POST';
			form.action = '';
			
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'delete_note';
			input.value = noteName;
			
			form.appendChild(input);
			document.body.appendChild(form);
			form.submit();
		}
	}
	</script>
</head>
<body>
<h2>笔记列表 - 后台管理</h2>

<?php if (isset($delete_message)): ?>
<div class="message <?php echo $delete_status; ?>">
	<?php echo htmlspecialchars($delete_message); ?>
</div>
<?php endif; ?>

<a href="<?php print $base_url; ?>">新建笔记</a><br><br>
<table id="filterTable">
	<tr>
	<th><input type="text" id="filterNotes" onkeyup="filterTable()" placeholder="按标题筛选..." style="background:transparent;border:none;"></th>
	</tr>
</table>
<table id="notelistTable">
	<th onclick="sortTable(0)">名称</th>
	<th onclick="sortTable(1)"><small>最后修改</small></th>
	<th><small>文件大小</small></th>
	<th class="action-column"><small>操作</small></th>
	</tr>
	<?php
    $files = array_diff(scandir($data_directory), array('.', '..','.htaccess'));
    $counter=0;
    $counterMax=500; //max number of notes to show
    foreach ($files as &$value) {
        if ($counter > $counterMax) {
            echo "<tr><td>达到列表最大数量 (". $counterMax . ")</td><td></td><td></td>";
            break; //have a max number of notes to show
        }
        echo "<tr><td style='padding-right: 20px;'><a href='".$value."' >".$value . "</a> </td>";
        echo "<td><small>" . ago(filemtime($data_directory.'/'.$value)) . "</small></td>";
        echo "<td><small>" . human_filesize(filesize($data_directory.'/'.$value)) . "</small></td>";
        echo "<td class='action-column'><button class='delete-btn' onclick='confirmDelete(\"".htmlspecialchars($value, ENT_QUOTES)."\")'>删除</button></td>" . PHP_EOL;
        $counter++;
    }
    ?>
</table>
</body>
</html>
