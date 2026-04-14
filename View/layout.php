<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Nutrition - FitTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-glass: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.1);
            --primary: #38bdf8;
            --primary-hover: #0284c7;
            --accent: #f43f5e;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --error: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(244, 63, 94, 0.15) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 1.5rem;
            text-align: center;
            background: var(--card-glass);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--card-border);
            margin-bottom: 2rem;
        }

        header h1 {
            font-size: 2rem;
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .navbar {
            margin-top: 1rem;
            display: flex; justify-content: center; gap: 2rem;
        }
        .navbar a {
            color: var(--text-main);
            text-decoration: none; font-weight: 600; transition: color 0.3s;
        }
        .navbar a:hover { color: var(--primary); }
        .navbar a.admin-link { color: var(--accent); opacity: 0.8; }
        .navbar a.admin-link:hover { opacity: 1; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; flex: 1; width: 100%; }

        .glass-card {
            background: var(--card-glass); backdrop-filter: blur(12px); border: 1px solid var(--card-border);
            border-radius: 16px; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn {
            display: inline-block; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600;
            text-decoration: none; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3);
        }
        
        .btn-outline {
            background: transparent; border: 1px solid var(--primary); color: var(--primary); box-shadow: none;
        }

        input, textarea {
            width: 100%; padding: 0.75rem; margin-bottom: 0.5rem; background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border); border-radius: 8px; color: var(--text-main); font-family: inherit;
        }

        label { display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.9rem; }

        .error-message {
            color: var(--error); font-size: 0.85rem; margin-bottom: 1rem; display: none;
        }
        .input-error { border-color: var(--error) !important; }

        .animate-fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>
    <header>
        <h1>Smart Nutrition</h1>
        <div class="navbar">
            <a href="index.php?action=home">Accueil</a>
            <a href="index.php?action=activites">Nos Activités</a>
            <a href="index.php?action=admin_index" class="admin-link">🛡️ BackOffice Admin</a>
        </div>
    </header>

    <main class="container">
        <?php echo $content; ?>
    </main>

    <footer style="text-align: center; padding: 2rem; color: var(--text-muted); margin-top: auto;">
        <p>&copy; <?php echo date('Y'); ?> Smart Nutrition. Projet Web 2A.</p>
    </footer>

    <!-- Script de validation côté client Vanilla JS pure (Sans HTML5 validations) -->
    <script src="public/js/validation.js"></script>
</body>
</html>
