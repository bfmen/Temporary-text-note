<link rel="stylesheet" type="text/css" href="modules/css/modal.min.css">
<div class="modal_password hidden" id="password_modal" style="hidden">
	<div class="modal-content">
		<span class="close-button_password">&times;</span>
		<div class="password-header">
			<h3 style="margin: 0 0 15px 0; color: #333;">🔒 密码保护</h3>
		</div>
		
		<div class="password-form" id="passwordForm">
			<div class="form-group">
				<label for="notepwd" style="display: block; margin-bottom: 5px; font-weight: bold;" id="passwordLabel">输入密码：</label>
				<div class="input-group">
					<span id="inputboxLocation"></span>
					<button class="submit primary-btn" id="submitpwd" onclick="handlePasswordAction();">设置密码</button>
				</div>
			</div>
			

		</div>
		
		<div class="message-area">
			<span id="pwdMessage" class="message"></span>
		</div>
		
		<div class="help-section">
			<a onclick='window.open("passwordHelp.html");' style="color: #007cba; text-decoration: none; font-size: 12px;">
				ℹ️ 了解密码保护机制
			</a>
		</div>
	</div>
</div>

<!-- 隐藏字段用于移除密码 -->
<input type="hidden" id="hdnRemovePassword" name="hdnRemovePassword" value="">

<style>
.password-header h3 {
	text-align: center;
	border-bottom: 2px solid #007cba;
	padding-bottom: 10px;
}

.form-group {
	margin-bottom: 15px;
}

.input-group {
	display: flex;
	gap: 10px;
	align-items: center;
}

.input-group input {
	flex: 1;
	min-width: 150px;
}

.checkbox-label {
	display: flex;
	align-items: center;
	cursor: pointer;
	font-size: 14px;
	gap: 8px;
}

.checkbox-label input[type="checkbox"] {
	margin: 0;
}

.primary-btn {
	background-color: #28a745 !important;
	border-color: #28a745 !important;
	transition: background-color 0.2s;
}

.primary-btn:hover {
	background-color: #218838 !important;
}

.danger-btn {
	background-color: #dc3545 !important;
	border-color: #dc3545 !important;
	transition: background-color 0.2s;
}

.danger-btn:hover {
	background-color: #c82333 !important;
}

.message {
	display: block;
	margin: 10px 0;
	padding: 8px;
	border-radius: 4px;
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

.message.info {
	background-color: #d1ecf1;
	color: #0c5460;
	border: 1px solid #bee5eb;
}

.help-section {
	text-align: center;
	margin-top: 15px;
	padding-top: 10px;
	border-top: 1px solid #eee;
}

.help-section a:hover {
	text-decoration: underline !important;
}

/* 响应式优化 */
@media screen and (max-width: 600px) {
	.input-group {
		flex-direction: column;
		gap: 8px;
	}
	
	.input-group input,
	.input-group button {
		width: 100%;
	}
}
</style>

<script>
// 确保在页面加载时就定义核心函数，避免ReferenceError
window.hasPassword = window.hasPassword || false;

// 辅助函数
function isEmpty(value) {
	return (value == null || value.length === 0);
}

function isValid(str) {
	return str && str.replace(/^\s+/g, '').length > 0;
}

// 兼容原有的请求函数
function passwordRequest_Add(requestToSend) {
	var notepwd = (document.getElementById("notepwd")) ? document.getElementById("notepwd").value : '';
	if (!isEmpty(notepwd)) {
		requestToSend = requestToSend + '&notepwd=' + encodeURIComponent(notepwd);
	}
	// 检查allowReadOnlyView元素是否存在，避免错误
	var allowReadOnlyView = document.getElementById("allowReadOnlyView");
	if (allowReadOnlyView && allowReadOnlyView.checked) {
		requestToSend = requestToSend + '&allowReadOnlyView=1';
	}
	return requestToSend;
}

function passwordRequest_Remove(requestToSend) {
	var removePassword = (document.getElementById("hdnRemovePassword")) ? document.getElementById("hdnRemovePassword").value : '';
	if (!isEmpty(removePassword)) {
		requestToSend = requestToSend + '&removePassword=' + encodeURIComponent(removePassword);
	}
	return requestToSend;
}

// 核心密码操作函数 - 立即定义
function handlePasswordAction() {
	var passwordInput = document.getElementById("notepwd");
	var password = passwordInput ? passwordInput.value : '';
	
	if (!password || password.length === 0) {
		showPasswordMessage("请输入密码", 'error');
		if (passwordInput) {
			passwordInput.focus();
		}
		return;
	}
	
	if (window.hasPassword) {
		// 移除密码
		removePasswordAction();
	} else {
		// 设置密码
		setPasswordAction();
	}
}

function setPasswordAction() {
	showPasswordMessage('正在设置密码保护...', 'info');
	var submitBtn = document.getElementById("submitpwd");
	if (submitBtn) {
		submitBtn.disabled = true;
		submitBtn.innerHTML = '设置中...';
	}
	
	// 设置成功回调
	window.passwordOperationSuccess = function() {
		window.hasPassword = true;
		updatePasswordUI();
		if (submitBtn) {
			submitBtn.disabled = false;
		}
		if (typeof toggleModal_Password === 'function') {
			toggleModal_Password();
		}
		showGlobalPasswordMessage('密码保护已设置成功！', 'success');
		// 清理回调
		window.passwordOperationSuccess = null;
		window.passwordOperationFailed = null;
	};
	
	// 设置失败回调
	window.passwordOperationFailed = function() {
		showPasswordMessage('密码设置失败，请重试', 'error');
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.innerHTML = '设置密码';
		}
		// 清空密码输入框并聚焦
		var passwordInput = document.getElementById("notepwd");
		if (passwordInput) {
			passwordInput.value = '';
			passwordInput.focus();
		}
		// 清理回调
		window.passwordOperationSuccess = null;
		window.passwordOperationFailed = null;
	};
	
	// 调用原有的上传函数
	if (typeof uploadContent === 'function') {
		uploadContent(true);
	}
	
	// 设置超时检查（如果3秒内没有收到响应，认为失败）
	setTimeout(function() {
		if (window.passwordOperationSuccess || window.passwordOperationFailed) {
			// 如果回调还在，说明操作可能还在进行中，再等1秒
			setTimeout(function() {
				if (window.passwordOperationSuccess) {
					window.passwordOperationSuccess();
				} else if (window.passwordOperationFailed) {
					window.passwordOperationFailed();
				}
			}, 1000);
		}
	}, 3000);
}

function removePasswordAction() {
	showPasswordMessage('正在移除密码保护...', 'info');
	var submitBtn = document.getElementById("submitpwd");
	if (submitBtn) {
		submitBtn.disabled = true;
		submitBtn.innerHTML = '移除中...';
	}
	
	// 设置成功回调
	window.passwordOperationSuccess = function() {
		window.hasPassword = false;
		updatePasswordUI();
		if (submitBtn) {
			submitBtn.disabled = false;
		}
		if (typeof toggleModal_Password === 'function') {
			toggleModal_Password();
		}
		showGlobalPasswordMessage('密码保护已成功移除！', 'success');
		// 清理回调
		window.passwordOperationSuccess = null;
		window.passwordOperationFailed = null;
	};
	
	// 设置失败回调
	window.passwordOperationFailed = function() {
		showPasswordMessage('密码错误，无法移除密码保护', 'error');
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.innerHTML = '移除密码';
		}
		// 清空密码输入框并聚焦
		var passwordInput = document.getElementById("notepwd");
		if (passwordInput) {
			passwordInput.value = '';
			passwordInput.focus();
		}
		// 清理回调
		window.passwordOperationSuccess = null;
		window.passwordOperationFailed = null;
	};
	
	// 设置移除密码标志
	var removeField = document.getElementById("hdnRemovePassword");
	if (removeField) {
		removeField.value = "1";
	}
	
	// 调用原有的上传函数
	if (typeof uploadContent === 'function') {
		uploadContent(true);
	}
	
	if (removeField) {
		removeField.value = "";
	}
	
	// 设置超时检查（如果3秒内没有收到响应，认为失败）
	setTimeout(function() {
		if (window.passwordOperationSuccess || window.passwordOperationFailed) {
			// 如果回调还在，说明操作可能还在进行中，再等1秒
			setTimeout(function() {
				if (window.passwordOperationSuccess) {
					window.passwordOperationSuccess();
				} else if (window.passwordOperationFailed) {
					window.passwordOperationFailed();
				}
			}, 1000);
		}
	}, 3000);
}

function updatePasswordUI() {
	var submitBtn = document.getElementById("submitpwd");
	var passwordLabel = document.getElementById("passwordLabel");
	var readOnlyGroup = document.getElementById("readOnlyGroup");
	
	if (window.hasPassword) {
		// 已有密码状态
		if (submitBtn) {
			submitBtn.innerHTML = '移除密码';
			submitBtn.className = 'submit danger-btn';
		}
		if (passwordLabel) {
			passwordLabel.innerHTML = '输入当前密码以移除保护：';
		}
		// 只在元素存在时才操作
		if (readOnlyGroup) {
			readOnlyGroup.style.display = 'none';
		}
	} else {
		// 无密码状态
		if (submitBtn) {
			submitBtn.innerHTML = '设置密码';
			submitBtn.className = 'submit primary-btn';
		}
		if (passwordLabel) {
			passwordLabel.innerHTML = '设置密码：';
		}
		// 只在元素存在时才操作
		if (readOnlyGroup) {
			readOnlyGroup.style.display = 'block';
		}
	}
}

function showPasswordMessage(message, type) {
	var messageEl = document.getElementById("pwdMessage");
	if (messageEl) {
		messageEl.innerHTML = message;
		messageEl.className = 'message ' + (type || '');
	}
}

function showGlobalPasswordMessage(message, type) {
	var messageDiv = document.createElement('div');
	messageDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 12px 20px; border-radius: 4px; font-family: sans-serif; font-size: 14px; z-index: 10000; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: opacity 0.3s ease;';
	
	if (type === 'success') {
		messageDiv.style.backgroundColor = '#d4edda';
		messageDiv.style.color = '#155724';
		messageDiv.style.border = '1px solid #c3e6cb';
	} else if (type === 'error') {
		messageDiv.style.backgroundColor = '#f8d7da';
		messageDiv.style.color = '#721c24';
		messageDiv.style.border = '1px solid #f5c6cb';
	}
	
	messageDiv.innerHTML = message;
	document.body.appendChild(messageDiv);
	
	setTimeout(function() {
		messageDiv.style.opacity = '0';
		setTimeout(function() {
			if (messageDiv.parentNode) {
				messageDiv.parentNode.removeChild(messageDiv);
			}
		}, 300);
	}, 3000);
}

// 兼容原有函数
function showRemovePassword() {
	window.hasPassword = true;
	updatePasswordUI();
}

function submitPassword() {
	handlePasswordAction();
}

function passwordRemove() {
	removePasswordAction();
}

// 初始化函数
function initPasswordModal() {
	var passwordInput = document.getElementById("notepwd");
	if (passwordInput) {
		passwordInput.focus();
		passwordInput.value = '';
		
		// 添加Enter键支持
		passwordInput.addEventListener("keyup", function(event) {
			event.preventDefault();
			if (event.keyCode === 13) {
				document.getElementById("submitpwd").click();
			}
		});
	}
	
	showPasswordMessage('', '');
	updatePasswordUI();
}

// 当模态框显示时初始化
document.addEventListener('DOMContentLoaded', function() {
	// 检测模态框显示
	var modal = document.getElementById('password_modal');
	if (modal) {
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
					if (modal.classList.contains('show-modal')) {
						setTimeout(initPasswordModal, 100);
					}
				}
			});
		});
		observer.observe(modal, { attributes: true });
	}
});
</script>
