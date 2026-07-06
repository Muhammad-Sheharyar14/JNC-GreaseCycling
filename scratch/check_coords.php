<?php

use App\Models\Location;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

$kernel = app(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$loc = Location::where('name', 'test location 1')->first();
if ($loc) {
    echo "Location: {$loc->name}\n";
    echo "  Map Link: " . var_export($loc->map_link, true) . "\n";
} else {
    echo "Location not found!\n";
}
