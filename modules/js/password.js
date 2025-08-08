// 全局变量跟踪密码状态
var hasPassword = false;

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

function metadataGet(requestToSend) {
  if (!isEmpty(notepwd)) {
    requestToSend = requestToSend + '&metadataGet=1';
  }
  return requestToSend;
}

function isEmpty(value) {
  return (value == null || value.length === 0);
}

// 统一的密码操作处理函数
function handlePasswordAction() {
  var passwordInput = document.getElementById("notepwd");
  var password = passwordInput ? passwordInput.value : '';
  
  if (isEmpty(password)) {
    showMessage("请输入密码", 'error');
    if (passwordInput) {
      passwordInput.focus();
    }
    return;
  }
  
  if (hasPassword) {
    // 移除密码
    removePassword();
  } else {
    // 设置密码
    setPassword();
  }
}

function setPassword() {
  var passwordInput = document.getElementById("notepwd");
  var password = passwordInput ? passwordInput.value : '';
  
  showMessage('正在设置密码保护...', 'info');
  document.getElementById("submitpwd").disabled = true;
  document.getElementById("submitpwd").innerHTML = '设置中...';
  
  uploadContent(true);
  
  setTimeout(function() {
    clearMessage();
    hasPassword = true;
    updatePasswordInterface();
    document.getElementById("submitpwd").disabled = false;
    toggleModal_Password();
    showGlobalMessage('密码保护已设置成功！', 'success');
  }, 500);
}

function removePassword() {
  showMessage('正在移除密码保护...', 'info');
  document.getElementById("submitpwd").disabled = true;
  document.getElementById("submitpwd").innerHTML = '移除中...';
  
  // 设置移除密码标志
  document.getElementById("hdnRemovePassword").value = "1";
  uploadContent(true);
  document.getElementById("hdnRemovePassword").value = "";
  
  setTimeout(function() {
    clearMessage();
    hasPassword = false;
    updatePasswordInterface();
    document.getElementById("submitpwd").disabled = false;
    toggleModal_Password();
    showGlobalMessage('密码保护已成功移除！', 'success');
  }, 500);
}

// 更新密码界面显示
function updatePasswordInterface() {
  var submitBtn = document.getElementById("submitpwd");
  var passwordLabel = document.getElementById("passwordLabel");
  var readOnlyGroup = document.getElementById("readOnlyGroup");
  
  if (hasPassword) {
    // 已有密码状态
    submitBtn.innerHTML = '移除密码';
    submitBtn.className = 'submit danger-btn';
    passwordLabel.innerHTML = '输入当前密码以移除保护：';
    if (readOnlyGroup) readOnlyGroup.style.display = 'none';
  } else {
    // 无密码状态
    submitBtn.innerHTML = '设置密码';
    submitBtn.className = 'submit primary-btn';
    passwordLabel.innerHTML = '设置密码：';
    if (readOnlyGroup) readOnlyGroup.style.display = 'block';
  }
}

function showMessage(message, type) {
  var messageEl = document.getElementById("pwdMessage");
  if (messageEl) {
    messageEl.innerHTML = message;
    messageEl.className = 'message ' + (type || '');
  }
}

function clearMessage() {
  var messageEl = document.getElementById("pwdMessage");
  if (messageEl) {
    messageEl.innerHTML = '';
    messageEl.className = 'message';
  }
}

function showGlobalMessage(message, type) {
  // 创建全局消息提示
  var messageDiv = document.createElement('div');
  messageDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 4px;
    font-family: sans-serif;
    font-size: 14px;
    z-index: 10000;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: opacity 0.3s ease;
  `;
  
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
  
  // 3秒后自动消失
  setTimeout(function() {
    messageDiv.style.opacity = '0';
    setTimeout(function() {
      if (messageDiv.parentNode) {
        messageDiv.parentNode.removeChild(messageDiv);
      }
    }, 300);
  }, 3000);
}

// 检测当前是否已有密码保护
function detectPasswordStatus() {
  // 通过检查是否有removePassword元素显示来判断
  // 这个函数会在showRemovePassword()被调用时更新状态
  return hasPassword;
}

// 兼容原有的showRemovePassword函数
function showRemovePassword() {
  hasPassword = true;
  updatePasswordInterface();
}

// 改进的密码强度检查
function checkPasswordStrength(password) {
  if (password.length < 4) {
    return { strength: 'weak', message: '密码太短，建议至少4个字符' };
  } else if (password.length < 8) {
    return { strength: 'medium', message: '密码强度一般' };
  } else {
    return { strength: 'strong', message: '密码强度良好' };
  }
}

// 实时密码强度提示（仅在设置密码时显示）
function setupPasswordStrengthIndicator() {
  var passwordInput = document.getElementById("notepwd");
  if (passwordInput) {
    passwordInput.addEventListener("input", function() {
      var password = this.value;
      if (password.length > 0 && !hasPassword) {
        var strength = checkPasswordStrength(password);
        showMessage(strength.message, strength.strength === 'strong' ? 'success' : 'info');
      } else {
        clearMessage();
      }
    });
  }
}

// https://www.w3schools.com/howto/howto_js_trigger_button_enter.asp
var passwordInput = document.getElementById("notepwd");
// Execute a function when the user releases a key on the keyboard
if (passwordInput) {
  passwordInput.addEventListener("keyup", function(event) {
    event.preventDefault(); // Cancel the default action, if needed
    if (event.keyCode === 13) {  // Number 13 is the "Enter" key on the keyboard
      document.getElementById("submitpwd").click(); // Trigger the button element with a click
    }
  });
  
  // 设置密码强度指示器
  setupPasswordStrengthIndicator();
}

// 模态框打开时的初始化
function initializePasswordModal() {
  clearMessage();
  var passwordInput = document.getElementById("notepwd");
  if (passwordInput) {
    passwordInput.focus();
    passwordInput.value = '';
  }
  updatePasswordInterface();
}

// 当模态框显示时调用初始化
document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('password_modal');
  if (modal) {
    // 监听模态框显示事件
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          if (modal.classList.contains('show-modal')) {
            setTimeout(initializePasswordModal, 100);
          }
        }
      });
    });
    observer.observe(modal, { attributes: true });
  }
});

// 兼容原有函数（为了不破坏现有逻辑）
function submitPassword() {
  handlePasswordAction();
}

function passwordRemove() {
  removePassword();
}
