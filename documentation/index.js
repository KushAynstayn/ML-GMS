/**
 * ML-GMS Documentation - Interactive Scripts
 * Features: sidebar accordion, scroll tracking, mobile toggle, search, copy code
 */

(function () {
    'use strict';

    // ==================== DOM REFERENCES ====================
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const nav = document.getElementById('sidebar-nav');
    const searchInput = document.getElementById('doc-search');

    // ==================== SIDEBAR ACCORDION ====================
    const sectionBtns = nav.querySelectorAll('.nav-section-btn');

    sectionBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const subsection = btn.nextElementSibling;

            // Close all other subsections
            nav.querySelectorAll('.nav-subsection').forEach(function (sub) {
                if (sub !== subsection) {
                    sub.classList.remove('open');
                    sub.previousElementSibling.classList.remove('open');
                }
            });

            // Toggle current
            const isOpen = subsection.classList.contains('open');
            if (isOpen) {
                subsection.classList.remove('open');
                btn.classList.remove('open');
            } else {
                subsection.classList.add('open');
                btn.classList.add('open');
            }
        });
    });

    // Open all sections by default on desktop
    function openAllSections() {
        if (window.innerWidth >= 1024) {
            nav.querySelectorAll('.nav-subsection').forEach(function (sub) {
                sub.classList.add('open');
                sub.previousElementSibling.classList.add('open');
            });
        }
    }
    openAllSections();

    // ==================== ACTIVE SECTION TRACKING ====================
    const allSectionAnchors = document.querySelectorAll('.doc-section[id], h3[id]');
    const allNavLinks = nav.querySelectorAll('.nav-subsection a');

    function updateActiveLink() {
        let currentId = '';
        const scrollPos = window.scrollY + 120;

        allSectionAnchors.forEach(function (el) {
            if (el.offsetTop <= scrollPos) {
                currentId = el.getAttribute('id');
            }
        });

        allNavLinks.forEach(function (link) {
            link.classList.remove('nav-active');
            if (link.getAttribute('href') === '#' + currentId) {
                link.classList.add('nav-active');
                // Expand parent section if not already open
                const sub = link.closest('.nav-subsection');
                if (sub && !sub.classList.contains('open')) {
                    sub.classList.add('open');
                    sub.previousElementSibling.classList.add('open');
                }
            }
        });
    }

    window.addEventListener('scroll', updateActiveLink, { passive: true });
    updateActiveLink();

    // ==================== SMOOTH SCROLL FOR ALL ANCHOR LINKS ====================
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
                // Close mobile sidebar after navigation
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            }
        });
    });

    // ==================== MOBILE SIDEBAR TOGGLE ====================
    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Close sidebar on Esc key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // ==================== COPY CODE BUTTONS ====================
    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const codeBlock = btn.closest('.code-block');
            const code = codeBlock.querySelector('code');
            const text = code.textContent;

            navigator.clipboard.writeText(text).then(function () {
                btn.classList.add('copied');
                const originalTitle = btn.getAttribute('title');
                btn.setAttribute('title', 'Copied!');

                setTimeout(function () {
                    btn.classList.remove('copied');
                    btn.setAttribute('title', originalTitle);
                }, 2000);
            }).catch(function () {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    btn.classList.add('copied');
                    setTimeout(function () {
                        btn.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.warn('Copy failed:', err);
                }
                document.body.removeChild(textarea);
            });
        });
    });

    // ==================== SEARCH FILTER ====================
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const links = nav.querySelectorAll('.nav-subsection a');
            const sections = nav.querySelectorAll('.nav-section');

            if (query.length === 0) {
                // Reset: show all
                links.forEach(function (link) {
                    link.style.display = '';
                    const highlight = link.querySelector('.search-highlight');
                    if (highlight) {
                        highlight.outerHTML = highlight.textContent;
                    }
                });
                sections.forEach(function (s) { s.style.display = ''; });
                return;
            }

            sections.forEach(function (section) {
                const sectionLinks = section.querySelectorAll('.nav-subsection a');
                let hasVisible = false;

                sectionLinks.forEach(function (link) {
                    const text = link.textContent.toLowerCase();
                    if (text.includes(query)) {
                        link.style.display = '';
                        // Highlight match
                        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        const regex = new RegExp('(' + escaped + ')', 'gi');
                        link.innerHTML = link.textContent.replace(regex, '<span class="search-highlight">$1</span>');
                        hasVisible = true;
                    } else {
                        link.style.display = 'none';
                    }
                });

                section.style.display = hasVisible ? '' : 'none';

                // Auto-expand sections with visible results
                if (hasVisible) {
                    const sub = section.querySelector('.nav-subsection');
                    const btn = section.querySelector('.nav-section-btn');
                    if (sub) sub.classList.add('open');
                    if (btn) btn.classList.add('open');
                }
            });
        });

        // Clear search on Escape
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
                this.blur();
            }
        });
    }

    // ==================== RESPONSIVE: HANDLE RESIZE ====================
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (window.innerWidth >= 1024) {
                closeSidebar();
                openAllSections();
            }
            updateActiveLink();
        }, 150);
    });

    // ==================== KEYBOARD SHORTCUT: Ctrl+K / Cmd+K for search ====================
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });

})();