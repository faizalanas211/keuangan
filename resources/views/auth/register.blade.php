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

    /* ===== ELEGANT CARD ===== */
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

    .register-header {
        margin-bottom: 1.8rem;
        text-align: center;
    }

    .register-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.7rem;
        font-weight: 700;
        color: var(--emerald-800);
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .register-header p {
        color: var(--emerald-600);
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .form-container {
        max-width: 420px;
        margin: 0 auto;
    }

    /* ===== ELEGANT FORM ===== */
    .form-group {
        margin-bottom: 1.2rem;
        animation: fadeInUp 0.6s ease-out;
        animation-fill-mode: both;
    }

    .form-group:nth-child(1) { animation-delay: 0.1s; }
    .form-group:nth-child(2) { animation-delay: 0.2s; }
    .form-group:nth-child(3) { animation-delay: 0.3s; }
    .form-group:nth-child(4) { animation-delay: 0.4s; }
    .form-group:nth-child(5) { animation-delay: 0.5s; }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
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

    .elegant-input.is-invalid {
        border-color: #dc2626;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc2626'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc2626' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        padding-right: calc(1.5em + 0.75rem);
    }

    .elegant-input.is-invalid:focus {
        box-shadow: 
            0 0 0 4px rgba(220, 38, 38, 0.1),
            0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #dc2626;
        font-weight: 500;
        background: rgba(220, 38, 38, 0.05);
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border-left: 3px solid #dc2626;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
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
        z-index: 2;
    }

    .password-toggle:hover {
        color: var(--emerald-700);
    }

    /* ===== ELEGANT BUTTON ===== */
    .elegant-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 400;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        text-transform: uppercase;
        margin-top: 1rem;
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

    /* ===== TERMS & CONDITIONS ===== */
    .terms-agreement {
        margin: 2rem 0;
        padding: 1.5rem;
        background: rgba(6, 95, 70, 0.03);
        border-radius: 12px;
        border: 1px solid rgba(6, 95, 70, 0.1);
    }

    .form-check {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.25rem;
        border: 2px solid rgba(6, 95, 70, 0.3);
        border-radius: 4px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .form-check-input:checked {
        background-color: var(--emerald-600);
        border-color: var(--emerald-600);
    }

    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
    }

    .form-check-label {
        font-size: 0.9rem;
        color: var(--emerald-700);
        line-height: 1.5;
        cursor: pointer;
    }

    .form-check-label a {
        color: var(--emerald-600);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
        position: relative;
    }

    .form-check-label a:hover {
        color: var(--emerald-800);
        text-decoration: underline;
    }

    /* ===== LOGIN LINK ===== */
    .login-link {
        text-align: center;
        margin-top: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(6, 95, 70, 0.1);
        animation: fadeInUp 0.6s ease-out 0.8s both;
    }

    .login-link p {
        margin: 0;
        color: var(--emerald-600);
        font-size: 0.95rem;
    }

    .login-link a {
        color: var(--emerald-600);
        text-decoration: none;
        font-weight: 600;
        margin-left: 0.5rem;
        transition: all 0.3s ease;
        position: relative;
    }

    .login-link a:hover {
        color: var(--emerald-800);
    }

    .login-link a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background: var(--emerald-600);
        transition: width 0.3s ease;
    }

    .login-link a:hover::after {
        width: 100%;
    }

    /* ===== FOOTER ===== */
    .register-footer {
        margin-top: 1rem;
        text-align: center;
        padding-top: 2rem;
        border-top: 1px solid rgba(6, 95, 70, 0.1);
    }

    .copyright {
        font-size: 0.875rem;
        color: var(--emerald-300);
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
            padding: 2.5rem 2.5rem;
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

    /* ===== PASSWORD STRENGTH ===== */
    .password-strength {
        margin-top: 0.5rem;
        height: 6px;
        background: rgba(6, 95, 70, 0.1);
        border-radius: 3px;
        overflow: hidden;
        position: relative;
    }

    .strength-meter {
        height: 100%;
        width: 0;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .strength-weak { background: #ef4444; }
    .strength-medium { background: #f59e0b; }
    .strength-strong { background: #10b981; }

    .strength-text {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        text-align: right;
        color: var(--emerald-600);
        font-weight: 500;
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
                            <i class="bx bx-user-circle"></i>
                        </div>
                        <h1 class="brand-name">
                            Sistem Distribusi<br>
                            Slip Gaji
                        </h1>
                        <p class="brand-tagline">
                            Aktifkan akun Anda untuk mengakses dan mencetak slip gaji secara mandiri melalui sistem.
                            
                        </p>
                    </div>
                    
                    <div class="feature-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-shield"></i>
                            </div>
                            <div class="feature-content">
                                <h5>Akses Pribadi</h5>
                                <p>Slip gaji hanya dapat diakses oleh masing-masing pegawai</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-history"></i>
                            </div>
                            <div class="feature-content"><h5>Riwayat Slip</h5>
                            <p>Lihat dan unduh slip gaji periode sebelumnya kapan saja</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-download"></i>
                            </div>
                            <div class="feature-content"><h5>Cetak Mandiri</h5>
                            <p>Unduh dan cetak slip gaji langsung dari sistem</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="security-note">
                        <p>
                            <i class="bx bx-lock-alt"></i>
                            <span>
                            Sistem ini dirancang untuk memastikan distribusi slip gaji lebih aman dan tidak dapat diakses oleh pihak lain.
                            </span>
                        </p>
                    </div>

                    <div class="register-footer">
                        <p class="copyright">
                            © 2026 Magang Kemnaker • @fzlns21 | @dhiyaind
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT PANEL - REGISTRATION FORM -->
            <div class="col-lg-6">
                <div class="elegant-right">
                    <div class="register-header">
                        <h3>Registrasi Akun</h3>
                        <p>Isilah detail berikut untuk registrasi akun</p>
                    </div>
                    
                    <div class="form-container">
                        <form id="formAuthentication" action="{{ route('registerAction') }}" method="POST">
                            @csrf
                            
                            <!-- Nama Lengkap -->
                            <div class="form-group">
                                <label class="input-label">Nama Lengkap</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-user input-icon"></i>
                                    <input type="text" 
                                           class="elegant-input @error('name') is-invalid @enderror"
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}"
                                           placeholder="Masukkan nama lengkap Anda"
                                           required 
                                           autocomplete="off">
                                </div>
                                @error('name')
                                    <div class="invalid-feedback">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <!-- NIP -->
                            <div class="form-group">
                                <label class="input-label">NIP</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-id-card input-icon"></i>
                                    <input type="text" 
                                           class="elegant-input @error('nip') is-invalid @enderror"
                                           id="nip" 
                                           name="nip" 
                                           value="{{ old('nip') }}"
                                           placeholder="Masukkan Nomor Induk Pegawai"
                                           required 
                                           autocomplete="off">
                                </div>
                                @error('nip')
                                    <div class="invalid-feedback">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div class="form-group">
                                <label class="input-label">Email</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-envelope input-icon"></i>
                                    <input type="email" 
                                           class="elegant-input @error('email') is-invalid @enderror"
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}"
                                           placeholder="contoh@emailanda.com"
                                           required 
                                           autocomplete="off">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <!-- Jabatan -->
                            <div class="form-group">
                                <label class="input-label">Jabatan</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-briefcase input-icon"></i>
                                    <input type="text" 
                                           class="elegant-input @error('jabatan') is-invalid @enderror"
                                           id="jabatan" 
                                           name="jabatan" 
                                           value="{{ old('jabatan') }}"
                                           placeholder="Masukkan jabatan Anda"
                                           required 
                                           autocomplete="off">
                                </div>
                                @error('jabatan')
                                    <div class="invalid-feedback">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <!-- Password -->
                            <div class="form-group">
                                <label class="input-label">Password</label>
                                <div class="input-with-icon">
                                    <i class="bx bx-lock-alt input-icon"></i>
                                    <input type="password" 
                                           class="elegant-input @error('password') is-invalid @enderror"
                                           id="password" 
                                           name="password" 
                                           placeholder="Buat password yang kuat"
                                           required 
                                           autocomplete="off">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="bx bx-hide" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-meter" id="strengthMeter"></div>
                                </div>
                                <div class="strength-text" id="strengthText">Password strength</div>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="bx bx-error-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <button type="submit" class="elegant-btn" id="registerBtn">
                                <i class="bx bx-user-plus"></i>
                                Buat Akun
                            </button>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="login-link">
                            <p>
                                Sudah punya akun?
                                <a href="{{ route('login') }}">
                                    Masuk di sini
                                </a>
                            </p>
                        </div>
                        
                        <!-- <div class="register-footer">
                            <p class="copyright">
                                © 2026 Magang Kemnaker • @fzlns21 | @dhiyaind
                            </p>
                        </div> -->
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
    // Password Toggle Function
    function togglePassword(fieldId) {
        const password = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + 'Icon');
        
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
    
    // Password Strength Checker
    document.getElementById('password').addEventListener('input', function(e) {
        const password = e.target.value;
        const meter = document.getElementById('strengthMeter');
        const text = document.getElementById('strengthText');
        
        let strength = 0;
        
        // Check password length
        if (password.length >= 8) strength += 20;
        if (password.length >= 12) strength += 20;
        
        // Check for uppercase letters
        if (/[A-Z]/.test(password)) strength += 20;
        
        // Check for lowercase letters
        if (/[a-z]/.test(password)) strength += 20;
        
        // Check for numbers
        if (/[0-9]/.test(password)) strength += 20;
        
        // Check for special characters
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        // Cap at 100
        strength = Math.min(strength, 100);
        
        // Update meter
        meter.style.width = strength + '%';
        
        // Update text and color
        if (strength < 40) {
            meter.className = 'strength-meter strength-weak';
            text.textContent = 'Lemah';
            text.style.color = '#ef4444';
        } else if (strength < 70) {
            meter.className = 'strength-meter strength-medium';
            text.textContent = 'Cukup';
            text.style.color = '#f59e0b';
        } else {
            meter.className = 'strength-meter strength-strong';
            text.textContent = 'Kuat';
            text.style.color = '#10b981';
        }
    });
    
    // Form submission with elegant loading state
    document.getElementById('formAuthentication').addEventListener('submit', function(e) {
        const btn = document.getElementById('registerBtn');
        const originalHTML = btn.innerHTML;
        
        if (!document.getElementById('terms').checked) {
            e.preventDefault();
            alert('Harap setujui Syarat & Ketentuan untuk melanjutkan pendaftaran.');
            return;
        }
        
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
        btn.disabled = true;
        btn.style.opacity = '0.8';
        
        // Revert after 5 seconds (fallback)
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            btn.style.opacity = '1';
        }, 5000);
    });
    
    // Add animation to form groups on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.form-group').forEach((group) => {
        observer.observe(group);
    });
    
    // Auto focus first input
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.getElementById('name');
        if (firstInput) {
            setTimeout(() => {
                firstInput.focus();
            }, 300);
        }
    });
    
    // Terms modal (simplified version)
    document.addEventListener('DOMContentLoaded', function() {
        const termsLinks = document.querySelectorAll('a[data-bs-target="#termsModal"], a[data-bs-target="#privacyModal"]');
        termsLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!document.getElementById('termsModal') && !document.getElementById('privacyModal')) {
                    e.preventDefault();
                    const modalType = this.getAttribute('data-bs-target').includes('terms') ? 'Syarat & Ketentuan' : 'Kebijakan Privasi';
                    alert(`${modalType} akan ditampilkan di sini. Untuk versi lengkap, hubungi administrator sistem.`);
                }
            });
        });
    });
</script>

@endsection