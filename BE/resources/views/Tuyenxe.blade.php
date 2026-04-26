<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuyen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f4efe8;
            --card: #fff9f2;
            --ink: #1f1a17;
            --muted: #76655b;
            --line: #d8c6b6;
            --accent: #cc4f1f;
            --ok: #26734d;
            --err: #a71d31;
            --chip-get: #2067c8;
            --chip-post: #1f8a5b;
            --chip-put: #9c6a00;
            --chip-delete: #bb2f2f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 0% 0%, #f7c89a 0%, rgba(247, 200, 154, 0) 35%),
                radial-gradient(circle at 100% 0%, #b7d9cd 0%, rgba(183, 217, 205, 0) 30%),
                linear-gradient(140deg, #f4efe8 0%, #efe5d8 100%);
            min-height: 100vh;
        }

        .page {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px;
        }

        .hero {
            border: 1px solid var(--line);
            background: var(--card);
            border-radius: 20px;
            padding: 18px 22px;
            box-shadow: 0 10px 20px rgba(33, 22, 16, 0.08);
            margin-bottom: 16px;
        }

        .hero h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: -0.02em;
        }

        .hero p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 16px;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--card);
            box-shadow: 0 10px 20px rgba(33, 22, 16, 0.08);
            overflow: hidden;
        }

        .panel-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.55);
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .endpoint-list {
            max-height: calc(100vh - 180px);
            overflow: auto;
            padding: 10px;
        }

        .endpoint-item {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            background: #fff;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .endpoint-item:hover {
            transform: translateY(-1px);
            border-color: var(--accent);
        }

        .endpoint-top {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }

        .chip {
            color: #fff;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .chip.GET {
            background: var(--chip-get);
        }

        .chip.POST {
            background: var(--chip-post);
        }

        .chip.PUT,
        .chip.PATCH {
            background: var(--chip-put);
        }

        .chip.DELETE {
            background: var(--chip-delete);
        }

        .endpoint-uri {
            font-family: Consolas, monospace;
            font-size: 12px;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .endpoint-meta {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.35;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .tester {
            padding: 16px;
        }

        .grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        label {
            font-size: 13px;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 10px;
            padding: 10px;
            font-size: 14px;
            font-family: inherit;
            color: var(--ink);
        }

        textarea {
            min-height: 130px;
            resize: vertical;
            font-family: Consolas, monospace;
            font-size: 13px;
            line-height: 1.45;
        }

        .actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-send {
            background: var(--accent);
            color: #fff;
        }

        .btn-reset {
            background: #e4d7cb;
            color: var(--ink);
        }

        .status {
            margin-top: 12px;
            font-size: 13px;
            font-weight: 600;
            min-height: 20px;
        }

        .status.ok {
            color: var(--ok);
        }

        .status.err {
            color: var(--err);
        }

        pre {
            margin: 12px 0 0;
            background: #1f1f24;
            color: #d6e6ff;
            border-radius: 12px;
            padding: 14px;
            overflow: auto;
            min-height: 170px;
            max-height: 420px;
            border: 1px solid #34363f;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 1040px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .endpoint-list {
                max-height: 300px;
            }
        }

        @media (max-width: 640px) {
            .page {
                padding: 14px;
            }

            .hero h1 {
                font-size: 22px;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .tester {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <section class="hero">
            <h1>API Docs + Test Console</h1>
            <p>Dùng như Postman mini trên web: chọn endpoint, sửa JSON body/header, gửi request và xem phản hồi ngay.
            </p>
        </section>

        <section class="layout">
            <aside class="panel">
                <div class="panel-head">Danh Sách Endpoint /api/admin</div>
                <div class="endpoint-list" id="endpointList"></div>
            </aside>

            <main class="panel">
                <div class="panel-head">Trình Test API</div>
                <div class="tester">
                    <div class="grid">
                        <label for="baseUrl">Base URL</label>
                        <input id="baseUrl" type="text" value="{{ $baseUrl }}">
                    </div>
                    <div class="grid">
                        <label for="method">Method</label>
                        <select id="method">
                            <option>GET</option>
                            <option>POST</option>
                            <option>PUT</option>
                            <option>PATCH</option>
                            <option>DELETE</option>
                        </select>
                    </div>
                    <div class="grid">
                        <label for="path">Path</label>
                        <input id="path" type="text" placeholder="/api/admin/...">
                    </div>
                    <div class="grid">
                        <label for="token">Bearer Token</label>
                        <input id="token" type="text" placeholder="Dán token nếu endpoint có auth:sanctum">
                    </div>
                    <div class="grid">
                        <label for="headers">Headers (JSON)</label>
                        <textarea id="headers">{
  "Accept": "application/json"
}</textarea>
                    </div>
                    <div class="grid">
                        <label for="body">Body (JSON)</label>
                        <textarea id="body">{
}</textarea>
                    </div>

                    <div class="actions">
                        <button class="btn-send" id="sendBtn" type="button">Gửi Request</button>
                        <button class="btn-reset" id="resetBtn" type="button">Reset Form</button>
                    </div>

                    <div class="status" id="statusLine"></div>
                    <pre id="result">Chọn endpoint bên trái hoặc nhập path thủ công để bắt đầu.</pre>
                </div>
            </main>
        </section>
    </div>

    <script>
        const apiRoutes = @json($apiRoutes);

        const endpointList = document.getElementById('endpointList');
        const baseUrl = document.getElementById('baseUrl');
        const method = document.getElementById('method');
        const path = document.getElementById('path');
        const token = document.getElementById('token');
        const headers = document.getElementById('headers');
        const body = document.getElementById('body');
        const statusLine = document.getElementById('statusLine');
        const result = document.getElementById('result');

        function renderRouteList() {
            endpointList.innerHTML = '';

            apiRoutes.forEach((route) => {
                const item = document.createElement('div');
                item.className = 'endpoint-item';

                const top = document.createElement('div');
                top.className = 'endpoint-top';

                route.methods.forEach((m) => {
                    const chip = document.createElement('span');
                    chip.className = `chip ${m}`;
                    chip.textContent = m;
                    top.appendChild(chip);
                });

                const uri = document.createElement('div');
                uri.className = 'endpoint-uri';
                uri.textContent = route.uri;

                const meta = document.createElement('div');
                meta.className = 'endpoint-meta';
                meta.textContent = `${route.name || 'khong_dat_ten'} | ${route.action}`;

                item.appendChild(top);
                item.appendChild(uri);
                item.appendChild(meta);

                item.addEventListener('click', () => {
                    method.value = route.methods[0] || 'GET';
                    path.value = route.uri;
                    statusLine.textContent = `Đã chọn ${route.methods.join('/')} ${route.uri}`;
                    statusLine.className = 'status';
                });

                endpointList.appendChild(item);
            });
        }

        function parseJson(input, fallback = null) {
            if (!input || !input.trim()) {
                return fallback;
            }

            return JSON.parse(input);
        }

        async function sendRequest() {
            statusLine.className = 'status';
            statusLine.textContent = 'Đang gửi request...';
            result.textContent = '';

            const startedAt = performance.now();

            try {
                const requestHeaders = parseJson(headers.value, {});
                if (!requestHeaders['Accept']) {
                    requestHeaders['Accept'] = 'application/json';
                }

                const tokenValue = token.value.trim();
                if (tokenValue) {
                    requestHeaders['Authorization'] = `Bearer ${tokenValue}`;
                }

                const requestMethod = method.value.toUpperCase();
                const hasBody = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(requestMethod);
                let requestBody;

                if (hasBody) {
                    const bodyObject = parseJson(body.value, {});
                    requestHeaders['Content-Type'] = 'application/json';
                    requestBody = JSON.stringify(bodyObject);
                }

                const url = `${baseUrl.value.replace(/\/$/, '')}${path.value}`;
                const response = await fetch(url, {
                    method: requestMethod,
                    headers: requestHeaders,
                    body: hasBody ? requestBody : undefined,
                });

                const elapsed = Math.round(performance.now() - startedAt);
                const raw = await response.text();
                let pretty = raw;

                try {
                    const data = JSON.parse(raw);
                    pretty = JSON.stringify(data, null, 2);
                } catch (e) {
                    // Keep raw text if not JSON.
                }

                statusLine.textContent = `${response.status} ${response.statusText} - ${elapsed}ms`;
                statusLine.className = response.status === 200 ? 'status ok' : 'status err';
                result.textContent = pretty || '(Không có dữ liệu trả về)';
            } catch (error) {
                statusLine.textContent = 'Gửi request thất bại';
                statusLine.className = 'status err';
                result.textContent = error.message;
            }
        }

        function resetForm() {
            method.value = 'GET';
            path.value = '';
            token.value = '';
            headers.value = '{\n  "Accept": "application/json"\n}';
            body.value = '{\n}';
            statusLine.className = 'status';
            statusLine.textContent = '';
            result.textContent = 'Form đã reset. Chọn endpoint để bắt đầu test.';
        }

        document.getElementById('sendBtn').addEventListener('click', sendRequest);
        document.getElementById('resetBtn').addEventListener('click', resetForm);

        renderRouteList();
    </script>
</body>

</html>
