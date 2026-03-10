<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="row g-0">
                        <!-- Sidebar: Chat History / List -->
                        <div class="col-lg-3 col-xl-3 border-end">
                            <div class="card-body border-bottom">
                                <h5 class="card-title mb-2">Consultation List</h5>
                                <input type="text" id="consult-search" class="form-control" placeholder="Search by name or problem">
                            </div>
                            <div class="scrollable position-relative" style="height: calc(100vh - 250px); overflow-y: auto;">
                                <ul class="mailbox list-style-none" id="consult-list">
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
                                        <div id="chat-actions" class="d-flex gap-2">
                                            @if(session('level') == 3)
                                                <button class="btn btn-danger btn-sm px-3" id="btn-cancel" onclick="cancelConsult()" style="background-color: #ff5e78; border-color: #ff5e78; display: none;">Cancel Consultation</button>
                                            @else
                                                <div id="teacher-actions" style="display: none;" class="d-flex gap-2">
                                                    <button class="btn btn-success btn-sm px-3" onclick="approveConsult()">Approve</button>
                                                    <button class="btn btn-danger btn-sm px-3" onclick="rejectConsult()">Reject</button>
                                                </div>
                                            @endif
                                            <button class="btn btn-warning btn-sm" id="btn-end" onclick="endConsult()" style="display: none;">End Conversation</button>
                                            <?php 
                                                $roleRaw = strtolower(trim(session('role') ?? '')); 
                                                $roleKey = str_replace([' ', '-'], ['_', '_'], $roleRaw);
                                                $isCounsel = in_array($roleKey, ['counselling_teacher','counselling_tc','counsellingtc','counselling']);
                                            ?>
                                            @if(session('level') == 2 && (int)(session('teacher_roleid') ?? 0) === 3)
                                            <button class="btn btn-info btn-sm" id="btn-report" onclick="openReportModal()">Submit Report</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Wait Area (Locked) -->
                                <div id="chat-wait" class="flex-grow-1 d-flex align-items-center justify-content-center flex-column d-none">
                                    <div class="text-center">
                                        <i class="fas fa-lock fa-5x text-secondary mb-4" style="opacity: 0.5;"></i>
                                        <h4 id="wait-message" class="font-weight-medium text-dark">Waiting for Teacher to Approve</h4>
                                        <div id="wait-sub-message" class="text-muted mb-4">Your consultation request is still waiting for approval from the teacher.</div>
                                    </div>
                                </div>

                                <!-- Active Chat Area -->
                                <div id="chat-active" class="flex-grow-1 d-flex flex-column d-none" style="overflow: hidden;">
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
                                <div id="chat-history" class="card-body chat-box scrollable flex-grow-1 d-none" style="overflow-y: auto;">
                                    <div class="alert alert-info text-center">This consultation has ended.</div>
                                    <div id="history-messages"></div>
                                </div>

                                <!-- Cancelled Area -->
                                <div id="chat-cancelled" class="flex-grow-1 d-flex align-items-center justify-content-center flex-column d-none">
                                    <div class="text-center">
                                        <i class="fas fa-times-circle fa-5x text-danger mb-4" style="opacity: 0.5;"></i>
                                        <h4 class="font-weight-medium text-dark">Consultation Cancelled</h4>
                                        <div class="text-muted">This consultation request has been cancelled or rejected.</div>
                                    </div>
                                </div>
                                
                                <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Consultation Report</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Outcome</label>
                                                    <textarea class="form-control" id="report_outcome" rows="4"></textarea>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="need_follow_up">
                                                    <label class="form-check-label" for="need_follow_up">Needs follow-up with homeroom teacher</label>
                                                </div>
                                                <div class="mb-3" id="follow_up_notes_container" style="display: none;">
                                                    <label class="form-label">Follow-up Notes</label>
                                                    <textarea class="form-control" id="follow_up_notes" rows="3" placeholder="Details for homeroom teacher"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary" id="btnSubmitReport" onclick="submitConsultReport()">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content bg-transparent border-0">
                                            <div class="modal-body text-center p-0 position-relative">
                                                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                                                <img id="view-image-src" class="img-fluid rounded" style="max-height: 80vh;">
                                                <div class="mt-3">
                                                    <a id="view-image-download" href="#" class="btn btn-primary" download>
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="imageEditModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Image</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-0">
                                                <div class="d-flex justify-content-center bg-dark" style="height: 400px; position: relative; overflow: hidden;" id="editor-container">
                                                    <img id="edit-image" style="max-height: 100%; max-width: 100%; display: block;">
                                                    <canvas id="doodle-canvas" style="position: absolute; top: 0; left: 0; pointer-events: none;"></canvas>
                                                </div>
                                                <div class="p-3 bg-light border-top">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="btn-group">
                                                            <button class="btn btn-outline-primary active" id="btn-mode-view" onclick="setEditMode('view')"><i class="fas fa-eye"></i> View</button>
                                                            <button class="btn btn-outline-primary" id="btn-mode-crop" onclick="setEditMode('crop')"><i class="fas fa-crop-alt"></i> Crop</button>
                                                            <button class="btn btn-outline-primary" id="btn-mode-doodle" onclick="setEditMode('doodle')"><i class="fas fa-pen"></i> Doodle</button>
                                                        </div>
                                                        <div id="doodle-tools" style="display: none;">
                                                            <input type="color" id="doodle-color" value="#ff0000" class="form-control form-control-color d-inline-block" style="width: 40px; height: 38px;">
                                                            <input type="range" id="doodle-size" min="1" max="20" value="5" class="d-inline-block align-middle" style="width: 100px;">
                                                            <button class="btn btn-outline-danger btn-sm" onclick="clearDoodle()"><i class="fas fa-eraser"></i> Clear</button>
                                                        </div>
                                                        <div id="crop-tools" style="display: none;">
                                                            <button class="btn btn-success btn-sm" onclick="applyCrop()">Apply Crop</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-primary" onclick="sendEditedImage()">Send Image</button>
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
    let hasReport = false;
    const consultSearch = document.getElementById('consult-search');
    const consultList = document.getElementById('consult-list');
    let consultSearchTimer = null;
    function renderConsultRows(rows) {
        if (!consultList) return;
        consultList.innerHTML = '<li><div class="message-center">';
        rows.forEach(function(c){
            const disabled = (c.status === 'cancelled') ? 'disabled-item' : '';
            const opacity = (c.status === 'cancelled') ? 'opacity: 0.5; cursor: not-allowed;' : '';
            const badgeClass = c.status === 'active' ? 'bg-success' : (c.status === 'pending' ? 'bg-warning' : (c.status === 'completed' ? 'bg-info' : 'bg-danger'));
            const displayName = ({{ session('level') }} == 3) ? c.teacher_name : c.student_name;
            const onclickAttr = (c.status !== 'cancelled') ? `onclick="loadChat('${c.consultid}','${displayName}','${c.date}','${c.start_time}','${c.end_time}','${c.status}')"` : '';
            consultList.innerHTML += `
                <a href="javascript:void(0)" ${onclickAttr}
                   class="message-item d-flex align-items-center border-bottom px-3 py-2 consult-item ${disabled}" 
                   id="consult-${c.consultid}" style="${opacity}">
                    <div class="user-img">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            ${displayName ? displayName.substring(0,1) : '?'}
                        </div>
                    </div>
                    <div class="w-75 d-inline-block v-middle ps-2">
                        <h6 class="message-title mb-0 mt-1">${displayName || ''}</h6>
                        <span class="font-12 text-nowrap d-block text-muted text-truncate">${c.problem || ''}</span>
                        <span class="font-12 text-nowrap d-block text-muted">${(c.date || '')}</span>
                        <span class="badge ${badgeClass} font-10 text-white">${(c.status || '').charAt(0).toUpperCase() + (c.status || '').slice(1)}</span>
                    </div>
                </a>`;
        });
        consultList.innerHTML += '</div></li>';
    }
    if (consultSearch) {
        consultSearch.addEventListener('input', function(){
            clearTimeout(consultSearchTimer);
            const q = this.value.trim();
            consultSearchTimer = setTimeout(function(){
                fetch('/chat/search?q=' + encodeURIComponent(q), { headers: { 'Accept':'application/json' } })
                    .then(r=>r.json())
                    .then(data=>{
                        if (data.success) renderConsultRows(data.rows || []);
                    });
            }, 250);
        });
    }

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
            
            // Explicitly hide buttons because status is actually active
            const teacherActions = document.getElementById('teacher-actions');
            const btnCancel = document.getElementById('btn-cancel');
            if (teacherActions) {
                teacherActions.style.display = 'none';
                teacherActions.classList.remove('d-flex');
            }
            if (btnCancel) btnCancel.style.display = 'none';

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
        const btnEnd = document.getElementById('btn-end');
        const teacherActions = document.getElementById('teacher-actions');
        const isStudent = {{ session('level') }} == 3;
        const btnReport = document.getElementById('btn-report');

        // Reset all areas using d-none
        [waitArea, activeArea, historyArea, cancelledArea].forEach(el => {
            el.classList.remove('d-flex');
            el.classList.add('d-none');
        });

        // Hide all action buttons initially
        if (btnCancel) btnCancel.style.display = 'none';
        if (teacherActions) {
            teacherActions.style.display = 'none';
            teacherActions.classList.remove('d-flex'); // Remove d-flex to ensure it hides
        }
        btnEnd.style.display = 'none';
        if (btnReport) btnReport.style.display = 'none';

        if (status === 'pending') {
            waitArea.classList.remove('d-none');
            waitArea.classList.add('d-flex');
            if (isStudent) {
                if (btnCancel) btnCancel.style.display = 'block';
            } else {
                if (teacherActions) {
                    teacherActions.style.display = 'flex';
                    teacherActions.classList.add('d-flex'); // Add d-flex back when showing
                }
            }
        } else if (status === 'active') {
            activeArea.classList.remove('d-none');
            activeArea.classList.add('d-flex');
            
            // Ensure teacherActions is hidden
            if (teacherActions) {
                teacherActions.style.display = 'none';
                teacherActions.classList.remove('d-flex');
            }

            // Check if 5 minutes passed for End Conversation button
            // header-time text content: "YYYY-MM-DD (HH:MM - HH:MM)"
            const timeText = document.getElementById('header-time').textContent;
            const datePart = timeText.split(' ')[0];
            const timeMatch = timeText.match(/\((.*?)\)/);
            if (timeMatch) {
                const startTimeStr = timeMatch[1].split(' - ')[0]; // "HH:MM"
                const startDateTime = new Date(datePart + ' ' + startTimeStr);
                const now = new Date();
                const diffMins = (now - startDateTime) / 1000 / 60;
                
                if (diffMins >= 5) {
                    btnEnd.style.display = 'block';
                } else {
                    // If less than 5 mins, maybe set timeout?
                    // For now, let the periodic refresh handle it or just hide it
                    btnEnd.style.display = 'none';
                }
            } else {
                btnEnd.style.display = 'block'; // Fallback
            }

            // Tombol cancel disembunyikan saat sudah active (mulai chat)
            if (btnCancel) btnCancel.style.display = 'none'; 
            if (!isStudent && btnReport && !hasReport) btnReport.style.display = 'inline-block';
        } else if (status === 'completed') {
            historyArea.classList.remove('d-none');
            historyArea.classList.add('d-flex');
            historyArea.style.flexDirection = 'column';
            if (!isStudent && btnReport && !hasReport) btnReport.style.display = 'inline-block';
        } else if (status === 'cancelled') {
            cancelledArea.classList.remove('d-none');
            cancelledArea.classList.add('d-flex');
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
                hasReport = !!data.has_report;

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
                    const btnReport = document.getElementById('btn-report');
                    if (btnReport) {
                        if (status === 'cancelled' || hasReport) btnReport.style.display = 'none';
                    }
                }

                const container = document.getElementById(targetId);
                let html = '<div class="chat-list">';
                const currentUserId = {{ session('userid') }};
                const now = new Date();

                messages.forEach(msg => {
                    const isMe = msg.userid == currentUserId;
                    const msgTime = new Date(msg.created_at);
                    const diffMins = (now - msgTime) / 1000 / 60;
                    
                    html += `<div class="chat-item ${isMe ? 'me' : 'other'}">
                        <div class="d-flex align-items-center ${isMe ? 'flex-row-reverse' : ''}">
                            <div class="chat-bubble shadow-sm" style="word-break: break-word; overflow-wrap: break-word; max-width: 100%; width: fit-content; min-width: 50px;">
                                ${msg.message ? `<div>${msg.message}</div>` : ''}
                                ${msg.file ? `<div><a href="javascript:void(0)" onclick="viewImage('/storage/${msg.file}')">
                                    ${msg.file.match(/\.(jpg|jpeg|png|gif)$/i) ? `<img src="/storage/${msg.file}" class="chat-img">` : '<i class="fas fa-file"></i> View File'}
                                </a></div>` : ''}
                            </div>
                        </div>
                        <div class="chat-time">${msgTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
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

    function viewImage(src) {
        const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
        document.getElementById('view-image-src').src = src;
        document.getElementById('view-image-download').href = src;
        modal.show();
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
        
        const file = input.files[0];
        
        // If not image, send directly
        if (!file.type.match('image.*')) {
            uploadFile(file);
            return;
        }

        // Open Editor
        const reader = new FileReader();
        reader.onload = function(e) {
            openImageEditor(e.target.result);
        };
        reader.readAsDataURL(file);
        
        // Reset input so same file can be selected again
        input.value = '';
    }

    function uploadFile(file, filename = null) {
        const formData = new FormData();
        formData.append('consultid', currentConsultId);
        if (filename) {
            formData.append('file', file, filename);
        } else {
            formData.append('file', file);
        }
        formData.append('_token', '{{ csrf_token() }}');

        fetch('/chat/send', {
            method: 'POST',
            body: formData
        }).then(() => {
            loadMessages(currentConsultId, 'chat-messages');
        });
    }

    // --- Image Editor Logic ---
    let cropper = null;
    let editorMode = 'view';
    const editImage = document.getElementById('edit-image');
    const doodleCanvas = document.getElementById('doodle-canvas');
    const ctx = doodleCanvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    function openImageEditor(src) {
        editImage.src = src;
        const modal = new bootstrap.Modal(document.getElementById('imageEditModal'));
        
        editImage.onload = function() {
            // Reset canvas to match image size (displayed size)
            // We need to handle this carefully. Cropper replaces the image with its own container.
            // For doodling, we need the canvas to overlay the image exactly.
            resetEditor();
            modal.show();
        }
    }

    function resetEditor() {
        if (cropper) cropper.destroy();
        cropper = null;
        ctx.clearRect(0, 0, doodleCanvas.width, doodleCanvas.height);
        setEditMode('view');
        
        // Resize canvas to match the image's displayed size
        // Note: This simple approach assumes image fits in container.
        // We might need to wait for modal to be visible for correct dimensions.
        setTimeout(() => {
           adjustCanvasSize();
        }, 500);
    }

    function adjustCanvasSize() {
        doodleCanvas.width = editImage.clientWidth;
        doodleCanvas.height = editImage.clientHeight;
        doodleCanvas.style.width = editImage.clientWidth + 'px';
        doodleCanvas.style.height = editImage.clientHeight + 'px';
        doodleCanvas.style.top = editImage.offsetTop + 'px';
        doodleCanvas.style.left = editImage.offsetLeft + 'px';
    }

    function setEditMode(mode) {
        editorMode = mode;
        document.getElementById('btn-mode-view').classList.toggle('active', mode === 'view');
        document.getElementById('btn-mode-crop').classList.toggle('active', mode === 'crop');
        document.getElementById('btn-mode-doodle').classList.toggle('active', mode === 'doodle');
        
        document.getElementById('doodle-tools').style.display = mode === 'doodle' ? 'block' : 'none';
        document.getElementById('crop-tools').style.display = mode === 'crop' ? 'block' : 'none';

        if (mode === 'crop') {
            doodleCanvas.style.display = 'none';
            if (!cropper) {
                cropper = new Cropper(editImage, {
                    viewMode: 1,
                    autoCropArea: 1,
                });
            }
        } else {
            if (cropper) {
                // If switching away from crop without applying, we might want to destroy it?
                // Or keep it? Let's destroy to show preview.
                // But usually user expects "Crop" mode to persist until applied.
                // For simplicity: If you switch mode, we destroy cropper (cancel crop).
                // Or we can just hide it. Cropper modifies DOM heavily.
                // Let's enforce: Apply Crop to save changes. Switching mode cancels crop UI but keeps image.
                cropper.destroy();
                cropper = null;
            }
            doodleCanvas.style.display = 'block';
            adjustCanvasSize(); // Re-adjust in case image size changed
        }

        if (mode === 'doodle') {
            doodleCanvas.style.pointerEvents = 'auto';
        } else {
            doodleCanvas.style.pointerEvents = 'none';
        }
    }

    function applyCrop() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas();
        editImage.src = canvas.toDataURL();
        cropper.destroy();
        cropper = null;
        setEditMode('view');
        // Clear doodle because dimensions changed
        ctx.clearRect(0, 0, doodleCanvas.width, doodleCanvas.height);
        setTimeout(adjustCanvasSize, 100);
    }

    function clearDoodle() {
        ctx.clearRect(0, 0, doodleCanvas.width, doodleCanvas.height);
    }

    // Doodle Drawing Logic
    doodleCanvas.addEventListener('mousedown', (e) => {
        isDrawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });
    doodleCanvas.addEventListener('mousemove', draw);
    doodleCanvas.addEventListener('mouseup', () => isDrawing = false);
    doodleCanvas.addEventListener('mouseout', () => isDrawing = false);

    // Touch support
    doodleCanvas.addEventListener('touchstart', (e) => {
        isDrawing = true;
        const rect = doodleCanvas.getBoundingClientRect();
        lastX = e.touches[0].clientX - rect.left;
        lastY = e.touches[0].clientY - rect.top;
        e.preventDefault();
    });
    doodleCanvas.addEventListener('touchmove', (e) => {
        if (!isDrawing) return;
        const rect = doodleCanvas.getBoundingClientRect();
        const x = e.touches[0].clientX - rect.left;
        const y = e.touches[0].clientY - rect.top;
        drawCore(x, y);
        e.preventDefault();
    });
    doodleCanvas.addEventListener('touchend', () => isDrawing = false);

    function draw(e) {
        if (!isDrawing) return;
        drawCore(e.offsetX, e.offsetY);
    }

    function drawCore(x, y) {
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.strokeStyle = document.getElementById('doodle-color').value;
        ctx.lineWidth = document.getElementById('doodle-size').value;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
        [lastX, lastY] = [x, y];
    }

    function sendEditedImage() {
        // 1. Determine base canvas (cropped or original)
        let baseCanvas;
        
        if (cropper) {
            // User hasn't clicked "Apply Crop" yet, so use current crop state
            baseCanvas = cropper.getCroppedCanvas();
        } else {
            // Either no crop was done, or "Apply Crop" was already clicked (updating editImage.src)
            baseCanvas = document.createElement('canvas');
            baseCanvas.width = editImage.naturalWidth;
            baseCanvas.height = editImage.naturalHeight;
            const ctx = baseCanvas.getContext('2d');
            ctx.drawImage(editImage, 0, 0);
        }

        // 2. Create final canvas to merge Base + Doodle
        const finalCanvas = document.createElement('canvas');
        finalCanvas.width = baseCanvas.width;
        finalCanvas.height = baseCanvas.height;
        const fCtx = finalCanvas.getContext('2d');

        // Draw the base image
        fCtx.drawImage(baseCanvas, 0, 0);

        // 3. Draw the doodle canvas on top
        if (doodleCanvas.width > 0) {
            fCtx.drawImage(doodleCanvas, 0, 0, finalCanvas.width, finalCanvas.height);
        }

        // 4. Convert to blob and upload
        finalCanvas.toBlob(function(blob) {
            // Ensure we pass a filename so it's treated as a file upload
            const filename = 'edited_image_' + new Date().getTime() + '.jpg';
            uploadFile(blob, filename);
            
            // Close modal
            const modalEl = document.getElementById('imageEditModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        }, 'image/jpeg', 0.9);
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

    // Checkbox toggle logic for follow up
    document.addEventListener('DOMContentLoaded', function() {
        const needFollowUpCheck = document.getElementById('need_follow_up');
        const followUpNotesContainer = document.getElementById('follow_up_notes_container');
        
        if(needFollowUpCheck){
            needFollowUpCheck.addEventListener('change', function() {
                if(this.checked) {
                    if(followUpNotesContainer) followUpNotesContainer.style.display = 'block';
                } else {
                    if(followUpNotesContainer) followUpNotesContainer.style.display = 'none';
                }
            });
        }
    });

    function openReportModal() {
        // If no current consult selected, still open modal as fallback
        if (!currentConsultId) {
            const modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();
            return;
        }
        // Check if already has report; on error, open anyway (server will enforce single report)
        fetch(`/chat/get/${currentConsultId}`)
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.json();
            })
            .then(data => {
                if (data && data.has_report) {
                    alert('Report already submitted for this consultation.');
                    return;
                }
                const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                modal.show();
            })
            .catch(() => {
                const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                modal.show();
            });
    }

    function submitConsultReport() {
        const outcome = document.getElementById('report_outcome').value;
        const needFollow = document.getElementById('need_follow_up').checked;
        const notes = document.getElementById('follow_up_notes').value;
        
        // Simple validation
        if(!outcome.trim()) {
            alert('Please enter outcome');
            return;
        }

        const btn = document.getElementById('btnSubmitReport');
        btn.disabled = true;
        btn.textContent = 'Submitting...';

        fetch('/chat/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                consultid: currentConsultId,
                report_outcome: outcome,
                need_follow_up: needFollow,
                follow_up_notes: notes
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Submit';
            
            if (data.success) {
                const modalEl = document.getElementById('reportModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                alert('Report submitted successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to submit report');
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.textContent = 'Submit';
            alert('Error submitting report');
        });
    }
</script>
