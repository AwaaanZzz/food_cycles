import './bootstrap';
import Alpine from 'alpinejs';

// Global Alpine Store for FoodCycle App State
Alpine.store('app', {
    mobileMenuOpen: false,
    openDemo: false,
    showPrefsModal: false,
    theme: 'system',
    isDark: false,

    init() {
        const stored = localStorage.getItem('theme');
        this.theme = (stored === 'dark' || stored === 'light') ? stored : 'system';
        this.applyTheme(this.theme);

        // Listen for OS system theme changes
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        if (mediaQuery.addEventListener) {
            mediaQuery.addEventListener('change', () => {
                if (this.theme === 'system') {
                    this.applyTheme('system');
                }
            });
        }

        // Cross-tab synchronization
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                this.theme = e.newValue || 'system';
                this.applyTheme(this.theme);
            }
        });
    },

    setTheme(val) {
        this.theme = val;
        if (val === 'system') {
            localStorage.removeItem('theme');
            localStorage.removeItem('color-scheme');
        } else {
            localStorage.setItem('theme', val);
            localStorage.setItem('color-scheme', val);
        }
        this.applyTheme(val);
    },

    toggleTheme() {
        const next = this.isDark ? 'light' : 'dark';
        this.setTheme(next);
    },

    applyTheme(theme) {
        const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const activeDark = theme === 'dark' || (theme === 'system' && isSystemDark);
        
        if (activeDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        this.isDark = activeDark;

        const meta = document.querySelector('meta[name="color-scheme"]');
        if (meta) {
            meta.content = theme === 'system' ? 'light dark' : theme;
        }
    },

    openPreferences() {
        this.showPrefsModal = true;
    },
    closePreferences() {
        this.showPrefsModal = false;
    },
    openTutorial() {
        this.openDemo = true;
    },
    closeTutorial() {
        this.openDemo = false;
    }
});

// Window helper functions for immediate onclick access
window.toggleDarkMode = function() {
    if (window.Alpine && window.Alpine.store('app')) {
        window.Alpine.store('app').toggleTheme();
    }
};

window.setThemeMode = function(mode) {
    if (window.Alpine && window.Alpine.store('app')) {
        window.Alpine.store('app').setTheme(mode);
    }
};

window.openPreferences = function() {
    if (window.Alpine && window.Alpine.store('app')) {
        window.Alpine.store('app').showPrefsModal = true;
    }
};

window.closePreferences = function() {
    if (window.Alpine && window.Alpine.store('app')) {
        window.Alpine.store('app').showPrefsModal = false;
    }
};

window.openTutorial = function() {
    if (window.Alpine && window.Alpine.store('app')) {
        window.Alpine.store('app').openDemo = true;
    }
};

window.closeTutorial = function() {
    if (window.Alpine && window.Alpine.store('app')) {
        window.Alpine.store('app').openDemo = false;
    }
};

window.Alpine = Alpine;
Alpine.start();

