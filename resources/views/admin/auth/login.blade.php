<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b5a70">
    <title>Đăng nhập quản trị | HG Trip</title>
    <x-favicon-links />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin-login-page">
    <main class="admin-login">
        <section class="admin-login__shell" aria-label="Đăng nhập quản trị HG Trip">
            <div class="admin-login__form-panel">
                <div class="admin-login__form-wrap">
                    <a href="{{ route('home') }}" class="admin-login__brand" aria-label="Về trang chủ HG Trip">
                        <img src="{{ asset('images/logo-hgtrip.png') }}" alt="HG Trip" class="admin-login__brand-logo">
                    </a>

                    <div class="admin-login__heading">
                        <span class="admin-login__eyebrow"><i class="bi bi-shield-check" aria-hidden="true"></i> Cổng quản trị</span>
                        <h1>Chào mừng trở lại</h1>
                        <p>Đăng nhập để quản lý hành trình và trải nghiệm khách hàng cùng HG Trip.</p>
                    </div>

                    <form method="post" action="{{ route('admin.login.store') }}" class="admin-login__form">
                        @csrf

                        <div class="admin-login__field">
                            <label for="email" class="form-label">Email quản trị</label>
                            <div class="admin-login__input-wrap">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control admin-login__input @error('email') is-invalid @enderror" autocomplete="username" required autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-login__field">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <div class="admin-login__input-wrap">
                                <i class="bi bi-key" aria-hidden="true"></i>
                                <input id="password" name="password" type="password" class="form-control admin-login__input @error('password') is-invalid @enderror" autocomplete="current-password" required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-login__options">
                            <div class="form-check">
                                <input name="remember" value="1" class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                        </div>

                        <button class="btn admin-login__submit" type="submit">
                            <span>Đăng nhập</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>

                    <p class="admin-login__security"><i class="bi bi-lock-fill" aria-hidden="true"></i> Khu vực dành riêng cho quản trị viên HG Trip.</p>
                </div>
            </div>

            <aside class="admin-login__visual" aria-label="Hành trình cùng HG Trip">
                <img src="{{ asset('images/about/hg-trip/journey-kazakhstan.jpg') }}" alt="Đoàn khách HG Trip tại Cape of Good Hope">
                <div class="admin-login__visual-shade"></div>
                <div class="admin-login__visual-copy">
                    <span class="admin-login__visual-badge"><i class="bi bi-stars" aria-hidden="true"></i> HG Trip Experience</span>
                    <div>
                        <p class="admin-login__visual-kicker">Mỗi chuyến đi là một câu chuyện</p>
                        <h2>Kiến tạo những hành trình đáng nhớ.</h2>
                        <p>Đồng hành cùng khách hàng từ kế hoạch đầu tiên đến từng khoảnh khắc trên đường đi.</p>
                    </div>
                </div>
            </aside>
        </section>
    </main>
</body>
</html>
