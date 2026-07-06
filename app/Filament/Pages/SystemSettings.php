<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemSettings extends Page
{
    protected string $view = 'filament.pages.system-settings';

    protected static ?string $title = 'System Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public string $default_reimbursement_rate = '0.00';
    public string $default_payout_frequency = 'quarterly';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    public function mount(): void
    {
        $this->default_reimbursement_rate = SystemSetting::get('default_reimbursement_rate', '0.00');
        $this->default_payout_frequency = SystemSetting::get('default_payout_frequency', 'quarterly');
    }

    public function save(): void
    {
        $this->validate([
            'default_reimbursement_rate' => 'required|numeric|min:0',
            'default_payout_frequency' => 'required|in:weekly,biweekly,monthly,quarterly',
        ]);

        SystemSetting::set('default_reimbursement_rate', number_format((float) $this->default_reimbursement_rate, 2, '.', ''));
        SystemSetting::set('default_payout_frequency', $this->default_payout_frequency);

        Notification::make()
            ->success()
            ->title('Settings saved successfully.')
            ->send();
    }
}
