<script>
    // Theme Controller Logic
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.querySelector('.theme-controller');

        // 1. Load saved theme
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        let initialTheme = 'light'; // Default from app.css

        if (savedTheme) {
            initialTheme = savedTheme;
        } else if (systemPrefersDark) {
            initialTheme = 'dark';
        }

        // 2. Apply theme
        if (initialTheme === 'dark') {
            themeToggle.checked = true;
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            themeToggle.checked = false;
            document.documentElement.setAttribute('data-theme', 'light');
        }

        // 3. Listen for changes
        themeToggle.addEventListener('change', (e) => {
            const newTheme = e.target.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    });
</script>