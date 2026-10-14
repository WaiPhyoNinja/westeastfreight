<?php

namespace App\Providers\Filament;

use App\Models\SiteSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Throwable;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn (): string => $this->brandName())
            ->brandLogo(fn (): string => $this->brandLogoUrl())
            ->brandLogoHeight('2.5rem')
            ->databaseNotifications()
            ->sidebarWidth('16rem')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.styles.page-blocks'))
            ->navigationGroups([
                'Website',
                'Invoices',
                'Site Settings',
                'Administration',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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

    private function brandName(): string
    {
        try {
            return SiteSetting::query()->value('brand_name') ?: config('app.name', 'West East Freight');
        } catch (Throwable) {
            return config('app.name', 'West East Freight');
        }
    }

    private function brandLogoUrl(): string
    {
        try {
            $logo = SiteSetting::query()->value('header_logo');

            if (filled($logo)) {
                return Str::startsWith($logo, ['http://', 'https://', '/'])
                    ? $logo
                    : url(Storage::disk('public')->url($logo));
            }
        } catch (Throwable) {
            // Keep the admin panel available before the settings table is ready.
        }

        return asset('assets/img/logo.png');
    }
}
