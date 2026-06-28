<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<head>
    <title>@yield('title', 'Dashboard') - Pengen Tani</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('pengentani-icon.png') }}" />
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    <style>
        .table-responsive { overflow-x: auto; }
        .table-responsive .table th:first-child,
        .table-responsive .table td:first-child {
            position: sticky;
            left: 0;
            background-color: var(--bs-body-bg);
            z-index: 1;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .table-responsive .table th:first-child {
            z-index: 2;
            background-color: var(--bs-light);
        }
        [data-bs-theme="dark"] .table-responsive .table th:first-child,
        [data-bs-theme="dark"] .table-responsive .table td:first-child {
            box-shadow: 2px 0 5px rgba(0,0,0,0.5);
        }
    </style>
    @stack('styles')
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_app_body" data-kt-app-layout="light-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
    <script>if (localStorage.getItem("sidebar_minimize_state") === "on") { document.body.setAttribute("data-kt-app-sidebar-minimize", "on"); }</script>
    <!--end::Theme mode setup on page load-->
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            
            @include('layouts.partials.header')
            
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                
                @include('layouts.partials.sidebar')
                
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        
                        <!--begin::Toolbar-->
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <!--begin::Toolbar container-->
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                                <!--begin::Page title-->
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                    <!--begin::Title-->
                                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">@yield('page_title', 'Dashboard')</h1>
                                    <!--end::Title-->
                                </div>
                                <!--end::Page title-->
                                <!--begin::Actions-->
                                <div class="d-flex align-items-center gap-2 gap-lg-3">
                                    @yield('page_actions')
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Toolbar container-->
                        </div>
                        <!--end::Toolbar-->

                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <!--begin::Content container-->
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @yield('content')
                            </div>
                            <!--end::Content container-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Content wrapper-->
                    
                    @include('layouts.partials.footer')
                    
                </div>
                <!--end:::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->
    
    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>
    <!--end::Scrolltop-->

    <!--begin::Javascript-->
    <script>var hostUrl = "{{ asset('assets/') }}";</script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toggleBtn = document.getElementById("kt_app_sidebar_toggle");
            if (toggleBtn) {
                toggleBtn.addEventListener("click", function() {
                    setTimeout(function() {
                        var isMinimized = document.body.getAttribute("data-kt-app-sidebar-minimize") === "on";
                        localStorage.setItem("sidebar_minimize_state", isMinimized ? "on" : "off");
                    }, 100);
                });
            }
        });
    </script>
    <script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
    <script>
        function openLightbox(e, url) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            var lightbox = new FsLightbox();
            lightbox.props.sources = [url];
            lightbox.open();
        }
    </script>
    @stack('scripts')
    <!--end::Javascript-->
</body>
<!--end::Body-->
</html>
