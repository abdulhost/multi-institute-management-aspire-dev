<?php
// Parent Dashboard Functions
function demoRenderParentContent($section, $action, $data = []) {
    ob_start();

    switch ($section) {
        case 'dashboard':
            echo render_parent_dashboard();
            break;
        case 'child-attendance':
            echo render_parent_attendance_view();
            break;
        case 'family-fees':
            echo render_parent_fees_view();
            break;
        case 'parent-notices':
            echo render_parent_notices_view();
            break;
        case 'child-library':
            echo render_parent_library_view();
            break;
        case 'child-borrowings':
            echo render_parent_inventory_transactions_view();
            break;
        case 'parent-chats':
            echo render_parent_chats();
            break;
        case 'child-reports':
            echo render_parent_reports_view();
            break;
        case 'child-grades':
            echo render_parent_results_view();
            break;
        case 'child-schedule':
            echo render_parent_timetable_view();
            break;
        case 'child-assignments':
            echo render_parent_homework_view();
            break;
        case 'child-exams':
            echo render_parent_exams_view();
            break;
        case 'student-report':
            echo render_parent_student_report_view();
            break;
        case 'progress-tracker':
            echo render_parent_progress_tracker();
            break;
        case 'meetings':
            echo render_parent_meetings_view();
            break;
        default:
            echo render_parent_dashboard(); // fallback to dashboard
    }

    return ob_get_clean();
}

// Render Parent Dashboard
function render_parent_dashboard() {
    ob_start();
    ?>
    <div class="dashboard-container" style="padding: 20px; background: #f4f6f9;">
        <h2 style="color: #4a90e2; margin-bottom: 20px;">Parent Dashboard</h2>
        <div class="card-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="card" style="border-left: 4px solid #4a90e2;">
                <div class="card-header">Child’s Attendance</div>
                <div class="card-body">View your child's attendance records.</div>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'child-attendance'])); ?>" class="btn" style="background: #4a90e2; color: #fff;">View</a>
            </div>
            <div class="card" style="border-left: 4px solid #4a90e2;">
                <div class="card-header">Family Fee Portal</div>
                <div class="card-body">Check and manage fee payments.</div>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'family-fees'])); ?>" class="btn" style="background: #4a90e2; color: #fff;">View</a>
            </div>
            <div class="card" style="border-left: 4px solid #4a90e2;">
                <div class="card-header">Parent Notices</div>
                <div class="card-body">Stay updated with school announcements.</div>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'parent-notices'])); ?>" class="btn" style="background: #4a90e2; color: #fff;">View</a>
            </div>
            <div class="card" style="border-left: 4px solid #4a90e2;">
                <div class="card-header">Parent-Teacher Meetings</div>
                <div class="card-body">Schedule meetings with teachers.</div>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'meetings'])); ?>" class="btn" style="background: #4a90e2; color: #fff;">Schedule</a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Attendance View (Read)
function render_parent_attendance_view() {
    $attendance_data = [
        ['date' => '2025-04-01', 'status' => 'Present', 'remarks' => 'On time'],
        ['date' => '2025-04-02', 'status' => 'Absent', 'remarks' => 'Sick leave'],
        ['date' => '2025-04-03', 'status' => 'Present', 'remarks' => 'Participated in class'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Attendance</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_data as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                        <td><?php echo esc_html($record['remarks']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Family Fee Portal (Read, Update)
function render_parent_fees_view() {
    $fees_data = [
        ['id' => 1, 'month' => 'April 2025', 'amount' => 500, 'status' => 'Paid'],
        ['id' => 2, 'month' => 'May 2025', 'amount' => 500, 'status' => 'Pending'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Family Fee Portal</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fees_data as $fee): ?>
                    <tr>
                        <td><?php echo esc_html($fee['month']); ?></td>
                        <td><?php echo esc_html($fee['amount']); ?></td>
                        <td><?php echo esc_html($fee['status']); ?></td>
                        <td>
                            <?php if ($fee['status'] === 'Pending'): ?>
                                <a href="<?php echo esc_url(add_query_arg(['action' => 'pay_fee', 'id' => $fee['id']])); ?>" class="btn" style="background: #4a90e2; color: #fff;">Pay Now</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Parent Notices View (Read)
function render_parent_notices_view() {
    $notices_data = [
        ['title' => 'School Closure', 'date' => '2025-04-20', 'content' => 'School will be closed on April 25 due to a holiday.'],
        ['title' => 'Parent Meeting', 'date' => '2025-04-22', 'content' => 'Join us for a parent-teacher meeting on April 30.'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Parent Notices</h2>
        <div class="notice-list" style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($notices_data as $notice): ?>
                <div class="card" style="border-left: 4px solid #4a90e2;">
                    <div class="card-header"><?php echo esc_html($notice['title']); ?> (<?php echo esc_html($notice['date']); ?>)</div>
                    <div class="card-body"><?php echo esc_html($notice['content']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Library View (Read)
function render_parent_library_view() {
    $library_data = [
        ['book' => 'Math Basics', 'due_date' => '2025-04-25', 'status' => 'Borrowed'],
        ['book' => 'Science Wonders', 'due_date' => '2025-04-30', 'status' => 'Borrowed'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Library</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Book</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library_data as $book): ?>
                    <tr>
                        <td><?php echo esc_html($book['book']); ?></td>
                        <td><?php echo esc_html($book['due_date']); ?></td>
                        <td><?php echo esc_html($book['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Borrowings View (Read)
function render_parent_inventory_transactions_view() {
    $transactions_data = [
        ['item' => 'Lab Kit', 'borrow_date' => '2025-04-10', 'return_date' => '2025-04-20'],
        ['item' => 'Sports Gear', 'borrow_date' => '2025-04-15', 'return_date' => ''],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Borrowings</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Item</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions_data as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['item']); ?></td>
                        <td><?php echo esc_html($transaction['borrow_date']); ?></td>
                        <td><?php echo esc_html($transaction['return_date'] ?: 'Not Returned'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render Parent Chats
 * Updated to match superadmin chat structure with conversation sidebar and message area
 */
function render_parent_chats() {
    // Hardcoded conversation data for parent
    $conversations = [
        ['id' => 'CONV001', 'recipient_id' => 'T001', 'recipient_name' => 'Ms. Alice Johnson', 'last_message' => 'Your child did well in the math quiz!', 'last_message_time' => '2025-04-19 10:00', 'unread' => 1],
        ['id' => 'CONV002', 'recipient_id' => 'T002', 'recipient_name' => 'Mr. Bob Wilson', 'last_message' => 'Can we schedule a meeting?', 'last_message_time' => '2025-04-18 15:45', 'unread' => 0],
    ];

    // Hardcoded chats for the first conversation (CONV001)
    $chats = [
        ['sender' => 'T001', 'sender_name' => 'Ms. Alice Johnson', 'content' => 'Your child did well in the math quiz!', 'time' => '2025-04-19 10:00', 'status' => 'received'],
        ['sender' => 'P001', 'sender_name' => 'Mary Brown', 'content' => 'Thank you for the update!', 'time' => '2025-04-19 10:05', 'status' => 'sent'],
        ['sender' => 'T001', 'sender_name' => 'Ms. Alice Johnson', 'content' => 'You’re welcome! Let me know if you need more details.', 'time' => '2025-04-19 10:10', 'status' => 'received'],
    ];

    ob_start();
    ?>
    <div class="container chat-container" style="padding: 20px; display: flex; flex-direction: column; height: 100%;">
        <h2 style="color: #4a90e2;">Parent Chats</h2>
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
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'parent-chats', 'demo-action' => 'send-chat'])); ?>" class="btn btn-primary" style="background: #4a90e2; color: #fff; padding: 8px 15px; border-radius: 5px;">New Message</a>
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
                        <div class="chat-message received" style="margin: 5px; padding: 10px; max-width: 70%; background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;">
                            <div class="bubble">Your child did well in the math quiz!</div>
                            <div class="meta" style="font-size: 0.8em; color: #555;">Ms. Alice Johnson • 2025-04-19 10:00</div>
                        </div>
                        <div class="chat-message sent" style="margin: 5px; padding: 10px; max-width: 70%; background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;">
                            <div class="bubble">Thank you for the update!</div>
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">Mary Brown • 2025-04-19 10:05</div>
                        </div>
                        <div class="chat-message received" style="margin: 5px; padding: 10px; max-width: 70%; background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;">
                            <div class="bubble">You’re welcome! Let me know if you need more details.</div>
                            <div class="meta" style="font-size: 0.8em; color: #555;">Ms. Alice Johnson • 2025-04-19 10:10</div>
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
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">Mary Brown • ${new Date().toLocaleString()}</div>
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
 * Render Parent Send Chat
 * New function to match superadmin new conversation structure
 */
function render_parent_send_chat() {
    // Hardcoded recipient data for parent (teachers only)
    $recipients = [
        'teachers' => [
            ['id' => 'T001', 'name' => 'Ms. Alice Johnson'],
            ['id' => 'T002', 'name' => 'Mr. Bob Wilson'],
        ],
    ];

    ob_start();
    ?>
    <div class="container chat-container" style="padding: 20px;">
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
                <input type="text" id="subject" class="edu-form-input" placeholder="e.g., Child’s Progress Discussion" style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required>
            </div>
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="chat" style="display: block; margin-bottom: 5px;">Message</label>
                <textarea id="chat" class="edu-form-input" rows="5" placeholder="Type your message..." style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required></textarea>
            </div>
            <div class="edu-form-actions" style="display: flex; gap: 10px;">
                <button type="submit" class="edu-button edu-button-primary" style="background: #4a90e2; color: #fff; padding: 10px 20px; border-radius: 5px; border: none;">Send Message</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'parent-chats'])); ?>" class="edu-button edu-button-secondary" style="background: #6c757d; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Cancel</a>
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
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'parent-chats'])); ?>';
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


// Child’s Reports View (Read)
function render_parent_reports_view() {
    $reports_data = [
        ['subject' => 'Math', 'score' => 85, 'comments' => 'Good progress'],
        ['subject' => 'Science', 'score' => 78, 'comments' => 'Needs focus on experiments'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Reports</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports_data as $report): ?>
                    <tr>
                        <td><?php echo esc_html($report['subject']); ?></td>
                        <td><?php echo esc_html($report['score']); ?></td>
                        <td><?php echo esc_html($report['comments']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Grades View (Read)
function render_parent_results_view() {
    $results_data = [
        ['exam' => 'Midterm', 'subject' => 'Math', 'grade' => 'A'],
        ['exam' => 'Midterm', 'subject' => 'Science', 'grade' => 'B+'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Grades</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Exam</th>
                    <th>Subject</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results_data as $result): ?>
                    <tr>
                        <td><?php echo esc_html($result['exam']); ?></td>
                        <td><?php echo esc_html($result['subject']); ?></td>
                        <td><?php echo esc_html($result['grade']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Schedule View (Read)
function render_parent_timetable_view() {
    $timetable_data = [
        ['day' => 'Monday', 'time' => '09:00-10:00', 'subject' => 'Math'],
        ['day' => 'Monday', 'time' => '10:00-11:00', 'subject' => 'Science'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Schedule</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Day</th>
                    <th>Time</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timetable_data as $slot): ?>
                    <tr>
                        <td><?php echo esc_html($slot['day']); ?></td>
                        <td><?php echo esc_html($slot['time']); ?></td>
                        <td><?php echo esc_html($slot['subject']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Assignments View (Read)
function render_parent_homework_view() {
    $homework_data = [
        ['title' => 'Math Worksheet', 'due_date' => '2025-04-25', 'status' => 'Submitted'],
        ['title' => 'Science Project', 'due_date' => '2025-04-30', 'status' => 'Pending'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Assignments</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Title</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homework_data as $assignment): ?>
                    <tr>
                        <td><?php echo esc_html($assignment['title']); ?></td>
                        <td><?php echo esc_html($assignment['due_date']); ?></td>
                        <td><?php echo esc_html($assignment['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Child’s Exams View (Read)
function render_parent_exams_view() {
    $exams_data = [
        ['subject' => 'Math', 'date' => '2025-05-01', 'type' => 'Midterm'],
        ['subject' => 'Science', 'date' => '2025-05-02', 'type' => 'Midterm'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Child’s Exams</h2>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams_data as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['subject']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['type']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// Student Report View (Read)
function render_parent_student_report_view() {
    $report_data = [
        ['category' => 'Academic', 'details' => '85% average, strong in Math', 'date' => '2025-04-19'],
        ['category' => 'Behavior', 'details' => 'Respectful, needs to participate more', 'date' => '2025-04-19'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Student Report</h2>
        <div class="report-list" style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($report_data as $report): ?>
                <div class="card" style="border-left: 4px solid #4a90e2;">
                    <div class="card-header"><?php echo esc_html($report['category']); ?> (<?php echo esc_html($report['date']); ?>)</div>
                    <div class="card-body"><?php echo esc_html($report['details']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Progress Tracker (Read)
function render_parent_progress_tracker() {
    $progress_data = [
        ['subject' => 'Math', 'progress' => 90, 'last_updated' => '2025-04-19'],
        ['subject' => 'Science', 'progress' => 80, 'last_updated' => '2025-04-19'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Progress Tracker</h2>
        <div class="progress-list" style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($progress_data as $progress): ?>
                <div class="card" style="border-left: 4px solid #4a90e2;">
                    <div class="card-header"><?php echo esc_html($progress['subject']); ?></div>
                    <div class="card-body">
                        <div style="background: #e8eaed; height: 20px; border-radius: 5px;">
                            <div style="width: <?php echo esc_html($progress['progress']); ?>%; background: #4a90e2; height: 100%; border-radius: 5px;"></div>
                        </div>
                        <p>Progress: <?php echo esc_html($progress['progress']); ?>% (Updated: <?php echo esc_html($progress['last_updated']); ?>)</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Parent-Teacher Meetings (Read, Create)
function render_parent_meetings_view() {
    $meetings_data = [
        ['teacher' => 'Ms. Smith', 'date' => '2025-04-30', 'time' => '14:00', 'status' => 'Scheduled'],
        ['teacher' => 'Mr. Jones', 'date' => '2025-05-01', 'time' => '15:00', 'status' => 'Pending'],
    ];
    ob_start();
    ?>
    <div class="container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Parent-Teacher Meetings</h2>
        <button class="btn" style="background: #4a90e2; color: #fff; margin-bottom: 10px;">Schedule New Meeting</button>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #4a90e2; color: #fff;">
                    <th>Teacher</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meetings_data as $meeting): ?>
                    <tr>
                        <td><?php echo esc_html($meeting['teacher']); ?></td>
                        <td><?php echo esc_html($meeting['date']); ?></td>
                        <td><?php echo esc_html($meeting['time']); ?></td>
                        <td><?php echo esc_html($meeting['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}


