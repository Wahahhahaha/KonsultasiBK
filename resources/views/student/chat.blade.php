<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="row g-0">
                        <!-- Sidebar: Chat History / List -->
                        <div class="col-lg-3 col-xl-3 border-end">
                            <div class="card-body border-bottom">
                                <h5 class="card-title mb-0">Consultation List</h5>
                            </div>
                            <div class="scrollable position-relative" style="height: calc(100vh - 250px); overflow-y: auto;">
                                <ul class="mailbox list-style-none">
                                    <li>
                                        <div class="message-center">
                                            @foreach($consults as $c)
                                            <a href="javascript:void(0)" 
                                               @if($c->status != 'cancelled')
                                               onclick="loadChat('{{ $c->consultid }}', '{{ session('level') == 3 ? $c->teacher_name : $c->student_name }}', '{{ $c->date }}', '{{ $c->start_time }}', '{{ $c->end_time }}', '{{ $c->status }}')"
                                               @endif
                                               class="message-item d-flex align-items-center border-bottom px-3 py-2 consult-item {{ $c->status == 'cancelled' ? 'disabled-item' : '' }}" 
                                               id="consult-{{ $c->consultid }}"
                                               style="{{ $c->status == 'cancelled' ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                                                <div class="user-img">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        {{ substr(session('level') == 3 ? $c->teacher_name : $c->student_name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="w-75 d-inline-block v-middle ps-2">
                                                    <h6 class="message-title mb-0 mt-1">
                                                        {{ session('level') == 3 ? $c->teacher_name : $c->student_name }}
                                                    </h6>
                                                    <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ $c->problem }}</span>
                                                    <span class="font-12 text-nowrap d-block text-muted">{{ date('d M Y', strtotime($c->date)) }}</span>
                                                    <span class="badge {{ $c->status == 'active' ? 'bg-success' : ($c->status == 'pending' ? 'bg-warning' : ($c->status == 'completed' ? 'bg-info' : 'bg-danger')) }} font-10 text-white">
                                                        {{ ucfirst($c->status) }}
                                                    </span>
                                                </div>
                                            </a>
                                            @endforeach
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Chat Area -->
                        <div class="col-lg-9 col-xl-9 d-flex flex-column" style="height: calc(100vh - 150px); overflow: hidden;">
                            <div id="chat-welcome" class="d-flex align-items-center justify-content-center h-100 w-100">
                                <div class="text-center">
                                    <i class="far fa-comments fa-4x text-muted mb-3"></i>
                                    <h4>Select a consultation to start chatting</h4>
                                </div>
                            </div>

                            <div id="chat-container" class="h-100 w-100 flex-column d-none">
                                <!-- Chat Header -->
                                <div class="card-body border-bottom flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <span id="header-avatar"></span>
                                            </div>
                                            <div>
                                                <h5 class="mb-0" id="header-name">Teacher Name</h5>
                                                <small class="text-muted" id="header-time"></small>
                                            </div>
                                        </div>
                                        <div id="chat-actions">
                                            @if(session('level') == 3)
                                                <button class="btn btn-danger btn-sm px-3" id="btn-cancel" onclick="cancelConsult()" style="background-color: #ff5e78; border-color: #ff5e78; display: none;">Cancel Consultation</button>
                                            @else
                                                <div id="teacher-actions" style="display: none;">
                                                    <button class="btn btn-success btn-sm px-3 me-2" onclick="approveConsult()">Approve</button>
                                                    <button class="btn btn-danger btn-sm px-3" onclick="rejectConsult()">Reject</button>
                                                </div>
                                            @endif
                                            <button class="btn btn-warning btn-sm" id="btn-end" onclick="endConsult()" style="display: none;">End Conversation</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Wait Area (Locked) -->
                                <div id="chat-wait" class="flex-grow-1 d-flex align-items-center justify-content-center flex-column" style="display: none;">
                                    <div class="text-center">
                                        <i class="fas fa-lock fa-5x text-secondary mb-4" style="opacity: 0.5;"></i>
                                        <h4 id="wait-message" class="font-weight-medium text-dark">Waiting for Teacher to Approve</h4>
                                        <div id="wait-sub-message" class="text-muted mb-4">Your consultation request is still waiting for approval from the teacher.</div>
                                    </div>
                                </div>

                                <!-- Active Chat Area -->
                                <div id="chat-active" class="flex-grow-1 d-flex flex-column" style="display: none; overflow: hidden;">
                                    <div class="card-body chat-box scrollable flex-grow-1" id="chat-messages" style="overflow-y: auto;">
                                        <!-- Messages will be loaded here -->
                                    </div>
                                    <div class="card-body border-top flex-shrink-0">
                                        <form id="chat-form" onsubmit="return sendMessage(event)">
                                            <div class="row">
                                                <div class="col-9">
                                                    <div class="input-group">
                                                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here...">
                                                        <input type="file" id="chat-file" style="display: none;" onchange="handleFileUpload(this)">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('chat-file').click()">
                                                            <i class="fas fa-paperclip"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <button class="btn btn-primary w-100" type="submit">Send</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- History / Completed Area -->
                                <div id="chat-history" style="display: none;" class="card-body chat-box scrollable flex-grow-1" style="overflow-y: auto;">
                                    <div class="alert alert-info text-center">This consultation has ended.</div>
                                    <div id="history-messages"></div>
                                </div>

                                <!-- Cancelled Area -->
                                <div id="chat-cancelled" class="flex-grow-1 d-flex align-items-center justify-content-center flex-column" style="display: none;">
                                    <div class="text-center">
                                        <i class="fas fa-times-circle fa-5x text-danger mb-4" style="opacity: 0.5;"></i>
                                        <h4 class="font-weight-medium text-dark">Consultation Cancelled</h4>
                                        <div class="text-muted">This consultation request has been cancelled or rejected.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .chat-list { list-style: none; padding: 0; }
    .chat-item { margin-bottom: 15px; display: flex; flex-direction: column; }
    .chat-item.me { align-items: flex-end; }
    .chat-item.other { align-items: flex-start; }
    .chat-bubble { padding: 10px 15px; border-radius: 15px; max-width: 70%; position: relative; }
    .me .chat-bubble { background-color: #7460ee; color: white; border-bottom-right-radius: 2px; }
    .other .chat-bubble { background-color: #f1f1f1; color: #333; border-bottom-left-radius: 2px; }
    .chat-time { font-size: 10px; margin-top: 5px; color: #999; }
    .chat-img { max-width: 200px; border-radius: 10px; margin-top: 5px; }
    .consult-item.active { background-color: #f8f9fa; border-left: 4px solid #7460ee; }
</style>

<script>
    let currentConsultId = null;
    let currentStatus = null;
    let chatInterval = null;
    let timeCheckInterval = null;

    function loadChat(id, name, date, start, end, status) {
        currentConsultId = id;
        currentStatus = status;
        document.querySelectorAll('.consult-item').forEach(el => el.classList.remove('active'));
        document.getElementById('consult-' + id).classList.add('active');

        // Sembunyikan Welcome Screen secara total
        const welcomeScreen = document.getElementById('chat-welcome');
        welcomeScreen.classList.remove('d-flex');
        welcomeScreen.classList.add('d-none');

        // Tampilkan Container Chat
        const chatContainer = document.getElementById('chat-container');
        chatContainer.classList.remove('d-none');
        chatContainer.classList.add('d-flex');
        
        document.getElementById('header-name').textContent = name;
        document.getElementById('header-avatar').textContent = name.charAt(0);
        document.getElementById('header-time').textContent = date + ' (' + start + ' - ' + end + ')';

        updateChatUI(status, date, start, end);
        
        if (chatInterval) clearInterval(chatInterval);
        if (timeCheckInterval) clearInterval(timeCheckInterval);

        timeCheckInterval = setInterval(() => checkTimeAndUnlock(date, start, end), 3000);
        checkTimeAndUnlock(date, start, end);
        
        if (status === 'completed') {
            loadMessages(id, 'history-messages');
        } else if (status === 'active') {
            loadMessages(id, 'chat-messages');
            chatInterval = setInterval(() => loadMessages(id, 'chat-messages'), 3000);
        }
    }

    function checkTimeAndUnlock(date, start, end) {
        const now = new Date();
        const startTime = new Date(date + ' ' + start);
        const endTime = new Date(date + ' ' + end);
        const waitMessage = document.getElementById('wait-message');
        const waitSubMessage = document.getElementById('wait-sub-message');
        const isStudent = {{ session('level') }} == 3;
        
        if (currentStatus === 'cancelled' || currentStatus === 'completed') {
            updateChatUI(currentStatus);
            clearInterval(timeCheckInterval);
            if (chatInterval) clearInterval(chatInterval);
            return;
        }

        if (currentStatus === 'pending') {
            updateChatUI('pending');
            if (isStudent) {
                waitMessage.textContent = 'Waiting for Teacher to Approve';
                waitSubMessage.textContent = 'Your consultation request is still waiting for approval from the teacher.';
            } else {
                waitMessage.textContent = 'Consultation Request';
                waitSubMessage.textContent = 'A student wants to consult with you. Please approve or reject this request.';
            }
            // Always poll for messages to get status updates
            if (!chatInterval) {
                chatInterval = setInterval(() => loadMessages(currentConsultId, 'chat-messages'), 3000);
            }
            return;
        }

        // Status must be 'active' if it reaches here
        if (now >= startTime && now <= endTime) {
            // Unlock chat
            updateChatUI('active');
            if (!chatInterval) {
                loadMessages(currentConsultId, 'chat-messages');
                chatInterval = setInterval(() => loadMessages(currentConsultId, 'chat-messages'), 3000);
            }
        } else if (now < startTime) {
            // Still waiting
            updateChatUI('pending');
            waitMessage.textContent = 'Wait till ' + start + ' to start chatting';
            waitSubMessage.textContent = isStudent ? 'Your request is approved! Please wait until the scheduled time.' : 'You have approved this request. Please wait until the scheduled time.';
            // Still need to poll for messages/status
            if (!chatInterval) {
                chatInterval = setInterval(() => loadMessages(currentConsultId, 'chat-messages'), 3000);
            }
        } else {
            // Time passed
            if (currentStatus === 'active') {
                updateChatUI('completed');
            }
        }
    }

    function updateChatUI(status, date, start, end) {
        const waitArea = document.getElementById('chat-wait');
        const activeArea = document.getElementById('chat-active');
        const historyArea = document.getElementById('chat-history');
        const cancelledArea = document.getElementById('chat-cancelled');
        const btnCancel = document.getElementById('btn-cancel');
        const btnCancelWait = document.getElementById('btn-cancel-wait');
        const btnEnd = document.getElementById('btn-end');
        const teacherActions = document.getElementById('teacher-actions');
        const isStudent = {{ session('level') }} == 3;

        waitArea.style.display = 'none';
        activeArea.style.display = 'none';
        historyArea.style.display = 'none';
        cancelledArea.style.display = 'none';
        if (btnCancel) btnCancel.style.display = 'none';
        if (btnCancelWait) btnCancelWait.style.display = 'none';
        if (teacherActions) teacherActions.style.display = 'none';
        btnEnd.style.display = 'none';

        if (status === 'pending') {
            waitArea.style.display = 'flex';
            if (isStudent) {
                if (btnCancel) btnCancel.style.display = 'block';
                if (btnCancelWait) btnCancelWait.style.display = 'block';
            } else {
                if (teacherActions) teacherActions.style.display = 'block';
            }
        } else if (status === 'active') {
            activeArea.style.display = 'flex';
            btnEnd.style.display = 'block';
            if (isStudent && btnCancel) btnCancel.style.display = 'block';
        } else if (status === 'completed') {
            historyArea.style.display = 'flex';
            historyArea.style.flexDirection = 'column';
        } else if (status === 'cancelled') {
            cancelledArea.style.display = 'flex';
        }
    }

    function approveConsult() {
        if (!confirm('Approve this consultation?')) return;
        fetch('/chat/approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ consultid: currentConsultId })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                // Update status locally and refresh UI
                currentStatus = 'active';
                const dateText = document.getElementById('header-time').textContent.split(' ')[0];
                const timeMatch = document.getElementById('header-time').textContent.match(/\((.*?)\)/);
                const timeRange = timeMatch ? timeMatch[1] : '';
                const start = timeRange.split(' - ')[0];
                const end = timeRange.split(' - ')[1];
                
                checkTimeAndUnlock(dateText, start, end);
                
                // Update badge in sidebar
                const consultItem = document.getElementById(`consult-${currentConsultId}`);
                const badge = consultItem.querySelector(`.badge`);
                if (badge) {
                    badge.className = 'badge bg-success font-10 text-white';
                    badge.textContent = 'Active';
                }
            }
        });
    }

    function rejectConsult() {
        if (!confirm('Reject this consultation?')) return;
        fetch('/chat/reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ consultid: currentConsultId })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                // Update status locally and refresh UI
                currentStatus = 'cancelled';
                updateChatUI('cancelled');
                
                // Update badge in sidebar and disable it
                const consultItem = document.getElementById(`consult-${currentConsultId}`);
                consultItem.style.opacity = '0.5';
                consultItem.style.cursor = 'not-allowed';
                consultItem.removeAttribute('onclick');
                
                const badge = consultItem.querySelector(`.badge`);
                if (badge) {
                    badge.className = 'badge bg-danger font-10 text-white';
                    badge.textContent = 'Cancelled';
                }
            }
        });
    }

    function loadMessages(id, targetId) {
        fetch('/chat/messages/' + id)
            .then(response => response.json())
            .then(data => {
                const messages = data.messages;
                const status = data.status;

                // Update current status if changed
                if (currentStatus !== status) {
                    currentStatus = status;
                    const dateText = document.getElementById('header-time').textContent.split(' ')[0];
                    const timeMatch = document.getElementById('header-time').textContent.match(/\((.*?)\)/);
                    if (timeMatch) {
                        const timeRange = timeMatch[1];
                        const start = timeRange.split(' - ')[0];
                        const end = timeRange.split(' - ')[1];
                        checkTimeAndUnlock(dateText, start, end);
                    }
                    
                    // Update badge in sidebar
                    const consultItem = document.getElementById(`consult-${currentConsultId}`);
                    if (consultItem) {
                        const badge = consultItem.querySelector(`.badge`);
                        if (badge) {
                            const statusClasses = {
                                'active': 'bg-success',
                                'pending': 'bg-warning',
                                'completed': 'bg-info',
                                'cancelled': 'bg-danger'
                            };
                            badge.className = `badge ${statusClasses[status] || 'bg-secondary'} font-10 text-white`;
                            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        }

                        if (status === 'cancelled') {
                            consultItem.style.opacity = '0.5';
                            consultItem.style.cursor = 'not-allowed';
                            consultItem.removeAttribute('onclick');
                        }
                    }
                }

                const container = document.getElementById(targetId);
                let html = '<div class="chat-list">';
                const currentUserId = {{ session('userid') }};

                messages.forEach(msg => {
                    const isMe = msg.userid == currentUserId;
                    html += `<div class="chat-item ${isMe ? 'me' : 'other'}">
                        <div class="chat-bubble shadow-sm">
                            ${msg.message ? `<div>${msg.message}</div>` : ''}
                            ${msg.file ? `<div><a href="/storage/${msg.file}" target="_blank">
                                ${msg.file.match(/\.(jpg|jpeg|png|gif)$/i) ? `<img src="/storage/${msg.file}" class="chat-img">` : '<i class="fas fa-file"></i> View File'}
                            </a></div>` : ''}
                        </div>
                        <div class="chat-time">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                    </div>`;
                });
                html += '</div>';
                
                const oldScrollHeight = container.scrollHeight;
                container.innerHTML = html;
                
                // Only scroll if was at bottom
                if (container.scrollTop + container.clientHeight >= oldScrollHeight - 100) {
                    container.scrollTop = container.scrollHeight;
                }
            });
    }

    function sendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        if (!message) return false;

        const formData = new FormData();
        formData.append('consultid', currentConsultId);
        formData.append('message', message);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('/chat/send', {
            method: 'POST',
            body: formData
        }).then(() => {
            input.value = '';
            loadMessages(currentConsultId, 'chat-messages');
        });

        return false;
    }

    function handleFileUpload(input) {
        if (!input.files || !input.files[0]) return;
        
        const formData = new FormData();
        formData.append('consultid', currentConsultId);
        formData.append('file', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('/chat/send', {
            method: 'POST',
            body: formData
        }).then(() => {
            input.value = '';
            loadMessages(currentConsultId, 'chat-messages');
        });
    }

    function cancelConsult() {
        if (!confirm('Are you sure you want to cancel this booking?')) return;

        fetch('/chat/cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ consultid: currentConsultId })
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    function endConsult() {
        if (!confirm('Do you want to end this conversation? This requires agreement from both parties.')) return;

        fetch('/chat/end', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ consultid: currentConsultId })
        }).then(res => res.json()).then(data => {
            if (data.completed) {
                alert('Conversation ended and moved to history.');
                location.reload();
            } else {
                alert('Wait for the other party to also agree to end the conversation.');
            }
        });
    }
</script>