<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Welcome', ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f6fa; color: #333; }
        .container { max-width: 640px; margin: 0 auto; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h1 { margin-bottom: 16px; font-size: 2rem; }
        p { margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars($title ?? 'Welcome', ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>This is the starter scaffold for the Zestta Club Pass platform. Build features inside <code>application/</code> and your assets inside <code>public/</code>.</p>
</div>
</body>
</html>
