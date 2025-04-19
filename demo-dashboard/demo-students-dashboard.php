<?php

/**
 * Student Content
 */

 function demoRenderStudentContent($section = '', $action = '', $data = []) {
    ob_start();
    $section = !empty($section) ? $section : (isset($_GET['demo-section']) ? sanitize_text_field($_GET['demo-section']) : 'dashboard');
    $action = !empty($action) ? $action : (isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : '');

    switch ($section) {
        case 'attendance-view':
            echo demoRenderStudentAttendance(demoGetStudentData('attendance'));
            break;
        case 'fees-view':
            echo demoRenderStudentFees(demoGetStudentData('fees'));
            break;
        case 'notices-view':
            echo demoRenderStudentNotices(demoGetStudentData('notices'));
            break;
        case 'library-view':
            echo demoRenderStudentLibrary(demoGetStudentData('library'));
            break;
        case 'exams':
            echo demoRenderStudentExams(demoGetStudentData('exams'));
            break;
        case 'inventory-transactions-view':
            echo demoRenderStudentInventoryTransactions(demoGetStudentData('inventory_transactions'));
            break;
        case 'chats':
            if ($action === 'send-chat') {
                echo demoRenderStudentSendChat();
            } else {
                echo demoRenderStudentChats(demoGetStudentData('chats'));
            }
            break;
        case 'reports-view':
            echo demoRenderStudentReports(demoGetStudentData('reports'));
            break;
        case 'results-view':
            echo demoRenderStudentResults(demoGetStudentData('results'));
            break;
        case 'timetable-view':
            echo demoRenderStudentTimetable(demoGetStudentData('timetable'));
            break;
        case 'homework-view':
            if ($action === 'submit-homework') {
                echo demoRenderStudentSubmitHomework();
            } else {
                echo demoRenderStudentHomework(demoGetStudentData('homework'));
            }
            break;
        case 'dashboard':
        default:
            echo demoRenderStudentOverview();
            break;
    }

    return ob_get_clean();
}

function demoGetStudentData($section = '') {
    $data = [
        'attendance' => [
            ['id' => 'ATT001', 'date' => '2025-04-15', 'status' => 'Present'],
            ['id' => 'ATT002', 'date' => '2025-04-16', 'status' => 'Absent'],
        ],
        'fees' => [
            ['fee_id' => 'FEE001', 'amount' => '150.00', 'due_date' => '2025-05-01', 'status' => 'Pending'],
            ['fee_id' => 'FEE002', 'amount' => '100.00', 'due_date' => '2025-04-10', 'status' => 'Paid'],
        ],
        'notices' => [
            ['id' => 'NOT001', 'title' => 'Parent-Teacher Meeting', 'date' => '2025-04-20', 'content' => 'Meeting for Class 1A parents.'],
        ],
        'library' => [
            ['book_id' => 'B001', 'title' => 'Algebra Basics', 'author' => 'Dr. Smith', 'status' => 'Checked Out', 'due_date' => '2025-04-25'],
        ],
        'inventory_transactions' => [
            ['transaction_id' => 'TRN001', 'item_name' => 'Projector', 'date' => '2025-04-15', 'status' => 'Borrowed'],
        ],
        'chats' => [
            ['chat_id' => 'CH001', 'sender' => 'Teacher Alice', 'message' => 'Please submit homework.', 'timestamp' => '2025-04-15 10:00'],
        ],
        'reports' => [
            ['report_id' => 'REP001', 'title' => 'Class 1A Progress', 'date' => '2025-04-15', 'summary' => 'Overall improvement in math scores.'],
        ],
        'results' => [
            ['result_id' => 'RES001', 'exam' => 'Math Exam', 'score' => 85, 'grade' => 'B+'],
        ],
        'timetable' => [
            ['id' => 'TIM001', 'subject' => 'Mathematics', 'day' => 'Monday', 'time_slot' => '08:00-09:00'],
        ],
        'homework' => [
            ['id' => 'HW001', 'subject' => 'Mathematics', 'title' => 'Algebra Practice', 'due_date' => '2025-04-20', 'status' => 'Pending'],
        ],
        'exams' => [
            ['name' => 'Midterm Math Exam', 'date' => '2025-05-10', 'class' => 'Class 1A'],
            ['name' => 'Science Quiz', 'date' => '2025-05-12', 'class' => 'Class 1A'],
        ],
    ];
    return $section ? ($data[$section] ?? []) : $data;
}

function demoRenderStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Attendance</h2>
        <div class="alert alert-info">View your attendance records.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Attendance ID</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $record): ?>
                            <tr>
                                <td><?php echo esc_html($record['id']); ?></td>
                                <td><?php echo esc_html($record['date']); ?></td>
                                <td><?php echo esc_html($record['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentFees($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Fee Status</h2>
        <div class="alert alert-info">Check your fee payment status.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Fee ID</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $fee): ?>
                            <tr>
                                <td><?php echo esc_html($fee['fee_id']); ?></td>
                                <td><?php echo esc_html($fee['amount']); ?></td>
                                <td><?php echo esc_html($fee['due_date']); ?></td>
                                <td><?php echo esc_html($fee['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No fee records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentNotices($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Notice Wall</h2>
        <div class="alert alert-info">Stay updated with the latest notices.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Notice ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $notice): ?>
                            <tr>
                                <td><?php echo esc_html($notice['id']); ?></td>
                                <td><?php echo esc_html($notice['title']); ?></td>
                                <td><?php echo esc_html($notice['date']); ?></td>
                                <td><?php echo esc_html($notice['content']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No notices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentLibrary($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Library</h2>
        <div class="alert alert-info">View your borrowed books.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $book): ?>
                            <tr>
                                <td><?php echo esc_html($book['book_id']); ?></td>
                                <td><?php echo esc_html($book['title']); ?></td>
                                <td><?php echo esc_html($book['author']); ?></td>
                                <td><?php echo esc_html($book['status']); ?></td>
                                <td><?php echo esc_html($book['due_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No books borrowed.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentInventoryTransactions($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Borrowings</h2>
        <div class="alert alert-info">View your inventory borrowing transactions.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Item Name</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $transaction): ?>
                            <tr>
                                <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                                <td><?php echo esc_html($transaction['item_name']); ?></td>
                                <td><?php echo esc_html($transaction['date']); ?></td>
                                <td><?php echo esc_html($transaction['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No borrowing transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render Student Chats
 * Updated to match superadmin chat structure with conversation sidebar and message area
 */
function demoRenderStudentChats($data) {
    // Hardcoded conversation data for student
    $conversations = [
        ['id' => 'CONV001', 'recipient_id' => 'T001', 'recipient_name' => 'Ms. Alice Johnson', 'last_message' => 'Can you clarify the homework?', 'last_message_time' => '2025-04-19 09:15', 'unread' => 1],
        ['id' => 'CONV002', 'recipient_id' => 'T002', 'recipient_name' => 'Mr. Bob Wilson', 'last_message' => 'Great job on the quiz!', 'last_message_time' => '2025-04-18 14:30', 'unread' => 0],
    ];

    // Hardcoded chats for the first conversation (CONV001)
    $chats = [
        ['sender' => 'S001', 'sender_name' => 'John Doe', 'content' => 'Can you clarify the homework?', 'time' => '2025-04-19 09:15', 'status' => 'sent'],
        ['sender' => 'T001', 'sender_name' => 'Ms. Alice Johnson', 'content' => 'Sure, it’s about chapter 5.', 'time' => '2025-04-19 09:20', 'status' => 'received'],
        ['sender' => 'S001', 'sender_name' => 'John Doe', 'content' => 'Thanks! I’ll review it.', 'time' => '2025-04-19 09:25', 'status' => 'sent'],
    ];

    ob_start();
    ?>
    <div class="dashboard-section chat-container" style="padding: 20px; display: flex; flex-direction: column; height: 100%;">
        <h2 style="color: #4a90e2;">Messages</h2>
        <div class="chat-wrapper" style="display: flex; flex: 1; overflow: hidden;">
            <!-- Sidebar -->
            <div class="chat-sidebar" style="width: 300px; border-right: 1px solid #ddd; overflow-y: auto;">
                <div class="sidebar-header" style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <h4>Conversations</h4>
                    <input type="text" id="conversation-search" class="form-control" placeholder="Search conversations..." style="width: 100%; padding: 8px; border: 1px solid #4a90e2; border-radius: 5px;">
                </div>
                <ul class="conversation-list" style="list-style: none; padding: 0;">
                    <?php foreach ($conversations as $index => $conv): ?>
                        <li class="conversation-item <?php echo $index === 0 ? 'active' : ''; ?>" data-conv-id="<?php echo esc_attr($conv['id']); ?>" style="padding: 10px; cursor: pointer; <?php echo $index === 0 ? 'background: #f4f6f9;' : ''; ?>">
                            <strong><?php echo esc_html($conv['recipient_name']); ?> (<?php echo esc_html($conv['recipient_id']); ?>)</strong>
                            <p style="margin: 5px 0; color: #555;"><?php echo esc_html($conv['last_message']); ?></p>
                            <span class="meta" style="font-size: 0.8em; color: #999;"><?php echo esc_html($conv['last_message_time']); ?></span>
                            <?php if ($conv['unread'] > 0): ?>
                                <span class="badge bg-primary" style="background: #4a90e2; color: #fff; padding: 2px 6px; border-radius: 10px;"><?php echo esc_html($conv['unread']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Main Chat Area -->
            <div class="chat-main" style="flex: 1; display: flex; flex-direction: column; padding: 10px;">
                <div class="chat-header" style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <h4>Conversation with <?php echo esc_html($conversations[0]['recipient_name']); ?> (<?php echo esc_html($conversations[0]['recipient_id']); ?>)</h4>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'chats', 'demo-action' => 'send-chat'])); ?>" class="btn btn-primary" style="background: #4a90e2; color: #fff; padding: 8px 15px; border-radius: 5px;">New Message</a>
                </div>
                <div class="chat-messages" id="chat-messages" style="flex: 1; overflow-y: auto; padding: 10px;">
                    <?php foreach ($chats as $msg): ?>
                        <div class="chat-message <?php echo $msg['status']; ?>" style="margin: 5px; padding: 10px; max-width: 70%; <?php echo $msg['status'] === 'sent' ? 'background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;' : 'background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;'; ?>">
                            <div class="bubble"><?php echo esc_html($msg['content']); ?></div>
                            <div class="meta" style="font-size: 0.8em; color: <?php echo $msg['status'] === 'sent' ? '#e8eaed' : '#555'; ?>;"><?php echo esc_html($msg['sender_name']); ?> • <?php echo esc_html($msg['time']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form class="chat-form" id="chat-form" style="padding: 10px; border-top: 1px solid #ddd;">
                    <div class="d-flex align-items-center">
                        <textarea class="form-control" id="chat-input" rows="2" placeholder="Type a message..." style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px; margin-right: 10px;"></textarea>
                        <button type="submit" class="btn btn-primary" style="background: #4a90e2; color: #fff; padding: 10px 15px; border-radius: 5px;"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
                <div class="chat-loading" id="chat-loading" style="display: none; text-align: center; padding: 10px;">Loading...</div>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            // Conversation search
            $('#conversation-search').on('input', function() {
                const query = $(this).val().toLowerCase();
                $('.conversation-item').each(function() {
                    const name = $(this).find('strong').text().toLowerCase();
                    const message = $(this).find('p').text().toLowerCase();
                    $(this).toggle(name.includes(query) || message.includes(query));
                });
            });

            // Conversation selection
            $('.conversation-item').on('click', function() {
                $('.conversation-item').removeClass('active');
                $(this).addClass('active');
                const convId = $(this).data('conv-id');
                $('#chat-messages').html('<div class="chat-loading active" style="text-align: center; padding: 10px;">Loading...</div>');
                // Simulate loading chats (hardcoded)
                setTimeout(() => {
                    $('#chat-messages').html(`
                        <div class="chat-message sent" style="margin: 5px; padding: 10px; max-width: 70%; background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;">
                            <div class="bubble">Can you clarify the homework?</div>
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">John Doe • 2025-04-19 09:15</div>
                        </div>
                        <div class="chat-message received" style="margin: 5px; padding: 10px; max-width: 70%; background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;">
                            <div class="bubble">Sure, it’s about chapter 5.</div>
                            <div class="meta" style="font-size: 0.8em; color: #555;">Ms. Alice Johnson • 2025-04-19 09:20</div>
                        </div>
                        <div class="chat-message sent" style="margin: 5px; padding: 10px; max-width: 70%; background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;">
                            <div class="bubble">Thanks! I’ll review it.</div>
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">John Doe • 2025-04-19 09:25</div>
                        </div>
                    `);
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                }, 500);
            });

            // Send chat
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                const message = $('#chat-input').val().trim();
                if (message) {
                    $('#chat-messages').append(`
                        <div class="chat-message sent" style="margin: 5px; padding: 10px; max-width: 70%; background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;">
                            <div class="bubble">${message}</div>
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">John Doe • ${new Date().toLocaleString()}</div>
                        </div>
                    `);
                    $('#chat-input').val('');
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render Student Send Chat
 * Updated to match superadmin new conversation structure
 */
function demoRenderStudentSendChat() {
    // Hardcoded recipient data for student (teachers only)
    $recipients = [
        'teachers' => [
            ['id' => 'T001', 'name' => 'Ms. Alice Johnson'],
            ['id' => 'T002', 'name' => 'Mr. Bob Wilson'],
        ],
    ];

    ob_start();
    ?>
    <div class="dashboard-section chat-container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Send New Message</h2>
        <form id="new-chat-form" class="edu-form" style="max-width: 600px;">
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="recipient" style="display: block; margin-bottom: 5px;">Recipient</label>
                <select id="recipient" class="edu-form-input" style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required>
                    <option value="">Select a teacher</option>
                    <optgroup label="Teachers">
                        <?php foreach ($recipients['teachers'] as $teacher): ?>
                            <option value="<?php echo esc_attr($teacher['id']); ?>">
                                <?php echo esc_html($teacher['name'] . ' (' . $teacher['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="subject" style="display: block; margin-bottom: 5px;">Subject</label>
                <input type="text" id="subject" class="edu-form-input" placeholder="e.g., Homework Clarification" style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required>
            </div>
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="chat" style="display: block; margin-bottom: 5px;">Message</label>
                <textarea id="chat" class="edu-form-input" rows="5" placeholder="Type your message..." style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required></textarea>
            </div>
            <div class="edu-form-actions" style="display: flex; gap: 10px;">
                <button type="submit" class="edu-button edu-button-primary" style="background: #4a90e2; color: #fff; padding: 10px 20px; border-radius: 5px; border: none;">Send Message</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'chats'])); ?>" class="edu-button edu-button-secondary" style="background: #6c757d; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message" style="margin-top: 10px;"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#new-chat-form').on('submit', function(e) {
                e.preventDefault();
                const recipient = $('#recipient').val();
                const subject = $('#subject').val().trim();
                const chat = $('#chat').val().trim();
                if (recipient && subject && chat) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Message sent successfully!').css('color', '#4a90e2');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'chats'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.').css('color', '#dc3545');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}


function demoRenderStudentReports($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Reports</h2>
        <div class="alert alert-info">View your performance reports.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Summary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $report): ?>
                            <tr>
                                <td><?php echo esc_html($report['report_id']); ?></td>
                                <td><?php echo esc_html($report['title']); ?></td>
                                <td><?php echo esc_html($report['date']); ?></td>
                                <td><?php echo esc_html($report['summary']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No reports found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentResults($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Grades</h2>
        <div class="alert alert-info">View your exam results.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Result ID</th>
                        <th>Exam</th>
                        <th>Score</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['result_id']); ?></td>
                                <td><?php echo esc_html($result['exam']); ?></td>
                                <td><?php echo esc_html($result['score']); ?></td>
                                <td><?php echo esc_html($result['grade']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderStudentTimetable($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Schedule</h2>
        <div class="alert alert-info">View your class timetable.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Timetable ID</th>
                        <th>Subject</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $entry): ?>
                            <tr>
                                <td><?php echo esc_html($entry['id']); ?></td>
                                <td><?php echo esc_html($entry['subject']); ?></td>
                                <td><?php echo esc_html($entry['day']); ?></td>
                                <td><?php echo esc_html($entry['time_slot']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No timetable entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderStudentHomework($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Assignments</h2>
        <div class="alert alert-info">View and submit your homework assignments.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'homework-view', 'demo-action' => 'submit-homework'])); ?>" class="btn btn-primary">Submit Assignment</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Homework ID</th>
                        <th>Subject</th>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $homework): ?>
                            <tr>
                                <td><?php echo esc_html($homework['id']); ?></td>
                                <td><?php echo esc_html($homework['subject']); ?></td>
                                <td><?php echo esc_html($homework['title']); ?></td>
                                <td><?php echo esc_html($homework['due_date']); ?></td>
                                <td><?php echo esc_html($homework['status']); ?></td>
                                <td>
                                    <?php if ($homework['status'] === 'Pending'): ?>
                                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'homework-view', 'demo-action' => 'submit-homework', 'id' => $homework['id']])); ?>" class="btn btn-sm btn-primary">Submit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No homework assignments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderStudentSubmitHomework() {
    ob_start();
    $homework_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Submit Assignment</h2>
        <div class="alert alert-info">Submit your homework for ID: <?php echo esc_html($homework_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="homework_id" class="form-label">Homework ID</label>
                <input type="text" class="form-control" id="homework_id" name="homework_id" value="<?php echo esc_html($homework_id); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="submission" class="form-label">Submission Content</label>
                <textarea class="form-control" id="submission" name="submission" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Assignment</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'homework-view'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderStudentProfile($data) {
    $student = [
        'student_id' => 'ST1001',
        'name' => 'John Doe',
        'email' => 'john@demo-pro.edu',
        'phone' => '123-456-7890',
        'class' => '1A',
        'roll_number' => '001',
        'admission_number' => 'AD1001'
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Profile</h2>
        <div class="card p-4">
            <p><strong>Student ID:</strong> <?php echo esc_html($student['student_id']); ?></p>
            <p><strong>Name:</strong> <?php echo esc_html($student['name']); ?></p>
            <p><strong>Email:</strong> <?php echo esc_html($student['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo esc_html($student['phone']); ?></p>
            <p><strong>Class:</strong> <?php echo esc_html($student['class']); ?></p>
            <p><strong>Roll Number:</strong> <?php echo esc_html($student['roll_number']); ?></p>
            <p><strong>Admission Number:</strong> <?php echo esc_html($student['admission_number']); ?></p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderStudentExams($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Exams</h2>
        <div class="alert alert-info">View your upcoming and past exams.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Class</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $exam): ?>
                            <tr>
                                <td><?php echo esc_html($exam['name']); ?></td>
                                <td><?php echo esc_html($exam['date']); ?></td>
                                <td><?php echo esc_html($exam['class']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No exams found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderStudentOverview() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Welcome to Your Dashboard</h2>
        <div class="alert alert-info">Access your academic information and stay updated with your school activities.</div>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user"></i> Profile</h5>
                        <p class="card-text">View your personal information.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'profile'])); ?>" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clipboard-list"></i> Attendance</h5>
                        <p class="card-text">Check your attendance records.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'attendance-view'])); ?>" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-wallet"></i> Fees</h5>
                        <p class="card-text">Monitor your fee payment status.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'fees-view'])); ?>" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tasks"></i> Assignments</h5>
                        <p class="card-text">Submit and track your homework.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'homework-view'])); ?>" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-pen-alt"></i> Exams</h5>
                        <p class="card-text">Check your exam schedule.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'exams'])); ?>" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
