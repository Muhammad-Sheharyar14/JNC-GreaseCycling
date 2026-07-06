<?php

namespace App\Filament\Resources\PayoutRecords\Tables;

use App\Models\Customer;
use App\Models\PayoutRecord;
use App\Models\PickupEvent;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_range')
                    ->label('Period')
                    ->state(fn ($record) => $record->date_range_start->format('M d, Y') . ' - ' . $record->date_range_end->format('M d, Y')),
                TextColumn::make('total_pounds')
                    ->label('Total Weight')
                    ->numeric(2)
                    ->suffix(' lbs')
                    ->sortable(),
                TextColumn::make('reimbursement_rate')
                    ->label('Rate')
                    ->money('USD')
                    ->suffix(' / lb')
                    ->sortable(),
                TextColumn::make('total_amount_owed')
                    ->label('Amount Owed')
                    ->money('USD')
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label('Paid Status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Paid Date')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'check' => 'Check',
                        'ach' => 'ACH Transfer',
                        'cash' => 'Cash',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->placeholder('-'),
            ])
            ->filters([
                TernaryFilter::make('is_paid')
                    ->label('Payment Status'),
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_date')->label('Period Start'),
                        DatePicker::make('end_date')->label('Period End'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start_date'], fn ($q) => $q->where('date_range_start', '>=', $data['start_date']))
                            ->when($data['end_date'], fn ($q) => $q->where('date_range_end', '<=', $data['end_date']));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                
                // Mark Payout Paid Action
                Action::make('markAsPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn (PayoutRecord $record) => !$record->is_paid)
                    ->form([
                        DatePicker::make('paid_at')
                            ->label('Date Paid')
                            ->default(now())
                            ->required(),
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'check' => 'Check',
                                'ach' => 'ACH Transfer',
                                'cash' => 'Cash',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label('Payment Notes')
                            ->nullable(),
                    ])
                    ->action(function (PayoutRecord $record, array $data) {
                        $record->update([
                            'is_paid' => true,
                            'paid_at' => $data['paid_at'],
                            'payment_method' => $data['payment_method'],
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Payout marked as paid.')
                            ->send();
                    }),

                // Single CSV Export Action
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(fn (PayoutRecord $record) => self::downloadCsv([$record])),
            ])
            ->toolbarActions([
                // Header Action: Generate Payouts
                Action::make('generatePayouts')
                    ->label('Generate Payouts')
                    ->icon('heroicon-o-cog')
                    ->color('amber')
                    ->form([
                        Select::make('customer_id')
                            ->label('Target Customer')
                            ->options(Customer::where('status', 'active')->pluck('name', 'id'))
                            ->placeholder('All Active Customers')
                            ->nullable(),
                        DatePicker::make('date_range_start')
                            ->label('Period Start Date')
                            ->default(Carbon::today()->startOfMonth())
                            ->required(),
                        DatePicker::make('date_range_end')
                            ->label('Period End Date')
                            ->default(Carbon::today()->endOfMonth())
                            ->required(),
                        TextInput::make('override_rate')
                            ->label('Override Price per Pound')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Leave blank to use location default rates')
                            ->nullable(),
                    ])
                    ->action(function (array $data) {
                        $start = $data['date_range_start'];
                        $end = $data['date_range_end'];
                        $overrideRate = $data['override_rate'] !== null ? (float) $data['override_rate'] : null;

                        // Identify target customers
                        $customersQuery = Customer::query();
                        if ($data['customer_id']) {
                            $customersQuery->where('id', $data['customer_id']);
                        } else {
                            $customersQuery->where('status', 'active');
                        }
                        $customers = $customersQuery->get();

                        $generatedCount = 0;

                        foreach ($customers as $customer) {
                            // Find completed pickup events for customer locations in date range
                            $pickups = PickupEvent::whereHas('location', function ($q) use ($customer) {
                                    $q->where('customer_id', $customer->id);
                                })
                                ->where('status', 'completed')
                                ->whereBetween(DB::raw('DATE(occurred_at)'), [$start, $end])
                                ->get();

                            $totalPounds = $pickups->sum('pounds_collected');
                            if ($totalPounds <= 0) {
                                continue;
                            }

                            // Calculate payout amount owed
                            if ($overrideRate !== null) {
                                $amountOwed = $totalPounds * $overrideRate;
                                $rateSnapshot = $overrideRate;
                            } else {
                                $amountOwed = 0.00;
                                foreach ($pickups as $pickup) {
                                    $locationRate = (float) ($pickup->location->reimbursement_rate ?? 0.00);
                                    $amountOwed += $pickup->pounds_collected * $locationRate;
                                }
                                $rateSnapshot = $totalPounds > 0 ? round($amountOwed / $totalPounds, 2) : 0.00;
                            }

                            // Prevent duplicates: Delete previous UNPAID payouts for this customer in this range
                            PayoutRecord::where('customer_id', $customer->id)
                                ->whereDate('date_range_start', $start)
                                ->whereDate('date_range_end', $end)
                                ->where('is_paid', false)
                                ->delete();

                            // Create payout record
                            PayoutRecord::create([
                                'customer_id' => $customer->id,
                                'date_range_start' => $start,
                                'date_range_end' => $end,
                                'total_pounds' => $totalPounds,
                                'reimbursement_rate' => $rateSnapshot,
                                'total_amount_owed' => round($amountOwed, 2),
                                'is_paid' => false,
                            ]);

                            $generatedCount++;
                        }

                        Notification::make()
                            ->success()
                            ->title("Payout records generation complete.")
                            ->body("Generated {$generatedCount} payout records.")
                            ->send();
                    }),

                // Bulk Action: Export selected payouts to CSV
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('exportBulkCsv')
                        ->label('Export Selected to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(fn (Collection $records) => self::downloadCsv($records->all())),
                ]),
            ]);
    }

    /**
     * Download collection of payout records as streamed CSV file response.
     */
    private static function downloadCsv(array $records): StreamedResponse
    {
        $filename = 'payouts-export-' . now()->format('Y-md-His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, [
                'Payout ID',
                'Customer ID',
                'Customer Name',
                'Period Start',
                'Period End',
                'Total Pounds Collected',
                'Reimbursement Rate',
                'Total Amount Owed',
                'Is Paid',
                'Paid At',
                'Payment Method',
                'Notes'
            ]);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->id,
                    $record->customer_id,
                    $record->customer->name,
                    $record->date_range_start->toDateString(),
                    $record->date_range_end->toDateString(),
                    $record->total_pounds,
                    $record->reimbursement_rate,
                    $record->total_amount_owed,
                    $record->is_paid ? 'Yes' : 'No',
                    $record->paid_at?->toDateString() ?? '',
                    $record->payment_method ?? '',
                    $record->notes ?? ''
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
