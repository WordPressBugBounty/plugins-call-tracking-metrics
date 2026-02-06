// Main tab switcher using style guide colors
const mainTabButtons = document.querySelectorAll('.main-doc-tab');
const mainTabPanels = document.querySelectorAll('.main-doc-tab-panel');

// Style guide color classes
const ACTIVE_TAB_CLASSES = [
    'text-primary-900',      // strong foreground
    'border-primary-500',    // primary border
    'font-semibold',
    'bg-primary-50'          // subtle background
];
const INACTIVE_TAB_CLASSES = [
    'text-neutral-500',      // muted/secondary text
    'border-transparent',
    'bg-transparent'
];

mainTabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        mainTabButtons.forEach(b => {
            b.classList.remove(...ACTIVE_TAB_CLASSES);
            b.classList.add(...INACTIVE_TAB_CLASSES);
        });
        this.classList.add(...ACTIVE_TAB_CLASSES);
        this.classList.remove(...INACTIVE_TAB_CLASSES);
        const tab = this.getAttribute('data-tab');
        mainTabPanels.forEach(panel => {
            if (panel.getAttribute('data-tab') === tab) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
        // Reset subtabs to first for each main tab
        if(tab === 'debug') {
            showSubtab('debug', 'system-info');
        } else if(tab === 'api') {
            showSubtab('api', 'overview');
        } else if(tab === 'cti') {
            showSubtab('cti', 'overview');
        }
    });
});

// Subtab switcher using style guide colors
function showSubtab(group, subtab) {
    const SUBTAB_ACTIVE = [
        'text-primary-900',
        'border-primary-500',
        'font-semibold',
        'bg-primary-50'
    ];
    const SUBTAB_INACTIVE = [
        'text-neutral-500',
        'border-transparent',
        'bg-transparent'
    ];
    document.querySelectorAll('.' + group + '-subtab').forEach(btn => {
        if(btn.getAttribute('data-subtab') === subtab) {
            btn.classList.add(...SUBTAB_ACTIVE);
            btn.classList.remove(...SUBTAB_INACTIVE);
        } else {
            btn.classList.remove(...SUBTAB_ACTIVE);
            btn.classList.add(...SUBTAB_INACTIVE);
        }
    });
    document.querySelectorAll('.' + group + '-subtab-panel').forEach(panel => {
        if(panel.getAttribute('data-subtab') === subtab) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    });
}

document.querySelectorAll('.debug-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('debug', this.getAttribute('data-subtab'));
    });
});
document.querySelectorAll('.api-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('api', this.getAttribute('data-subtab'));
    });
});
document.querySelectorAll('.cti-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('cti', this.getAttribute('data-subtab'));
    });
});

// Default to General tab and first subtab for each group
if(mainTabPanels.length) mainTabPanels[0].classList.remove('hidden');
// Activate the first main tab button with style guide colors
if(mainTabButtons.length) {
    mainTabButtons.forEach(b => {
        b.classList.remove(...ACTIVE_TAB_CLASSES, ...INACTIVE_TAB_CLASSES);
        b.classList.add(...INACTIVE_TAB_CLASSES);
    });
    mainTabButtons[0].classList.add(...ACTIVE_TAB_CLASSES);
    mainTabButtons[0].classList.remove(...INACTIVE_TAB_CLASSES);
}

// Initialize all first subtabs as active when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set first subtab for each group as active
    const debugSubtabs = document.querySelectorAll('.debug-subtab');
    const apiSubtabs = document.querySelectorAll('.api-subtab');
    const ctiSubtabs = document.querySelectorAll('.cti-subtab');

    const SUBTAB_ACTIVE = [
        'text-primary-900',
        'border-primary-500',
        'font-semibold',
        'bg-primary-50'
    ];
    const SUBTAB_INACTIVE = [
        'text-neutral-500',
        'border-transparent',
        'bg-transparent'
    ];

    if(debugSubtabs.length > 0) {
        debugSubtabs[0].classList.add(...SUBTAB_ACTIVE);
        debugSubtabs[0].classList.remove(...SUBTAB_INACTIVE);
    }
    if(apiSubtabs.length > 0) {
        apiSubtabs[0].classList.add(...SUBTAB_ACTIVE);
        apiSubtabs[0].classList.remove(...SUBTAB_INACTIVE);
    }
    if(ctiSubtabs.length > 0) {
        ctiSubtabs[0].classList.add(...SUBTAB_ACTIVE);
        ctiSubtabs[0].classList.remove(...SUBTAB_INACTIVE);
    }
});

showSubtab('debug', 'system-info');
showSubtab('api', 'overview');
showSubtab('cti', 'overview'); 