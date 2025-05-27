<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Warga\Pages\FinancialReport;
use Filament\Enums\ThemeMode;

class WargaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('warga')
            ->default()
            ->path('/')
            ->favicon('images/favicon-wargaku.png')
            ->darkmode(false)
            ->login(\App\Filament\Warga\Pages\Auth\Login::class)
            ->defaultThemeMode(
                ThemeMode::Light
            )
            ->colors([
                'primary' => Color::Blue,
            ])
            ->viteTheme('resources/css/filament/warga/theme.css') 
            ->discoverResources(in: app_path('Filament/Warga/Resources'), for: 'App\\Filament\\Warga\\Resources')
            ->discoverPages(in: app_path('Filament/Warga/Pages'), for: 'App\\Filament\\Warga\\Pages')
            ->pages([
                Pages\Dashboard::class,
                FinancialReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Warga/Widgets'), for: 'App\\Filament\\Warga\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
