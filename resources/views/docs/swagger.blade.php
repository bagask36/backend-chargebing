<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChargeBing REST API - Swagger UI</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    
    <!-- Swagger UI CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .top-navbar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #10b981;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .top-navbar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
        }

        .top-navbar .brand span.badge {
            background: #10b981;
            color: #0f172a;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 9999px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .top-navbar .links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-left: 16px;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .top-navbar .links a:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.1);
        }

        .top-navbar .links a.btn-dashboard {
            background: #10b981;
            color: #ffffff;
        }

        .top-navbar .links a.btn-dashboard:hover {
            background: #059669;
        }

        #swagger-ui {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Swagger theme tweaks */
        .swagger-ui .topbar {
            display: none;
        }

        .swagger-ui .info {
            margin: 25px 0;
        }

        .swagger-ui .info .title {
            color: #0f172a;
            font-weight: 800;
        }

        .swagger-ui .scheme-container {
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .swagger-ui .btn.authorize {
            background-color: #10b981;
            border-color: #10b981;
            color: #ffffff;
        }

        .swagger-ui .btn.authorize svg {
            fill: #ffffff;
        }
    </style>
</head>
<body>
    <div class="top-navbar">
        <a href="/" class="brand">
            <span>⚡ ChargeBing</span>
            <span class="badge">OpenAPI 3.0</span>
        </a>
        <div class="links">
            <a href="/docs/api/openapi.json" target="_blank" download="chargebing-openapi.json">📥 Download OpenAPI JSON</a>
            <a href="/" class="btn-dashboard">← Ke Web Admin</a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <!-- Swagger UI JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js" crossorigin></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js" crossorigin></script>
    
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/docs/api/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                persistAuthorization: true,
                displayRequestDuration: true,
                docExpansion: 'list',
                filter: true,
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 2,
            });
        };
    </script>
</body>
</html>

