<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('service_address')
                    ->live()
                    ->extraAlpineAttributes([
                        'x-init' => "
                            if (window.google) {
                                const autocomplete = new google.maps.places.Autocomplete(\$el.querySelector('input') || \$el, {
                                    types: ['address']
                                });
                                autocomplete.addListener('place_changed', () => {
                                    const place = autocomplete.getPlace();
                                    if (!place.geometry) return;
                                    
                                    \$wire.set('data.service_address', place.formatted_address);
                                    if (place.url) {
                                        \$wire.set('data.map_link', place.url);
                                    } else {
                                        const lat = place.geometry.location.lat();
                                        const lng = place.geometry.location.lng();
                                        \$wire.set('data.map_link', `https://www.google.com/maps/search/?api=1&query=\${lat},\${lng}`);
                                    }
                                });
                            }
                        "
                    ])
                    ->required(),
                TextInput::make('map_link')
                    ->label('Google Maps Link (or Lat/Lng)')
                    ->url()
                    ->default(null),
                Placeholder::make('map_preview')
                    ->label('Map Preview')
                    ->content(function ($get) {
                        $address = $get('service_address');
                        if (!$address) {
                            return new HtmlString('<div class="text-gray-400 text-sm">Enter a service address to view the map preview.</div>');
                        }
                        
                        $query = urlencode($address);
                        $src = "https://maps.google.com/maps?q={$query}&z=15&output=embed";
                        
                        return new HtmlString("
                            <div style='width: 100%; border-radius: 0.5rem; overflow: hidden; border: 1px solid #e2e8f0;' class='dark:border-slate-700'>
                                <iframe
                                    width='100%'
                                    height='300'
                                    frameborder='0'
                                    style='border:0; display: block;'
                                    src='{$src}'
                                    allowfullscreen
                                ></iframe>
                            </div>
                        ");
                    })
                    ->columnSpanFull(),
                Textarea::make('special_instructions')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('service_frequency')
                    ->options(['weekly' => 'Weekly', 'biweekly' => 'Biweekly', 'monthly' => 'Monthly', 'on_call' => 'On Call'])
                    ->default('weekly')
                    ->required(),
                TextInput::make('reimbursement_rate')
                    ->label('Reimbursement Rate (per pound)')
                    ->numeric()
                    ->prefix('$')
                    ->default(0.00)
                    ->required(),
                Select::make('status')
                    ->options(['active' => 'Active', 'paused' => 'Paused', 'cancelled' => 'Cancelled'])
                    ->default('active')
                    ->required(),
                Select::make('default_route_id')
                    ->relationship('defaultRoute', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
            ]);
    }
}
