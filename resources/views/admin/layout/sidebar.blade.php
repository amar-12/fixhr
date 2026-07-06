<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$rolePrmission = new RolePermissionLogics();
// NEW LINE ADDED
$hasActiveSubscription = RolePermissionLogics::businessHasActiveSubscription();
?>
<!-- APP-SIDEBAR -->
<div class="sticky">
    <aside class="app-sidebar">
        <div class="app-sidebar__logo">
            <a class="header-brand" href="index.html">
                <img src="{{ asset('assets/logo/logo.png') }}" class="header-brand-img desktop-main-logo"
                    alt="FixingDotslogo">
                <a href="{{ url('/dashboard') }}" style="display: flex; justify-content: center; align-items: center;">
                    <img src="{{ asset('assets/logo/logo_dark.png') }}" class="header-brand-img dark-logo"
                        style="transform: translateY(-5px); max-width: 70%;" alt="FixingDotslogo">
                </a>
                <img src="{{ asset('assets/logo/logo_round.png') }}" class="header-brand-img mobile-logo"
                    alt="FixingDotslogo">
                <img src="{{ asset('assets/logo/logo_round.png') }}" class="header-brand-img darkmobile-logo"
                    alt="FixingDotslogo">
                <small
                    style="position: absolute; bottom: 3px; right: 1px;
                    font-size: 11px; color: rgb(253, 253, 253); padding: 2px 4px;
                    border-radius: 3px; font-weight: 900;">
                    v{{ config('app.version') }}
                </small>
            </a>
        </div>
        <div class="app-sidebar3">
            <div class="main-menu">
                <div class="app-sidebar__user">
                    <div class="dropdown user-pro-body text-center">
                        <div class="user-info">
                            <h5 class=" mb-2">{{ ucfirst($user->emp_full_name) }}</h5>
                            <span
                                class="text-muted app-sidebar__user-name text-sm">{{ ucfirst($user->fh_role->role_name) }}</span>
                        </div>
                    </div>
                </div>
                <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                        width="24" height="24" viewBox="0 0 24 24">
                        <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                    </svg>
                </div>
                <ul class="side-menu">
                    {{-- NEW CONDITION: SHOW MENUS ONLY IF SUBSCRIPTION ACTIVE --}}
                    @if ($hasActiveSubscription)
                        @php
                            // Fetch all menu data and convert to array
                            $allMenus = $rolePrmission->get_all_menus();
                            if ($allMenus instanceof \Illuminate\Support\Collection) {
                                $allMenus = $allMenus->toArray();
                            }
                            $permissions = [];
                            if ($rolePrmission->get_role_wise_menu_permissions()) {
                                $permissions = (array) json_decode(
                                    $rolePrmission->get_role_wise_menu_permissions()->rhp_permissions,
                                );
                            }
                            if (!function_exists('get_menu_panel_list')) {
                                function get_menu_panel_list($menus, $id = 0)
                                {
                                    return array_filter($menus, function ($menu) use ($id) {
                                        return $menu['menu_p_id'] == $id &&
                                            $menu['menu_status'] == 1 &&
                                            is_null($menu['menu_sub_status']);
                                    });
                                }
                            }
                            if (!function_exists('get_menu_list')) {
                                function get_menu_list($menus, $id = '', $status = 0)
                                {
                                    return array_filter($menus, function ($menu) use ($id, $status) {
                                        $matches = $menu['menu_status'] == 1 && $menu['menu_sub_status'] == $status;
                                        if ($id !== '') {
                                            $matches = $matches && $menu['menu_p_id'] == $id;
                                        }
                                        return $matches;
                                    });
                                }
                            }
                            $menus = get_menu_panel_list($allMenus);
                        @endphp
                        {{-- UNTOUCHED LOOP STARTS HERE --}}
                        @foreach ($menus as $menu)
                            @foreach (get_menu_panel_list($allMenus, $menu['menu_id']) as $submenu)
                                @foreach (get_menu_list($allMenus, $submenu['menu_id']) as $childmenu)
                                    @if (array_key_exists($childmenu['menu_id'], $permissions))
                                        @php($pannelEnable[$menu['menu_id']] = true)
                                    @endif
                                @endforeach
                            @endforeach
                            @foreach (get_menu_panel_list($allMenus, $menu['menu_id']) as $submenu)
                                @if ($submenu['menu_route'] != '#')
                                    @php($pannelEnable[$menu['menu_id']] = true)
                                @endif
                            @endforeach
                            @foreach ($menus as $submenu)
                                @foreach (get_menu_list($allMenus, $submenu['menu_id']) as $childmenu)
                                    @if (array_key_exists($childmenu['menu_id'], $permissions))
                                        @php($pannelEnable[$submenu['menu_id']] = true)
                                    @endif
                                @endforeach
                            @endforeach
                            @if (isset($pannelEnable[$menu['menu_id']]))
                                <li class="side-item side-item-category mt-4"
                                    @if ($menu['menu_is_hidden'] ?? false) hidden @endif>{{ $menu['menu_name'] }}</li>
                            @endif
                            @foreach (get_menu_panel_list($allMenus, $menu['menu_id']) as $submenu)
                                @foreach (get_menu_list($allMenus, $submenu['menu_id']) as $childmenu)
                                    @if (array_key_exists($childmenu['menu_id'], $permissions))
                                        @php($enable[$submenu['menu_id']] = true)
                                    @endif
                                @endforeach
                                @if ($submenu['menu_route'] != '#')
                                    <li class="slide">
                                        <a class="side-menu__item {{ RolePermissionLogics::is_active_sidebar_menu($allMenus, $submenu['menu_id']) ? 'active' : '' }}"
                                            href="{{ url($submenu['menu_route']) }}">
                                            <i class="sidemenu_icon {{ $submenu['menu_icon'] }}"></i>
                                            <span class="side-menu__label">{{ $submenu['menu_name'] }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (isset($enable[$submenu['menu_id']]))
                                    <li
                                        class="slide  {{ RolePermissionLogics::is_active_sidebar_menu($allMenus, $submenu['menu_id']) ? 'is-expanded' : '' }}">
                                        <a class="side-menu__item {{ RolePermissionLogics::is_active_sidebar_menu($allMenus, $submenu['menu_id']) ? 'active' : '' }}"
                                            data-bs-toggle="slide" href="javascript:void(0);">
                                            <i class="sidemenu_icon {{ $submenu['menu_icon'] }}"></i>
                                            <span class="side-menu__label">{{ $submenu['menu_name'] }}</span><i
                                                class="angle fa fa-angle-right"></i>
                                        </a>
                                        <ul class="slide-menu menu-bg">
                                            <li class="side-menu-label1"><a
                                                    href="javascript:void(0);">{{ $submenu['menu_name'] }}</a></li>
                                            @foreach (get_menu_list($allMenus, $submenu['menu_id']) as $childmenu)
                                                @if (array_key_exists($childmenu['menu_id'], $permissions))
                                                    <li><a href="{{ url($childmenu['menu_route']) }}"
                                                            class="slide-item {{ RolePermissionLogics::is_active_sidebar_menu($allMenus, $childmenu['menu_id']) ? 'active' : '' }}">{{ $childmenu['menu_name'] }}</a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endforeach
                            @foreach (get_menu_list($allMenus, $menu['menu_id']) as $submenu)
                                @if (array_key_exists($submenu['menu_id'], $permissions))
                                    @if ($submenu['menu_route'] != '#')
                                        <li class="slide">
                                            <a class="side-menu__item {{ RolePermissionLogics::is_active_sidebar_menu($allMenus, $submenu['menu_id']) ? 'active' : '' }}"
                                                href="{{ url($submenu['menu_route']) }}">
                                                <i class="sidemenu_icon {{ $submenu['menu_icon'] }}"></i>
                                                <span class="side-menu__label">{{ $submenu['menu_name'] }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endforeach
                        @endforeach
                        {{-- LOOP END --}}
                        {{-- END SUBSCRIPTION CHECK --}}
                    @else
                        <li class="side-item mt-4 px-3 text-danger">
                            Subscription Expired
                        </li>
                    @endif
                    {{-- @if ($user->is_super_admin)
                        <li class="slide">
                            <a class="side-menu__item " href="{{ url('/super-admin/organizations') }}">
                                <i class="sidemenu_icon "></i>
                                <span class="side-menu__label">Super Admin</span>
                            </a>
                        </li>
                    @endif --}}
                </ul>
                <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                        width="24" height="24" viewBox="0 0 24 24">
                        <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                    </svg></div>
            </div>
        </div>
    </aside>
</div>
<!-- APP-SIDEBAR CLOSED -->
