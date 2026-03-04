<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('WOLVIX - ERP')
            ->favicon(asset('favicon.ico'))
            ->maxContentWidth('full')
            ->darkMode(false)
            ->colors([
                'primary'  => Color::Indigo,
                'info'     => Color::Sky,
                'success'  => Color::Emerald,
                'warning'  => Color::Amber,
                'danger'   => Color::Rose,
            ])
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Estructura')
                    ->icon('heroicon-o-building-office-2'),
                \Filament\Navigation\NavigationGroup::make('Usuarios y Accesos')
                    ->icon('heroicon-o-users'),
                \Filament\Navigation\NavigationGroup::make('Finanzas y Caja')
                    ->icon('heroicon-o-banknotes'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('280px')
            ->breadcrumbs(true)
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Ir al POS')
                    ->url('/pos', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-shopping-cart')
                    ->group('Ventas')
                    ->sort(1),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->renderHook(
                'panels::user-menu.before',
                function () {
                    $user = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
                    if (!$user) return '';
                    $empresa = $user->company?->razon_social ?? 'Genack Core';
                    return '<div class="hidden lg:block text-xs text-gray-400 mr-4">Empresa: <span class="font-bold text-indigo-500">' . e($empresa) . '</span></div>';
                }
            )
            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Filament\Http\Middleware\AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureCompanyScope::class,
            ])
            ->authGuard('admin');
    }
}
