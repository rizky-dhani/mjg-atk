<?php

namespace App\Providers\Filament;

use App\Helpers\UserRoleChecker;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Auth\EditProfile;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Filament\Resources\OfficeStationeryStockUsageResource;
use App\Filament\Pages\ListRequestOfficeStationeryStockRequest;
use App\Filament\Resources\OfficeStationeryStockRequestResource;
use App\Filament\Resources\OfficeStationeryStockPerDivisionResource;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->login()
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => auth()->user()->name)
                    ->url(fn() => (string) EditProfile::getUrl())
                    ->icon('heroicon-o-user')
            ])
            ->theme(asset('css/filament/dashboard/theme.css'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->navigationGroups([
                NavigationGroup::make('Media Cetak'),
                NavigationGroup::make('Alat Tulis Kantor'),
                NavigationGroup::make('Settings'),

            ])
            ->navigationItems([
                // Pemasukan ATK
                NavigationItem::make('Permintaan ATK (Divisi Saya)')
                    ->url(fn() => (string) OfficeStationeryStockRequestResource::getUrl('my-division'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.office-stationery-stock-requests.my-division'))
                    ->icon('heroicon-o-list-bullet')
                    ->group('Alat Tulis Kantor')
                    ->sort(2),
                NavigationItem::make('Permintaan ATK')
                    ->visible(fn() => UserRoleChecker::isInDivisionWithInitial('IPC'))
                    ->url(fn() => (string) OfficeStationeryStockRequestResource::getUrl('request-list'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.office-stationery-stock-requests.request-list'))
                    ->icon('heroicon-o-document-text')
                    ->group('Alat Tulis Kantor')
                    ->sort(3),
                NavigationItem::make('Pemasukan ATK')
                    ->visible(fn() => UserRoleChecker::isInDivisionWithInitial(['IPC', 'GA']))
                    ->url(fn() => (string) OfficeStationeryStockRequestResource::getUrl('index'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.office-stationery-stock-requests.index'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->group('Alat Tulis Kantor')
                    ->sort(4),

                // Pengeluaran ATK
                NavigationItem::make('Pengeluaran ATK (Divisi Saya)')
                    ->url(fn() => (string) OfficeStationeryStockUsageResource::getUrl('my-divisions'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.office-stationery-stock-usages.my-divisions'))
                    ->icon('heroicon-o-list-bullet')
                    ->group('Alat Tulis Kantor')
                    ->sort(5),
                NavigationItem::make('Pengeluaran ATK')
                    ->visible(fn() => UserRoleChecker::isInDivisionWithInitial(['IPC', 'GA']))
                    ->url(fn() => (string) OfficeStationeryStockUsageResource::getUrl('usage-list'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.office-stationery-stock-usages.request-list'))
                    ->icon('heroicon-o-document-text')
                    ->group('Alat Tulis Kantor')
                    ->sort(6),
                NavigationItem::make('Pengeluaran ATK')
                    ->visible(fn() => UserRoleChecker::isInDivisionWithInitial(['IPC', 'GA']))
                    ->url(fn() => (string) OfficeStationeryStockUsageResource::getUrl('index'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.office-stationery-stock-usages.index'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->group('Alat Tulis Kantor')
                    ->sort(7),

                // Pemasukan Media Cetak
                NavigationItem::make('Permintaan Media Cetak (Divisi Saya)')
                    ->visible(function(){
                        $user = auth()->user();
                        // Only allow users from Marketing divisions to access this page
                        if ($user->division && strpos($user->division->name, 'Marketing') !== false) {
                            return $user->hasRole(['Admin', 'Head']);
                        }
                    })
                    ->url(fn() => (string) \App\Filament\Resources\MarketingMediaStockRequestResource::getUrl('my-division'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.marketing-media-stock-requests.my-division'))
                    ->icon('heroicon-o-list-bullet')
                    ->group('Media Cetak')
                    ->sort(2),
                NavigationItem::make('Permintaan Media Cetak')
                    ->visible(fn() => auth()->user()->division->initial === 'IPC')
                    ->url(fn() => (string) \App\Filament\Resources\MarketingMediaStockRequestResource::getUrl('request-list'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.marketing-media-stock-requests.request-list'))
                    ->icon('heroicon-o-document-text')
                    ->group('Media Cetak')
                    ->sort(3),
                NavigationItem::make('Pemasukan Media Cetak')
                    ->visible(fn() => auth()->user()->division->initial === 'IPC')
                    ->url(fn() => (string) \App\Filament\Resources\MarketingMediaStockRequestResource::getUrl('index'))
                    ->isActiveWhen(fn(): string => request()->routeIs('filament.dashboard.resources.marketing-media-stock-requests.index'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->group('Media Cetak')
                    ->sort(4)
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //
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
