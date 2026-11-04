<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>System Expired</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #e2e8f0;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .card {
            width: min(460px, calc(100% - 40px));
            padding: 40px;
            border-radius: 20px;
            background: #111827;
            border: 1px solid #334155;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
        }

        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
        }

        p {
            margin: 0;
            line-height: 1.7;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="icon">🔒</div>

    <h1>System Expired</h1>

    <p>
        The system security key is missing, invalid, or has been changed.
        Please contact the system administrator.
    </p>

</div>

</body>
</html>