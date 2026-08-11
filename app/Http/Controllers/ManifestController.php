<?php

namespace App\Http\Controllers;

use App\Models\PickupEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * Show print-ready HTML collection manifest.
     */
    public function show(PickupEvent $pickupEvent)
    {
        $pickupEvent->load(['location.customer', 'route.vehicle', 'driver']);

        return view('pdf.manifest', [
            'pickupEvent' => $pickupEvent,
        ]);
    }

    /**
     * Download direct PDF manifest file.
     */
    public function download(PickupEvent $pickupEvent)
    {
        $pickupEvent->load(['location.customer', 'route.vehicle', 'driver']);

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('pdf.manifest', ['pickupEvent' => $pickupEvent])
                ->setPaper('a4', 'portrait')
                ->setOption([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => [public_path(), storage_path('app/public')],
                ]);
            $filename = 'Manifest-JNC-MN-' . str_pad($pickupEvent->id, 6, '0', STR_PAD_LEFT) . '.pdf';
            return $pdf->download($filename);
        }

        return redirect()->route('manifest.show', ['pickupEvent' => $pickupEvent->id, 'download' => 1]);
    }
}
