let activeChatUserId = null;
let chatInterval = null;

function toggleChat() {
    const window = document.getElementById('chat-window');
    const footer = document.getElementById('chat-footer');

    if (window.style.display === 'flex') {
        window.style.display = 'none';
        clearInterval(chatInterval);
    } else {
        window.style.display = 'flex';
        if (IS_ADMIN) {
            loadUserList();
            footer.style.display = 'none';
            document.getElementById('chat-title').innerText = 'لیست گفتگوها';
        } else {
            openChat(null); // Non-admins always chat with admin
            footer.style.display = 'flex';
        }
    }
}

function loadUserList() {
    fetch(BASE_URL + 'chat_api.php?action=fetch_users')
        .then(res => res.json())
        .then(users => {
            const body = document.getElementById('chat-body');
            body.innerHTML = '';
            if (users.length === 0) {
                body.innerHTML = '<p style="text-align:center; padding:20px;">هیچ پیامی یافت نشد.</p>';
            }
            users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'chat-user-item ' + (user.unread_count > 0 ? 'unread' : '');
                div.innerHTML = `${user.name} ${user.unread_count > 0 ? `<span style="background:red; color:#fff; border-radius:50%; padding:2px 6px; font-size:10px;">${user.unread_count}</span>` : ''}`;
                div.onclick = () => openChat(user.id, user.name);
                body.appendChild(div);
            });
        });
}

function openChat(userId, userName = 'پشتیبانی آنلاین') {
    activeChatUserId = userId;
    document.getElementById('chat-title').innerText = userName;
    document.getElementById('chat-footer').style.display = 'flex';

    // Clear any existing back buttons first to prevent duplicates
    const existingBackBtn = document.getElementById('chat-back-btn');
    if (existingBackBtn) existingBackBtn.remove();

    if (IS_ADMIN && userId) {
        // Add a back button
        const backBtn = document.createElement('span');
        backBtn.id = 'chat-back-btn';
        backBtn.innerText = ' ⬅ ';
        backBtn.style.cursor = 'pointer';
        backBtn.onclick = (e) => {
            e.stopPropagation();
            activeChatUserId = null;
            clearInterval(chatInterval);
            loadUserList();
            document.getElementById('chat-title').innerText = 'لیست گفتگوها';
            document.getElementById('chat-footer').style.display = 'none';
            backBtn.remove();
        };
        document.getElementById('chat-header').prepend(backBtn);
    }

    loadMessages();
    clearInterval(chatInterval);
    chatInterval = setInterval(loadMessages, 3000);
}

function loadMessages() {
    if (!activeChatUserId && IS_ADMIN) return;

    let url = BASE_URL + 'chat_api.php?action=fetch_messages';
    if (activeChatUserId) url += '&user_id=' + activeChatUserId;

    fetch(url)
        .then(res => res.json())
        .then(messages => {
            const body = document.getElementById('chat-body');
            const isAtBottom = body.scrollHeight - body.scrollTop === body.clientHeight;

            body.innerHTML = '';
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'msg ' + (msg.sender_id == CURRENT_USER_ID ? 'msg-sent' : 'msg-received');
                div.innerText = msg.message;
                body.appendChild(div);
            });

            if (isAtBottom) body.scrollTop = body.scrollHeight;
        });
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;

    const formData = new FormData();
    formData.append('message', message);
    if (activeChatUserId) formData.append('receiver_id', activeChatUserId);

    fetch(BASE_URL + 'chat_api.php?action=send', {
        method: 'POST',
        body: formData
    }).then(() => {
        input.value = '';
        loadMessages();
    });
}

// Polling for new message indicator
setInterval(() => {
    fetch(BASE_URL + 'chat_api.php?action=check_new')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('chat-badge');
            badge.style.display = data.new_messages > 0 ? 'block' : 'none';
        });
}, 5000);

// Allow Enter key to send message
document.getElementById('chat-input')?.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
