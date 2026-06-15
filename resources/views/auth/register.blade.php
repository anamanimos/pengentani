<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head>
		<title>Register - Pengen Tani</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
		<!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="app-blank">
		<!--begin::Theme mode setup on page load-->
		<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
		<!--end::Theme mode setup on page load-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<!--begin::Authentication - Sign-up -->
			<div class="d-flex flex-column flex-lg-row flex-column-fluid">
				<!--begin::Body-->
				<div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
					<!--begin::Form-->
					<div class="d-flex flex-center flex-column flex-lg-row-fluid">
						<!--begin::Wrapper-->
						<div class="w-lg-500px p-10">
							<!--begin::Form-->
							<form class="form w-100" novalidate="novalidate" id="kt_sign_up_form" method="POST" action="{{ route('register') }}">
                                @csrf
								<!--begin::Heading-->
								<div class="text-center mb-11">
									<!--begin::Title-->
									<h1 class="text-gray-900 fw-bolder mb-3">Sign Up</h1>
									<!--end::Title-->
									<!--begin::Subtitle-->
									<div class="text-gray-500 fw-semibold fs-6">Pengen Tani System</div>
									<!--end::Subtitle=-->
								</div>
								<!--begin::Heading-->
								
								<!--begin::Input group=-->
								<div class="fv-row mb-8">
									<!--begin::Name-->
									<input type="text" placeholder="Name" name="name" value="{{ old('name') }}" autocomplete="name" class="form-control bg-transparent @error('name') is-invalid @enderror" />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
									<!--end::Name-->
								</div>
								<!--end::Input group=-->

								<!--begin::Input group=-->
								<div class="fv-row mb-8">
									<!--begin::Email-->
									<input type="email" placeholder="Email" name="email" value="{{ old('email') }}" autocomplete="email" class="form-control bg-transparent @error('email') is-invalid @enderror" />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
									<!--end::Email-->
								</div>
                                <!--end::Input group=-->
                                
								<!--begin::Input group-->
								<div class="fv-row mb-8" data-kt-password-meter="true">
									<!--begin::Wrapper-->
									<div class="mb-1">
										<!--begin::Input wrapper-->
										<div class="position-relative mb-3">
											<input class="form-control bg-transparent @error('password') is-invalid @enderror" type="password" placeholder="Password" name="password" autocomplete="new-password" />
											<span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
												<i class="ki-duotone ki-eye-slash fs-2"></i>
												<i class="ki-duotone ki-eye fs-2 d-none"></i>
											</span>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
										</div>
										<!--end::Input wrapper-->
										<!--begin::Meter-->
										<div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
											<div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
										</div>
										<!--end::Meter-->
									</div>
									<!--end::Wrapper-->
									<!--begin::Hint-->
									<div class="text-muted">Use 8 or more characters with a mix of letters, numbers & symbols.</div>
									<!--end::Hint-->
								</div>
								<!--end::Input group=-->
                                
								<!--begin::Input group=-->
								<div class="fv-row mb-8">
									<!--begin::Repeat Password-->
									<input placeholder="Repeat Password" name="password_confirmation" type="password" autocomplete="new-password" class="form-control bg-transparent @error('password_confirmation') is-invalid @enderror" />
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
									<!--end::Repeat Password-->
								</div>
								<!--end::Input group=-->

								<!--begin::Submit button-->
								<div class="d-grid mb-10">
									<button type="submit" id="kt_sign_up_submit" class="btn btn-primary">
										<!--begin::Indicator label-->
										<span class="indicator-label">Sign up</span>
										<!--end::Indicator label-->
									</button>
								</div>
								<!--end::Submit button-->
								<!--begin::Sign up-->
								<div class="text-gray-500 text-center fw-semibold fs-6">Already have an Account? 
								<a href="{{ route('login') }}" class="link-primary fw-semibold">Sign in</a></div>
								<!--end::Sign up-->
							</form>
							<!--end::Form-->
						</div>
						<!--end::Wrapper-->
					</div>
					<!--end::Form-->
				</div>
				<!--end::Body-->
				<!--begin::Aside-->
				<div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2" style="background-image: url({{ asset('assets/media/misc/auth-bg.png') }})">
					<!--begin::Content-->
					<div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
						<!--begin::Logo-->
						<a href="{{ url('/') }}" class="mb-0 mb-lg-12">
							<img alt="Logo" src="{{ asset('assets/media/logos/custom-1.png') }}" class="h-60px h-lg-75px" />
						</a>
						<!--end::Logo-->
						<!--begin::Image-->
						<img class="d-none d-lg-block mx-auto w-275px w-md-50 w-xl-500px mb-10 mb-lg-20" src="{{ asset('assets/media/misc/auth-screens.png') }}" alt="" />
						<!--end::Image-->
						<!--begin::Title-->
						<h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-7">Welcome to Pengen Tani</h1>
						<!--end::Title-->
						<!--begin::Text-->
						<div class="d-none d-lg-block text-white fs-base text-center">Pengen Tani Registration Portal</div>
						<!--end::Text-->
					</div>
					<!--end::Content-->
				</div>
				<!--end::Aside-->
			</div>
			<!--end::Authentication - Sign-up-->
		</div>
		<!--end::Root-->
		<!--begin::Javascript-->
		<script>var hostUrl = "{{ asset('assets/') }}";</script>
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Custom Javascript(used for this page only)-->
		<script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('kt_sign_up_form');
                const submitButton = document.getElementById('kt_sign_up_submit');

                // Konfigurasi Toastr bawaan Metronic
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toastr-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // Tampilkan loading state
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;

                    // Bersihkan pesan error sebelumnya
                    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    document.querySelectorAll('.invalid-feedback.ajax-error').forEach(el => el.remove());

                    axios.post(form.action, new FormData(form))
                        .then(function (response) {
                            toastr.success("Registrasi berhasil! Mengalihkan ke dashboard...", "Berhasil");
                            setTimeout(function() {
                                window.location.href = "{{ route('dashboard') }}";
                            }, 1000);
                        })
                        .catch(function (error) {
                            if (error.response && error.response.status === 422) {
                                let errors = error.response.data.errors;
                                for (let field in errors) {
                                    let input = form.querySelector('[name="' + field + '"]');
                                    if (input) {
                                        input.classList.add('is-invalid');
                                        let errorDiv = document.createElement('div');
                                        errorDiv.className = 'invalid-feedback ajax-error';
                                        errorDiv.innerHTML = errors[field][0];
                                        input.parentNode.appendChild(errorDiv);
                                    }
                                    toastr.error(errors[field][0], "Validasi Gagal");
                                }
                            } else {
                                toastr.error("Terjadi kesalahan sistem, silakan coba lagi nanti.", "Error");
                            }
                        })
                        .finally(function () {
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        });
                });
            });
        </script>
		<!--end::Custom Javascript-->
	</body>
	<!--end::Body-->
</html>
