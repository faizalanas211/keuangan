@extends('layouts.auth')
@section('content')

<style>
    /* ===== VARIABLES & THEME ===== */
    :root {
        --emerald-50: #ecfdf5;
        --emerald-100: #d1fae5;
        --emerald-200: #a7f3d0;
        --emerald-300: #6ee7b7;
        --emerald-400: #34d399;
        --emerald-500: #10b981;
        --emerald-600: #059669;
        --emerald-700: #047857;
        --emerald-800: #065f46;
        --emerald-900: #064e3b;
        --gold-500: #d4af37;
        --gold-600: #b8941f;
        --cream: #fdf6e3;
        --charcoal: #1a1a1a;
    }

    /* ===== BASE STYLES ===== */
    body {
        background: linear-gradient(145deg, var(--emerald-900), var(--charcoal));
        min-height: 100vh;
        margin: 0;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        position: relative;
        overflow-x: hidden;
    }

    /* ===== DECORATIVE BACKGROUND ===== */
    .geometric-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 0;
        overflow: hidden;
    }

    .triangle {
        position: absolute;
        border-style: solid;
        opacity: 0.03;
    }

    .triangle-1 {
        width: 0;
        height: 0;
        border-width: 0 200px 346px 200px;
        border-color: transparent transparent var(--emerald-400) transparent;
        top: -100px;
        left: -100px;
        transform: rotate(45deg);
    }

    .triangle-2 {
        width: 0;
        height: 0;
        border-width: 0 150px 260px 150px;
        border-color: transparent transparent var(--emerald-300) transparent;
        bottom: -100px;
        right: -80px;
        transform: rotate(135deg);
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        opacity: 0.02;
    }

    .circle-1 {
        width: 300px;
        height: 300px;
        background: var(--emerald-300);
        top: 50%;
        left: 10%;
    }

    .circle-2 {
        width: 200px;
        height: 200px;
        background: var(--emerald-400);
        bottom: 20%;
        right: 15%;
    }

    /* ===== MAIN CONTAINER ===== */
    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        z-index: 1;
    }

    /* ===== ELEGANT CARD ===== */
    .elegant-card .row {
        min-height: 100%;
    }

    .elegant-card .col-lg-6 {
        display: flex;
    }

    .elegant-left,
    .elegant-right {
        flex: 1;
    }

    .elegant-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(20px);
        border-radius: 32px;
        box-shadow: 
            0 20px 60px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        overflow: hidden;
        width: 100%;
        max-width: 1200px;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* ===== LEFT PANEL - ELEGANT ===== */
    .elegant-left {
        background: linear-gradient(145deg, var(--emerald-800), var(--emerald-900));
        color: white;
        padding: 4rem 3rem;
        position: relative;
        overflow: hidden;
    }

    .elegant-left::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 10% 20%, rgba(212, 175, 55, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
    }

    .prestige-badge {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: rgba(212, 175, 55, 0.15);
        color: var(--gold-500);
        padding: 0.5rem 1.25rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        border: 1px solid rgba(212, 175, 55, 0.2);
        backdrop-filter: blur(10px);
    }

    .logo-container {
        margin-bottom: 3rem;
    }

    .logo-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600));
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        position: relative;
        overflow: hidden;
    }

    .logo-icon::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transform: translateX(-100%);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        100% {
            transform: translateX(100%);
        }
    }

    .logo-icon i {
        font-size: 2rem;
        color: white;
        z-index: 1;
    }

    .brand-name {
        font-family: 'Playfair Display', serif;
        font-size: 2.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #fff, var(--emerald-200));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .brand-tagline {
        font-size: 1.1rem;
        opacity: 0.85;
        margin-bottom: 3rem;
        line-height: 1.6;
        font-weight: 300;
        max-width: 400px;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .feature-icon i {
        color: var(--emerald-300);
        font-size: 1.25rem;
    }

    .feature-content h5 {
        color: #6cbf98;
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .feature-content p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.8;
        line-height: 1.5;
    }

    .security-note {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .security-note p {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .security-note i {
        color: var(--emerald-300);
        font-size: 1.25rem;
    }

    /* ===== RIGHT PANEL - ELEGANT ===== */
    .elegant-right {
        padding: 4rem 3rem;
        background: var(--cream);
    }

    .login-header {
        margin-bottom: 3rem;
        text-align: center;
    }

    .login-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--emerald-800);
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .login-header p {
        color: var(--emerald-600);
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .form-container {
        max-width: 400px;
        margin: 0 auto;
    }

    /* ===== ELEGANT FORM ===== */
    .form-group {
        margin-bottom: 2rem;
    }

    .input-label {
        display: block;
        color: var(--emerald-800);
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .elegant-input {
        width: 100%;
        padding: 1rem 1.25rem;
        background: white;
        border: 2px solid rgba(6, 95, 70, 0.1);
        border-radius: 12px;
        font-size: 1rem;
        color: var(--charcoal);
        transition: all 0.3s ease;
        outline: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .elegant-input:focus {
        border-color: var(--emerald-500);
        box-shadow: 
            0 0 0 4px rgba(16, 185, 129, 0.1),
            0 10px 15px -3px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .input-with-icon {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--emerald-500);
        font-size: 1.25rem;
        pointer-events: none;
    }

    .input-with-icon .elegant-input {
        padding-left: 3.5rem;
    }

    .password-toggle {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--emerald-500);
        cursor: pointer;
        font-size: 1.25rem;
        padding: 0;
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: var(--emerald-700);
    }

    /* ===== ELEGANT BUTTON ===== */
    .elegant-btn {
        width: 100%;
        padding: 1.25rem;
        background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        text-transform: uppercase;
    }

    .elegant-btn:hover {
        transform: translateY(-2px);
        box-shadow: 
            0 15px 30px rgba(16, 185, 129, 0.3),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        background: linear-gradient(135deg, var(--emerald-700), var(--emerald-800));
    }

    .elegant-btn:active {
        transform: translateY(0);
    }

    .elegant-btn i {
        margin-right: 0.75rem;
    }

    /* ===== FLASH MESSAGES ===== */
    .elegant-alert {
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        border: none;
        animation: slideDown 0.4s ease-out;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600));
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
    }

    .alert-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    /* ===== FOOTER ===== */
    .login-footer {
        margin-top: 1rem;
        text-align: center;
        padding-top: 2rem;
        border-top: 1px solid rgba(6, 95, 70, 0.1);
    }

    .copyright {
        font-size: 0.875rem;
        color: var(--emerald-600);
        opacity: 0.8;
    }

    .version {
        display: block;
        font-size: 0.75rem;
        color: var(--emerald-500);
        margin-top: 0.5rem;
        letter-spacing: 0.5px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .elegant-card {
            border-radius: 24px;
            margin: 1rem;
        }
        
        .elegant-left,
        .elegant-right {
            padding: 3rem 2rem;
        }
        
        .brand-name {
            font-size: 2.25rem;
        }
    }

    @media (max-width: 768px) {
        .elegant-left {
            display: none;
        }
        
        .elegant-right {
            padding: 3rem 2rem;
        }
    }
</style>

<!-- Geometric Background -->
<div class="geometric-bg">
    <div class="triangle triangle-1"></div>
    <div class="triangle triangle-2"></div>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
</div>

<div class="auth-wrapper">
    <div class="elegant-card">
        <div class="row g-0">
            
            <!-- LEFT PANEL - ELEGANT -->
            <div class="col-lg-6">
                <div class="elegant-left">
                    
                    <div class="logo-container">
                        <div class="logo-icon">
                            <i class="bx bx-wallet"></i>
                        </div>
                        <h1 class="brand-name">
                            Sistem Distribusi<br>
                            Slip Gaji</h1>
                        <p class="brand-tagline">
                            Aplikasi yang membantu pegawai melihat dan mencetak slip gaji secara mandiri dan lebih privat.
                        </p>
                    </div>
                    
                    <div class="feature-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-user-check"></i>
                            </div>
                            <div class="feature-content">
                                <h5>Akses Mandiri</h5>
                                <p>Pegawai dapat login menggunakan akun masing-masing.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-printer"></i>
                            </div>
                            <div class="feature-content">
                                <h5>Cetak Slip Gaji</h5>
                                <p>Slip gaji dapat dilihat dan dicetak langsung melalui sistem.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-lock"></i>
                            </div>
                            <div class="feature-content">
                                <h5>Lebih Privat</h5>
                                <p>Mengurangi risiko slip gaji terlihat oleh pegawai lain.</p>
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            
            <!-- RIGHT PANEL - LOGIN FORM -->
            <div class="col-lg-6">
                <div class="elegant-right">
                    <div class="login-header">
                        <h3>Masuk Sistem</h3>
                        <p>Masuk untuk melihat dan mencetak slip gaji Anda</p>
                    </div>
                    
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="elegant-alert alert-success">
                            <i class='bx bx-check-circle alert-icon'></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="elegant-alert alert-danger">
                            <i class='bx bx-error-circle alert-icon'></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif
                    
                    <div class="form-container">
                        <form action="{{ route('loginAction') }}" method="POST" id="loginForm">
                            @csrf
                            
                            <div class="form-group">
                                <label class="input-label">Username / NIP</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-user input-icon"></i>
                                    <input type="text" 
                                           class="elegant-input" 
                                           name="login" 
                                           placeholder="Masukkan username / email / NIP"
                                           required 
                                           autocomplete="off"
                                           autofocus>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="input-label">Password</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-key input-icon"></i>
                                    <input type="password" 
                                           class="elegant-input" 
                                           name="password" 
                                           id="password"
                                           placeholder="Masukkan password"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <i class="bx bx-hide" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="elegant-btn" id="loginBtn">
                                <i class="bx bx-log-in-circle"></i>
                                Masuk
                            </button>
                        </form>
                        
                        <div class="login-footer">
                            <p class="copyright">
                                © 2026 Magang Kemnaker • @fzlns21 | @dhiyaind
                                <!-- <span class="version">Sistem Distribusi Slip Gaji</span> -->
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const password = document.getElementById('password');
        const icon = document.getElementById('passwordIcon');
        
        if (password.type === "password") {
            password.type = "text";
            icon.classList.remove("bx-hide");
            icon.classList.add("bx-show");
        } else {
            password.type = "password";
            icon.classList.remove("bx-show");
            icon.classList.add("bx-hide");
        }
    }
    
    // Form submission with elegant loading state
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        const originalHTML = btn.innerHTML;
        
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Memproses...';
        btn.disabled = true;
        btn.style.opacity = '0.8';
        
        // Revert after 5 seconds (fallback)
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            btn.style.opacity = '1';
        }, 5000);
    });
    
    // Add floating animation to logo icon
    const logoIcon = document.querySelector('.logo-icon');
    if (logoIcon) {
        logoIcon.style.animation = 'shine 3s infinite';
    }
    
    // Add subtle animation to form inputs on focus
    const inputs = document.querySelectorAll('.elegant-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    
    // Auto focus first input
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('input[name="login"]');
        if (firstInput) {
            setTimeout(() => {
                firstInput.focus();
            }, 300);
        }
    });
</script>

@endsection