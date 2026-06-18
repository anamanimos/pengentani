<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ route('console.dashboard') }}">
            <img alt="Logo" src="{{ asset('pengentani.png') }}" class="h-35px app-sidebar-logo-default" />
            <img alt="Logo" src="{{ asset('pengentani-icon.png') }}" class="h-30px app-sidebar-logo-minimize" />
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('console.dashboard') ? 'active' : '' }}" href="{{ route('console.dashboard') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-element-11 fs-2">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('pertanians.*') ? 'active' : '' }}" href="{{ route('pertanians.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-briefcase fs-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Pertanian</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('updates.*') || request()->routeIs('pertanians.updates.*') ? 'active' : '' }}" href="{{ route('updates.global_index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-information fs-2">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Update Info Proyek</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}" href="{{ route('incomes.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-dollar fs-2">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Pencatatan Pendapatan</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-basket fs-2">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Pencatatan Pembelian</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('worker-jobs.*') ? 'active' : '' }}" href="{{ route('worker-jobs.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-calendar-tick fs-2">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                                </i>
                            </span>
                            <span class="menu-title">Pencatatan Pekerja</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item (Master Dropdown)-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs(['users.*', 'kebuns.*', 'tanamans.*']) ? 'here' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-abstract-26 fs-2">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Master</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Pengguna</span>
                                </a>
                            </div>
                            <!--end:Menu item-->

                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('kebuns.*') ? 'active' : '' }}" href="{{ route('kebuns.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Kebun</span>
                                </a>
                            </div>
                            <!--end:Menu item-->

                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('tanamans.*') ? 'active' : '' }}" href="{{ route('tanamans.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Tanaman</span>
                                </a>
                            </div>
                            <!--end:Menu item-->

                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('tengkulaks.*') ? 'active' : '' }}" href="{{ route('tengkulaks.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Tengkulak</span>
                                </a>
                            </div>
                            <!--end:Menu item-->
                        </div>
                    </div>
                    <!--end:Menu item-->

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
<!--end::Sidebar-->
