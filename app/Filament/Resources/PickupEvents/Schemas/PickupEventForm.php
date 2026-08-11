<?php

namespace App\Filament\Resources\PickupEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class PickupEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('location_id')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('driver_id')
                    ->label('Driver')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('route_id')
                    ->relationship('route', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                DateTimePicker::make('occurred_at')
                    ->label('Collection Date & Time')
                    ->required(),
                Select::make('material_type')
                    ->label('Material Type')
                    ->options([
                        'used_cooking_oil' => 'Used Cooking Oil (UCO)',
                        'grease' => 'Yellow / Brown Grease',
                        'tallow' => 'Tallow',
                        'other' => 'Other / Feedstocks',
                    ])
                    ->default('used_cooking_oil')
                    ->required(),
                Select::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'skipped' => 'Skipped',
                    ])
                    ->required(),
                TextInput::make('gross_weight')
                    ->label('Gross Weight (lbs)')
                    ->numeric()
                    ->default(null),
                TextInput::make('tare_weight')
                    ->label('Tare Weight (lbs)')
                    ->numeric()
                    ->default(null),
                TextInput::make('pounds_collected')
                    ->label('Net Pounds Collected')
                    ->numeric()
                    ->required(),
                TextInput::make('skip_reason')
                    ->label('Skip Reason')
                    ->default(null),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Placeholder::make('photos_preview')
                    ->label('Site Photos & Proof')
                    ->content(function ($record) {
                        if ($record && !empty($record->photos) && is_array($record->photos)) {
                            $html = '<div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 4px;">';
                            foreach ($record->photos as $photoPath) {
                                $url = Storage::disk('public')->url($photoPath);
                                $html .= "<a href='{$url}' target='_blank' style='border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; display: inline-block; background: #0f172a;'><img src='{$url}' style='height: 120px; width: 120px; object-fit: cover;' alt='Site Photo'/></a>";
                            }
                            $html .= '</div>';
                            return new HtmlString($html);
                        }
                        return new HtmlString('<span class="text-gray-400">No site photos attached</span>');
                    })
                    ->columnSpanFull(),
                Placeholder::make('driver_signature')
                    ->label('Driver Signature')
                    ->content(function ($record) {
                        if ($record && $record->driver_signature_path) {
                            $url = Storage::disk('public')->url($record->driver_signature_path);
                            return new HtmlString("<div style='background:#0f172a; padding:10px; border-radius:8px; display:inline-block;'><img src='{$url}' style='max-height:80px;' alt='Driver Signature'/></div>");
                        }
                        return new HtmlString('<span class="text-gray-400">No driver signature on file</span>');
                    }),
                Placeholder::make('customer_signature')
                    ->label('Customer Signature')
                    ->content(function ($record) {
                        if ($record && $record->customer_signature_path) {
                            $url = Storage::disk('public')->url($record->customer_signature_path);
                            return new HtmlString("<div style='background:#0f172a; padding:10px; border-radius:8px; display:inline-block;'><img src='{$url}' style='max-height:80px;' alt='Customer Signature'/></div>");
                        }
                        return new HtmlString('<span class="text-gray-400">No customer signature on file</span>');
                    }),
            ]);
    }
}
