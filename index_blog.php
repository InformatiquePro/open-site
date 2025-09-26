<?php
// Cherles-Elie Software -- logiciel OpenSource, profitez !
// --- PARTIE API POUR LE CHARGEMENT DYNAMIQUE ---
// Si la page est appelée avec le paramètre 'action=load_archives',
// on ne retourne que les données des articles au format JSON.

if (isset($_GET['action']) && $_GET['action'] === 'load_archives') {
    header('Content-Type: application/json');

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    
    $articlesPerPage = 5; // On charge 5 articles à la fois
    $offset = ($page - 1) * $articlesPerPage;

    $archiveFiles = glob('archive/s-*.txt');
    $articles = [];

    if ($archiveFiles) {
        // Tri des fichiers par date, du plus récent au plus ancien
        usort($archiveFiles, function($a, $b) {
            // Regex mise à jour pour _JJ-MM-AAAA.txt
            preg_match('/_(\d{2}-\d{2}-\d{4})\.txt$/', $a, $matchesA);
            preg_match('/_(\d{2}-\d{2}-\d{4})\.txt$/', $b, $matchesB);

            // Format de date mis à jour pour d-m-Y
            $dateA = isset($matchesA[1]) ? DateTime::createFromFormat('d-m-Y', $matchesA[1]) : false;
            $dateB = isset($matchesB[1]) ? DateTime::createFromFormat('d-m-Y', $matchesB[1]) : false;

            if (!$dateA || !$dateB) return 0;
            
            // On inverse la comparaison pour trier du plus récent au plus ancien
            return $dateB <=> $dateA; 
        });

        // On sélectionne la bonne "page" d'articles
        $filesToLoad = array_slice($archiveFiles, $offset, $articlesPerPage);

        foreach ($filesToLoad as $file) {
            $content = file_get_contents($file);
            // Regex mise à jour pour capturer le titre et la date avec le nouveau format
            preg_match('/^s-(.*?)_(\d{2}-\d{2}-\d{4})\.txt$/', basename($file), $titleMatches);
            preg_match('/sub=([\s\S]*?)(?=\nart=|$)/', $content, $subMatches);
            preg_match('/art=([\s\S]*)/', $content, $artMatches);

            $articles[] = [
                'title' => isset($titleMatches[1]) ? str_replace('-', ' ', $titleMatches[1]) : 'Titre inconnu',
                'sub' => isset($subMatches[1]) ? trim($subMatches[1]) : '',
                'art' => isset($artMatches[1]) ? trim($artMatches[1]) : ''
            ];
        }
    }
    
    echo json_encode($articles);
    exit; // On arrête le script ici pour ne pas envoyer le HTML
}

// --- PARTIE AFFICHAGE DE LA PAGE NORMALE ---

// Fonction pour parser les fichiers du dossier "front"
function getFrontProjects() {
    $frontFiles = glob('front/[0-9]*-*.txt');
    $projects = [];
    if ($frontFiles) {
        sort($frontFiles, SORT_NATURAL); // Trie par ordre numérique (1, 2, 3...)
        
        foreach (array_slice($frontFiles, 0, 3) as $file) { // On prend les 3 premiers max
            $content = file_get_contents($file);
            preg_match('/^[0-9]+-(.*)\.txt$/', basename($file), $titleMatches);
            preg_match('/sub=([\s\S]*?)(?=\nart=|$)/', $content, $subMatches);
            preg_match('/art=([\s\S]*)/', $content, $artMatches);

            $projects[] = [
                'title' => isset($titleMatches[1]) ? str_replace('-', ' ', $titleMatches[1]) : 'Titre inconnu',
                'sub' => isset($subMatches[1]) ? trim($subMatches[1]) : '',
                'art' => isset($artMatches[1]) ? trim($artMatches[1]) : ''
            ];
        }
    }
    return $projects;
}

$frontProjects = getFrontProjects();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio de Charles-Elie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/2.1.0/showdown.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-grad-1: #1a2a6c; --bg-grad-2: #b21f1f; --bg-grad-3: #fdbb2d;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-grad-1), var(--bg-grad-2), var(--bg-grad-3));
            background-attachment: fixed;
            transition: background 0.5s ease-in-out;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            border-radius: 1rem; border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .markdown-content h1, .markdown-content h2, .markdown-content h3 { font-weight: bold; margin-top: 1em; margin-bottom: 0.5em; }
        .markdown-content h1 { font-size: 1.5em; } .markdown-content h2 { font-size: 1.25em; } .markdown-content h3 { font-size: 1.1em; }
        .markdown-content p { margin-bottom: 1em; } .markdown-content ul { list-style-type: disc; margin-left: 1.5em; margin-bottom: 1em; }
        .markdown-content ol { list-style-type: decimal; margin-left: 1.5em; margin-bottom: 1em; }
        .markdown-content a { color: #fdbb2d; text-decoration: underline; }
        .markdown-content code { background-color: rgba(0, 0, 0, 0.2); padding: 0.2em 0.4em; border-radius: 0.3em; font-family: monospace; }
        .markdown-content pre { background-color: rgba(0, 0, 0, 0.3); padding: 1em; border-radius: 0.5em; overflow-x: auto; }
        #load-more-btn:disabled { cursor: not-allowed; opacity: 0.6; }
    </style>
</head>
<body class="text-white min-h-screen flex flex-col items-center p-4 sm:p-8">

    <a href="https://ipro.frstud.fr/wait" target="_blank" rel="noopener noreferrer" class="fixed top-4 right-4 z-50 p-3 glass-card hover:bg-white/20 transition-colors font-semibold text-sm rounded-lg">
        Se connecter
    </a>
    <div id="theme-btn-wrapper" class="fixed top-2 left-2 z-50 p-2 group">
        <button id="theme-toggle-btn" class="p-3 glass-card hover:bg-white/20 transition-opacity duration-300 opacity-50 group-hover:opacity-100 focus:opacity-100" aria-label="Personnaliser le thème">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0L12 2.69z"></path><path d="M12 22.31V12"></path></svg>
        </button>
    </div>
    <div id="theme-panel" class="fixed top-0 left-0 h-full w-64 md:w-80 glass-card p-6 z-40 transform -translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Personnaliser</h3>
            <button id="close-panel-btn" class="p-2 -mr-2" aria-label="Fermer le panneau">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div id="theme-options" class="space-y-2"></div>
    </div>

    <main class="w-full max-w-4xl mx-auto">
        <header class="glass-card text-center p-8 mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Charles-Elie</h1>
            <p class="text-lg md:text-xl text-gray-200">Passionné par la création, l'électronique et le code, avec une affection particulière pour Python et l'univers Arduino.</p>
        </header>

        <section>
            <h2 class="text-3xl font-bold text-center mb-8">Mes Projets à la Une</h2>
            <div id="front-projects-container" class="grid grid-cols-1 gap-8">
            </div>
            
            <h2 id="archive-title" class="text-3xl font-bold text-center my-8 pt-8 border-t border-white/20 hidden">Archives</h2>
            <div id="archive-projects-container" class="grid grid-cols-1 gap-8">
            </div>

            <div class="text-center mt-12">
                <button id="load-more-btn" class="p-4 px-8 glass-card hover:bg-white/20 transition-colors font-semibold rounded-lg">
                    En voir plus
                </button>
            </div>
        </section>
    </main>

    <footer class="w-full max-w-4xl mx-auto text-center text-gray-300 mt-12 py-4">
        <p>&copy; Charles-Elie Software - Pour me contacter : <a href="mailto:ce@ipro.frstud.fr" class="hover:text-yellow-400 transition-colors">ce@ipro.frstud.fr</a> Site héberger par FranceStudent. Version : 1.2-prod1-site-principal</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const frontProjectsContainer = document.getElementById('front-projects-container');
            const archiveProjectsContainer = document.getElementById('archive-projects-container');
            const loadMoreBtn = document.getElementById('load-more-btn');
            const archiveTitle = document.getElementById('archive-title');
            const converter = new showdown.Converter();
            let currentPage = 1;

            const frontProjects = <?php echo json_encode($frontProjects); ?>;

            function createProjectCard(project) {
                const subHtml = converter.makeHtml(project.sub || '');
                const artHtml = converter.makeHtml(project.art || '');
                
                const card = document.createElement('div');
                card.className = 'glass-card p-6 md:p-8';
                card.innerHTML = `
                    <h3 class="text-2xl font-bold mb-3">${project.title}</h3>
                    <div class="text-gray-200 markdown-content mb-4">${subHtml}</div>
                    <div class="article-content hidden markdown-content border-t border-gray-500 pt-4 mt-4">${artHtml}</div>
                    <button class="toggle-article-btn font-semibold text-yellow-400 hover:text-yellow-300 transition-colors mt-2">Lire la suite</button>
                `;
                 card.querySelector('.toggle-article-btn').addEventListener('click', (e) => {
                    const articleContent = e.target.previousElementSibling;
                    const isHidden = articleContent.classList.contains('hidden');
                    articleContent.classList.toggle('hidden');
                    e.target.textContent = isHidden ? "Réduire l'article" : 'Lire la suite';
                });
                return card;
            }

            // Afficher les projets "front"
            if (frontProjects.length > 0) {
                frontProjects.forEach(proj => frontProjectsContainer.appendChild(createProjectCard(proj)));
            } else {
                frontProjectsContainer.innerHTML = `<div class="glass-card text-center p-8"><p class="text-gray-300">Aucun projet à la une pour le moment.</p></div>`;
            }

            // Logique du bouton "En voir plus"
            loadMoreBtn.addEventListener('click', async () => {
                loadMoreBtn.textContent = 'Chargement...';
                loadMoreBtn.disabled = true;

                try {
                    const response = await fetch(`index.php?action=load_archives&page=${currentPage}`);
                    const newProjects = await response.json();

                    if (newProjects.length > 0) {
                        archiveTitle.classList.remove('hidden');
                        newProjects.forEach(proj => archiveProjectsContainer.appendChild(createProjectCard(proj)));
                        currentPage++;
                        loadMoreBtn.textContent = 'En voir plus';
                    } else {
                        loadMoreBtn.textContent = 'Tous les articles ont été chargés';
                        // On ne désactive pas le bouton pour garder le message
                        setTimeout(() => loadMoreBtn.style.display = 'none', 2000);
                    }
                } catch (error) {
                    console.error("Erreur lors du chargement des articles:", error);
                    loadMoreBtn.textContent = 'Erreur de chargement';
                } finally {
                     if (loadMoreBtn.textContent !== 'Tous les articles ont été chargés') {
                        loadMoreBtn.disabled = false;
                    }
                }
            });

            // --- GESTION DES THÈMES (inchangé) ---
            const themeBtnWrapper = document.getElementById('theme-btn-wrapper');
            const themeToggleBtn = document.getElementById('theme-toggle-btn');
            const themePanel = document.getElementById('theme-panel');
            const closePanelBtn = document.getElementById('close-panel-btn');
            const themeOptionsContainer = document.getElementById('theme-options');
            const themes = [
                { name: 'Coucher de Soleil', colors: ['#1a2a6c', '#b21f1f', '#fdbb2d'] },
                { name: 'Océan Profond', colors: ['#002244', '#00529B', '#0077CC'] },
                { name: 'Forêt Mystique', colors: ['#0f2027', '#203a43', '#2c5364'] },
                { name: 'Néon Nocturne', colors: ['#3a1c71', '#d76d77', '#ffaf7b'] },
                { name: 'Acier Brossé', colors: ['#232526', '#414345', '#65686b'] },
                { name: 'Émeraude Royale', colors: ['#00467F', '#A5CC82', '#009E60'] }
            ];
            function applyTheme(theme) {
                const root = document.documentElement;
                root.style.setProperty('--bg-grad-1', theme.colors[0]);
                root.style.setProperty('--bg-grad-2', theme.colors[1]);
                root.style.setProperty('--bg-grad-3', theme.colors[2]);
                localStorage.setItem('portfolioTheme', theme.name);
            }
            themes.forEach(theme => {
                const button = document.createElement('button');
                button.className = 'w-full text-left p-2 rounded-lg hover:bg-white/20 transition-colors flex items-center';
                button.innerHTML = `<span class="w-5 h-5 rounded-full mr-3 border border-white/50" style="background: linear-gradient(135deg, ${theme.colors.join(', ')})"></span>${theme.name}`;
                button.onclick = () => { applyTheme(theme); themePanel.classList.add('-translate-x-full'); themeBtnWrapper.classList.remove('hidden'); };
                themeOptionsContainer.appendChild(button);
            });
            themeToggleBtn.addEventListener('click', () => { themePanel.classList.remove('-translate-x-full'); themeBtnWrapper.classList.add('hidden'); });
            closePanelBtn.addEventListener('click', () => { themePanel.classList.add('-translate-x-full'); themeBtnWrapper.classList.remove('hidden'); });
            const savedThemeName = localStorage.getItem('portfolioTheme');
            const savedTheme = themes.find(t => t.name === savedThemeName);
            const defaultTheme = themes.find(t => t.name === 'Océan Profond') || themes[0];
            applyTheme(savedTheme || defaultTheme);
        });
    </script>
</body>
</html>

