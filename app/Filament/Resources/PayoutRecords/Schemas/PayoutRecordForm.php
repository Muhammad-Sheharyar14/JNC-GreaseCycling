<?php

namespace App\Filament\Resources\PayoutRecords\Schemas;

use App\Models\Customer;
use App\Models\PickupEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class PayoutRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::pluck('name', 'id'))
                    ->disabled(fn ($context) => $context === 'edit')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set, $get) => self::calculateTotals($set, $get)),
                DatePicker::make('date_range_start')
                    ->label('Start Date')
                    ->disabled(fn ($context) => $context === 'edit')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set, $get) => self::calculateTotals($set, $get)),
                DatePicker::make('date_range_end')
                    ->label('End Date')
                    ->disabled(fn ($context) => $context === 'edit')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set, $get) => self::calculateTotals($set, $get)),
                TextInput::make('total_pounds')
                    ->label('Total Weight (lbs)')
                    ->numeric()
                    ->disabled()
                    ->required(),
                TextInput::make('reimbursement_rate')
                    ->label('Reimbursement Rate ($/lb)')
                    ->numeric()
                    ->disabled(fn ($record) => $record?->is_paid ?? false)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                        $pounds = (float) $get('total_pounds');
                        $rate = (float) $state;
                        $set('total_amount_owed', round($pounds * $rate, 2));
                    }),
                TextInput::make('total_amount_owed')
                    ->label('Total Owed ($)')
                    ->numeric()
                    ->disabled()
                    ->required(),
                Toggle::make('is_paid')
                    ->label('Mark Paid')
                    ->live()
                    ->disabled(fn ($record) => $record?->is_paid ?? false),
                DatePicker::make('paid_at')
                    ->label('Date Paid')
                    ->visible(fn (callable $get) => $get('is_paid'))
                    ->required(fn (callable $get) => $get('is_paid'))
                    ->default(now()),
                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'check' => 'Check',
                        'ach' => 'ACH Transfer',
                        'cash' => 'Cash',
                        'other' => 'Other',
                    ])
                    ->visible(fn (callable $get) => $get('is_paid'))
                    ->required(fn (callable $get) => $get('is_paid')),
                Textarea::make('notes')
                    ->label('Accounting Notes')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Compute totals based on customer and range input.
     */
    public static function calculateTotals(callable $set, callable $get): void
    {
        $customerId = $get('customer_id');
        $start = $get('date_range_start');
        $end = $get('date_range_end');

        if (!$customerId || !$start || !$end) {
            return;
        }

        // Query completed pickups for this customer in this range
        $pickups = PickupEvent::whereHas('location', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            })
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(occurred_at)'), [$start, $end])
            ->get();

        $totalPounds = (float) $pickups->sum('pounds_collected');
        $amountOwed = 0.00;
        foreach ($pickups as $pickup) {
            $locationRate = (float) ($pickup->location->reimbursement_rate ?? 0.00);
            $amountOwed += $pickup->pounds_collected * $locationRate;
        }

        $rateSnapshot = $totalPounds > 0 ? round($amountOwed / $totalPounds, 4) : 0.00;

        $set('total_pounds', $totalPounds);
        $set('reimbursement_rate', $rateSnapshot);
        $set('total_amount_owed', round($amountOwed, 2));
    }
}
