<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php if (isset($_GET['note'])) print htmlspecialchars($_GET['note']); else print '密码保护'; ?></title>
	<style>
	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
	}

	body {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		min-height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px;
	}

	.login-container {
		background: rgba(255, 255, 255, 0.95);
		backdrop-filter: blur(10px);
		border-radius: 20px;
		box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
		padding: 40px;
		width: 100%;
		max-width: 400px;
		text-align: center;
		border: 1px solid rgba(255, 255, 255, 0.2);
	}

	.lock-icon {
		font-size: 48px;
		margin-bottom: 20px;
		color: #667eea;
	}

	.title {
		font-size: 24px;
		font-weight: 600;
		color: #333;
		margin-bottom: 10px;
	}

	.subtitle {
		font-size: 14px;
		color: #666;
		margin-bottom: 30px;
		line-height: 1.5;
	}

	.form-group {
		margin-bottom: 20px;
		text-align: left;
	}

	.form-label {
		display: block;
		font-size: 14px;
		font-weight: 500;
		color: #333;
		margin-bottom: 8px;
	}

	.form-input {
		width: 100%;
		padding: 14px 16px;
		border: 2px solid #e1e5e9;
		border-radius: 12px;
		font-size: 16px;
		transition: all 0.3s ease;
		background: #fff;
	}

	.form-input:focus {
		outline: none;
		border-color: #667eea;
		box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
		transform: translateY(-2px);
	}

	.submit-btn {
		width: 100%;
		padding: 14px;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: white;
		border: none;
		border-radius: 12px;
		font-size: 16px;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
		margin-bottom: 20px;
	}

	.submit-btn:hover {
		transform: translateY(-2px);
		box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
	}

	.submit-btn:active {
		transform: translateY(0);
	}

	.error-message {
		background: #fee;
		color: #c53030;
		padding: 12px 16px;
		border-radius: 8px;
		margin-bottom: 20px;
		border-left: 4px solid #fc8181;
		font-size: 14px;
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.links {
		border-top: 1px solid #e1e5e9;
		padding-top: 20px;
		margin-top: 20px;
	}

	.link-item {
		margin: 8px 0;
	}

	.link-item a {
		color: #667eea;
		text-decoration: none;
		font-size: 14px;
		transition: color 0.3s ease;
	}

	.link-item a:hover {
		color: #764ba2;
		text-decoration: underline;
	}

	.note-info {
		background: #f7fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 12px;
		margin-bottom: 20px;
		font-size: 13px;
		color: #4a5568;
	}

	/* 响应式设计 */
	@media (max-width: 480px) {
		.login-container {
			padding: 30px 20px;
			margin: 10px;
			border-radius: 16px;
		}

		.title {
			font-size: 20px;
		}

		.lock-icon {
			font-size: 40px;
		}
	}

	/* 加载动画 */
	@keyframes fadeInUp {
		from {
			opacity: 0;
			transform: translateY(30px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.login-container {
		animation: fadeInUp 0.6s ease;
	}

	/* 输入框图标 */
	.input-wrapper {
		position: relative;
	}

	.input-icon {
		position: absolute;
		left: 16px;
		top: 50%;
		transform: translateY(-50%);
		color: #9ca3af;
		font-size: 16px;
	}

	.form-input.with-icon {
		padding-left: 45px;
	}
	</style>
</head>
<body onload="document.forms[0].password.focus();">
	<div class="login-container">
		<div class="lock-icon">🔒</div>
		<h1 class="title">受保护的笔记</h1>
		
		<form method="POST">
			<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') { ?>
				<div class="error-message">
					<span>⚠️</span>
					<span>密码无效，请重新输入</span>
				</div>
			<?php } ?>

			<?php if (isset($_GET['note'])) { ?>
				<div class="note-info">
					📝 笔记名称: <strong><?php echo htmlspecialchars($_GET['note']); ?></strong>
				</div>
			<?php } ?>

			<div class="form-group">
				<label class="form-label" for="password">密码</label>
				<div class="input-wrapper">
					<span class="input-icon">🔑</span>
					<input 
						type="password" 
						name="password" 
						id="password"
						class="form-input with-icon" 
						placeholder="请输入密码"
						autofocus 
						required
					>
				</div>
			</div>

			<button type="submit" class="submit-btn">
				🚀 访问笔记
			</button>

			<div class="links">
				<div class="link-item">
					<a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>">✨ 新建笔记</a>
				</div>
				
				<?php if ($allowReadOnlyView == "1") { ?>
					<div class="link-item">
						<a href="<?php echo strtok($_SERVER["REQUEST_URI"],'?') . "?view"; ?>">👁️ 以只读方式查看</a>
					</div>
				<?php } ?>
			</div>
		</form>
	</div>

	<script>
	// 添加一些交互效果
	document.addEventListener('DOMContentLoaded', function() {
		const input = document.getElementById('password');
		const form = document.querySelector('form');
		
		// Enter键提交
		input.addEventListener('keypress', function(e) {
			if (e.key === 'Enter') {
				form.submit();
			}
		});

		// 输入时的视觉反馈
		input.addEventListener('input', function() {
			if (this.value.length > 0) {
				this.style.borderColor = '#48bb78';
			} else {
				this.style.borderColor = '#e1e5e9';
			}
		});

		// 表单提交时的加载状态
		form.addEventListener('submit', function() {
			const btn = document.querySelector('.submit-btn');
			btn.innerHTML = '🔄 验证中...';
			btn.disabled = true;
		});
	});
	</script>
</body>
</html>
