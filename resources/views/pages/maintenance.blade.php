<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Em manutenção | Revista Negócios Pet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,700,900&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #ed258f;
            --ink: #111827;
            --muted: #6b7280;
            --wash: #fdf2f8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            background: #f9fafb;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }
        .card {
            width: 100%;
            max-width: 36rem;
            background: #fff;
            border: 1px solid #f3f4f6;
            border-radius: 2rem;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.06);
        }
        .brand {
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            font-style: italic;
            text-transform: uppercase;
        }
        .brand span { color: var(--brand); }
        .eyebrow {
            margin: 1.75rem 0 0;
            font-size: 0.65rem;
            font-weight: 900;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--brand);
        }
        h1 {
            margin: 0.6rem 0 0;
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: 900;
            font-style: italic;
            text-transform: uppercase;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        h1 em {
            font-style: italic;
            color: var(--brand);
        }
        p {
            margin: 1.25rem auto 0;
            max-width: 28rem;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
            font-weight: 500;
        }
        .soon {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.85rem 1.4rem;
            border-radius: 999px;
            background: var(--wash);
            color: var(--brand);
            font-size: 0.7rem;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">RN<span>Pet</span></div>
        <p class="eyebrow">Revista Negócios Pet</p>
        <h1>Site em <em>manutenção</em></h1>
        <p>{{ $message }}</p>
        <div class="soon">Voltaremos em breve</div>
    </main>
</body>
</html>
