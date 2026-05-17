/* ============================================================
   SIMM MAHASISWA - student.js
   Animasi & interaksi untuk semua halaman user mahasiswa.
   Mirip dengan sistem animasi user koordinator bidang.
   ============================================================ */

/* --------------------------------------------------
   TOAST NOTIFICATION (identik dengan koordinator)
   -------------------------------------------------- */
function showToast(message, type = 'info') {
    const oldToast = document.getElementById('toast-notif');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'toast-notif';

    const icons = { success: '✔', danger: '✖', info: 'ℹ', warning: '⚠' };
    toast.innerHTML = `<span style="font-size:16px;margin-right:8px">${icons[type] || icons.info}</span>${message}`;

    const colors = {
        success: { bg: '#28C76F', color: '#fff' },
        danger:  { bg: '#EA5455', color: '#fff' },
        warning: { bg: '#FF9F43', color: '#fff' },
        info:    { bg: '#1F3653', color: '#fff' }
    };
    const c = colors[type] || colors.info;

    Object.assign(toast.style, {
        position:     'fixed',
        bottom:       '30px',
        right:        '30px',
        background:   c.bg,
        color:        c.color,
        padding:      '14px 24px',
        borderRadius: '10px',
        fontSize:     '14px',
        fontWeight:   '600',
        boxShadow:    '0 6px 24px rgba(0,0,0,0.18)',
        zIndex:       '9999',
        fontFamily:   'Inter, sans-serif',
        display:      'flex',
        alignItems:   'center',
        opacity:      '0',
        transform:    'translateY(14px)',
        transition:   'opacity 0.25s, transform 0.25s'
    });

    document.body.appendChild(toast);

    // Animasi masuk
    requestAnimationFrame(() => {
        toast.style.opacity   = '1';
        toast.style.transform = 'translateY(0)';
    });

    // Auto hilang setelah 3 detik
    setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateY(14px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* --------------------------------------------------
   CARD ENTRANCE ANIMATION (staggered fade-in-up)
   -------------------------------------------------- */
function animateEntrance() {
    const targets = document.querySelectorAll(
        '.card, .welcome-card, .tahap-container, .member-card, .add-member-form'
    );

    targets.forEach((el, i) => {
        el.style.opacity   = '0';
        el.style.transform = 'translateY(18px)';
        el.style.transition = `opacity 0.35s ease ${i * 60}ms, transform 0.35s ease ${i * 60}ms`;

        requestAnimationFrame(() => {
            setTimeout(() => {
                el.style.opacity   = '1';
                el.style.transform = 'translateY(0)';
            }, 30);
        });
    });
}


/* --------------------------------------------------
   BUTTON RIPPLE EFFECT (identik rasa koordinator)
   -------------------------------------------------- */
function attachRipple() {
    document.querySelectorAll('.btn, .btn-dark, .btn-light-blue').forEach(btn => {
        if (btn.dataset.rippleAttached) return;
        btn.dataset.rippleAttached = 'true';
        btn.style.position = 'relative';
        btn.style.overflow = 'hidden';

        btn.addEventListener('click', function(e) {
            const rect   = btn.getBoundingClientRect();
            const size   = Math.max(rect.width, rect.height);
            const x      = e.clientX - rect.left - size / 2;
            const y      = e.clientY - rect.top  - size / 2;

            const ripple = document.createElement('span');
            Object.assign(ripple.style, {
                position:      'absolute',
                width:         size + 'px',
                height:        size + 'px',
                left:          x + 'px',
                top:           y + 'px',
                background:    'rgba(255,255,255,0.3)',
                borderRadius:  '50%',
                transform:     'scale(0)',
                animation:     'rippleAnim 0.55s linear',
                pointerEvents: 'none'
            });

            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 580);
        });
    });
}

/* --------------------------------------------------
   MEMBER CARD HOVER LIFT
   -------------------------------------------------- */
function attachMemberCardHover() {
    document.querySelectorAll('.member-card').forEach(card => {
        if (card.dataset.hoverAttached) return;
        card.dataset.hoverAttached = 'true';
        card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
        card.addEventListener('mouseenter', () => {
            card.style.transform  = 'translateY(-3px)';
            card.style.boxShadow  = '0 8px 24px rgba(28,51,77,0.25)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform  = 'translateY(0)';
            card.style.boxShadow  = '';
        });
    });
}

/* --------------------------------------------------
   CARD GENERIC HOVER LIFT (seperti dosen-rekap-card koordinator)
   -------------------------------------------------- */
function attachCardHover() {
    document.querySelectorAll('.card, .tahap-container:not(.locked)').forEach(card => {
        if (card.dataset.hoverAttached) return;
        card.dataset.hoverAttached = 'true';
        card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-2px)';
            card.style.boxShadow = '0 8px 20px rgba(0,0,0,0.09)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '';
        });
    });
}

/* --------------------------------------------------
   ACTIVE STEP CIRCLE PULSE (dashboard stepper)
   -------------------------------------------------- */
function animateActiveStepper() {
    const activeCircle = document.querySelector('.step.active .step-circle');
    if (!activeCircle) return;
    activeCircle.style.animation = 'stepPulse 2s ease-in-out infinite';
}

/* --------------------------------------------------
   FORM INPUT FOCUS GLOW
   -------------------------------------------------- */
function attachInputFocusGlow() {
    document.querySelectorAll('.form-group input, .form-group textarea').forEach(inp => {
        if (inp.dataset.glowAttached) return;
        inp.dataset.glowAttached = 'true';
        inp.addEventListener('focus', () => {
            inp.style.boxShadow = '0 0 0 3px rgba(0,163,255,0.18)';
            inp.style.transition = 'box-shadow 0.2s, border-color 0.2s';
        });
        inp.addEventListener('blur', () => {
            inp.style.boxShadow = '';
        });
    });
}



/* --------------------------------------------------
   INJECT CSS KEYFRAMES ke <head>
   -------------------------------------------------- */
function injectKeyframes() {
    if (document.getElementById('student-anim-keyframes')) return;
    const style = document.createElement('style');
    style.id = 'student-anim-keyframes';
    style.textContent = `
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes rippleAnim {
            to { transform: scale(3.5); opacity: 0; }
        }
        @keyframes stepPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(135,206,235,0.5); }
            50%       { box-shadow: 0 0 0 10px rgba(135,206,235,0); }
        }
        /* Sidebar logo - no animation */
        .logo-container {
            /* animation dihapus */
        }

        /* Btn global scale on hover */
        .btn:hover, .btn-dark:hover, .btn-light-blue:hover {
            transform: translateY(-1px) scale(1.02);
            transition: transform 0.18s ease, opacity 0.18s ease;
        }

        /* Tab content fade */
        .tab-content.active {
            animation: fadeInUp 0.28s ease;
        }

        /* Member section title - no animation */
        .member-section-title {
            /* animation dihapus */
        }

        /* Tahap container transition */
        .tahap-container {
            transition: box-shadow 0.2s, transform 0.2s;
        }

        /* Table row hover */
        .table tbody tr {
            transition: background 0.18s;
        }

        /* Badge warning subtle pulse */
        @keyframes badgeWarn {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.7; }
        }
        .badge-warning {
            animation: badgeWarn 2.5s ease-in-out infinite;
        }
    `;
    document.head.appendChild(style);
}

/* --------------------------------------------------
   INIT: jalankan semua saat DOM siap
   -------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    injectKeyframes();
    animateEntrance();
    animateActiveStepper();
    attachRipple();
    attachMemberCardHover();
    attachCardHover();
    attachInputFocusGlow();
});
