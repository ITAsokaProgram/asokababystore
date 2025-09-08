<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Activity Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .activity-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 24px;
            transition: all 0.3s ease;
        }

        .activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .header-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .icon-container {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .icon-container i {
            color: white;
            font-size: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            padding: 6px 12px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 20px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-item {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.4));
            padding: 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.6));
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .activity-list::-webkit-scrollbar {
            width: 6px;
        }

        .activity-list::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .activity-list::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 8px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(4px);
        }

        .activity-item.login {
            border-left-color: #22c55e;
        }

        .activity-item.page-view {
            border-left-color: #3b82f6;
        }

        .activity-item.transaction {
            border-left-color: #f59e0b;
        }

        .activity-item.logout {
            border-left-color: #ef4444;
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
        }

        .activity-icon.login {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .activity-icon.page-view {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .activity-icon.transaction {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .activity-icon.logout {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .activity-details {
            flex: 1;
        }

        .activity-user {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .activity-action {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .activity-time {
            font-size: 11px;
            color: #9ca3af;
            white-space: nowrap;
        }

        .page-traffic {
            margin-top: 16px;
        }

        .page-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .page-item:last-child {
            border-bottom: none;
        }

        .page-name {
            font-weight: 500;
            color: #374151;
            flex: 1;
        }

        .page-views {
            font-weight: 600;
            color: #667eea;
            margin-left: 12px;
        }

        .progress-bar {
            width: 60px;
            height: 4px;
            background: rgba(102, 126, 234, 0.2);
            border-radius: 2px;
            margin-left: 12px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            transition: width 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="activity-card">
        <div class="header-section">
            <div class="icon-container">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="title">Member Activity & Traffic</h3>
            <div class="live-indicator">
                <div class="live-dot"></div>
                <span style="font-size: 12px; color: #22c55e; font-weight: 500;">LIVE</span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="onlineCount">247</div>
                <div class="stat-label">Online Now</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="todayVisits">1,842</div>
                <div class="stat-label">Today's Visits</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="avgSession">12m</div>
                <div class="stat-label">Avg Session</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4 style="font-weight: 600; color: #374151; margin-bottom: 12px;">Recent Activities</h4>
                <div class="activity-list" id="activityList">
                    <!-- Activities will be populated by JavaScript -->
                </div>
            </div>

            <div>
                <h4 style="font-weight: 600; color: #374151; margin-bottom: 12px;">Page Traffic</h4>
                <div class="page-traffic" id="pageTraffic">
                    <!-- Page traffic will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const evtSource = new EventSource("/src/api/stream/data_visitor.php");

        evtSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);

                // stats
                document.getElementById('todayVisits').textContent =
                    (data.today ?? 0).toLocaleString();

                document.getElementById('onlineCount').textContent =
                    data.online ?? 0;

                document.getElementById('avgSession').textContent =
                    (data.avg ?? 0) + " users";

                // page traffic
                const container = document.getElementById('pageTraffic');
                container.innerHTML = '';
                (data.pages ?? []).forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'page-item';
                    div.innerHTML = `
                <div class="page-name">${p.page}</div>
                <div class="page-views">${p.views}</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:${Math.min(p.views,100)}%"></div>
                </div>
            `;
                    container.appendChild(div);
                });

                // recent activities
                const actContainer = document.getElementById('activityList');
                actContainer.innerHTML = '';
                (data.activities ?? []).forEach(act => {

                    const div = document.createElement('div');
                    div.className = 'activity-item page-view';
                    div.innerHTML = `
                <div class="activity-icon page-view">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="activity-details">
                    <div class="activity-user">${act.nama}</div>
                    <div class="activity-action">Mengunjungi: ${act.page}</div>
                </div>
                <div class="activity-time">${act.time}</div>
            `;
                    actContainer.appendChild(div);
                });
            } catch (e) {
                console.error("Failed parse SSE:", e, event.data);
            }
        };

        evtSource.onerror = function(err) {
            console.error("SSE error:", err);
        };

    </script>
</body>

</html>