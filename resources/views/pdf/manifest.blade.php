@php
    $getImageDataUri = function(?string $path) {
        if (!$path) return null;
        if (str_starts_with($path, 'data:image')) return $path;
        
        $cleanPath = ltrim($path, '/');
        
        // 1. Check storage/app/public/
        $storagePath = storage_path('app/public/' . $cleanPath);
        if (file_exists($storagePath)) {
            $ext = pathinfo($storagePath, PATHINFO_EXTENSION) ?: 'png';
            $data = @file_get_contents($storagePath);
            if ($data !== false) {
                return 'data:image/' . $ext . ';base64,' . base64_encode($data);
            }
        }
        
        // 2. Check public/storage/
        $publicPath = public_path('storage/' . $cleanPath);
        if (file_exists($publicPath)) {
            $ext = pathinfo($publicPath, PATHINFO_EXTENSION) ?: 'png';
            $data = @file_get_contents($publicPath);
            if ($data !== false) {
                return 'data:image/' . $ext . ';base64,' . base64_encode($data);
            }
        }

        // 3. Fallback to public storage URL
        return \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
    };

    $driverSigSrc = $getImageDataUri($pickupEvent->driver_signature_path);
    $customerSigSrc = $getImageDataUri($pickupEvent->customer_signature_path);
    $hasPhotos = !empty($pickupEvent->photos) && is_array($pickupEvent->photos);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collection Manifest #{{ $pickupEvent->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px 24px;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .company-title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
        }
        .manifest-title {
            font-size: 18px;
            font-weight: bold;
            color: #f59e0b;
            text-align: right;
        }
        .box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .box-bottom-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px;
            height: 120px;
            box-sizing: border-box;
        }
        .box-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
            height: 16px;
            line-height: 16px;
            white-space: nowrap;
            overflow: hidden;
        }
        .box-subtitle {
            font-size: 11px;
            color: #64748b;
            height: 16px;
            line-height: 16px;
            white-space: nowrap;
            overflow: hidden;
            margin-bottom: 4px;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-table td {
            padding: 6px 0;
            font-size: 14px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #475569;
            width: 140px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
            font-size: 14px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #334155;
        }
        .img-slot {
            height: 64px;
            line-height: 64px;
            text-align: center;
            vertical-align: middle;
        }
        .footer-note {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
        }
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">JNC GreaseCycling</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">Used Cooking Oil & Grease Recycling Services</div>
            </td>
            <td style="text-align: right;">
                <div class="manifest-title">COLLECTION MANIFEST</div>
                <div style="font-size: 12px; color: #475569; margin-top: 3px;">Manifest #: <strong>JNC-MN-{{ str_pad($pickupEvent->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
                <div style="font-size: 12px; color: #64748b;">Date: <strong>{{ $pickupEvent->occurred_at->format('M d, Y g:i A') }}</strong></div>
            </td>
        </tr>
    </table>

    <div class="box">
        <div class="box-title">Point of Origin & Customer Details</div>
        <table class="grid-table">
            <tr>
                <td class="label">Customer Name:</td>
                <td>{{ $pickupEvent->location->customer->name ?? 'N/A' }}</td>
                <td class="label">Location Name:</td>
                <td>{{ $pickupEvent->location->name }}</td>
            </tr>
            <tr>
                <td class="label">Service Address:</td>
                <td>{{ $pickupEvent->location->service_address }}</td>
                <td class="label">Bin / Tank Size:</td>
                <td>{{ $pickupEvent->location->bin_size_gallons ? $pickupEvent->location->bin_size_gallons . ' Gallons' : 'Standard' }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Collection Details & Weights</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Material Collected</th>
                    <th>Gross Weight</th>
                    <th>Tare Weight</th>
                    <th>Net Quantity Collected</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>{{ strtoupper(str_replace('_', ' ', $pickupEvent->material_type ?? 'used_cooking_oil')) }}</strong></td>
                    <td>{{ $pickupEvent->gross_weight ? number_format($pickupEvent->gross_weight, 2) . ' lbs' : '-' }}</td>
                    <td>{{ $pickupEvent->tare_weight ? number_format($pickupEvent->tare_weight, 2) . ' lbs' : '-' }}</td>
                    <td><strong style="color: #10b981; font-size: 16px;">{{ number_format($pickupEvent->pounds_collected, 2) }} {{ $pickupEvent->unit ?? 'lbs' }}</strong></td>
                    <td>
                        <span style="text-transform: uppercase; font-weight: bold; color: {{ $pickupEvent->status === 'completed' ? '#10b981' : '#ef4444' }};">
                            {{ $pickupEvent->status }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        @if($pickupEvent->notes)
            <div style="margin-top: 10px; font-size: 13px; color: #475569;">
                <strong>Driver Notes:</strong> {{ $pickupEvent->notes }}
            </div>
        @endif
    </div>

    <div class="box">
        <div class="box-title">Transport & Collector Information</div>
        <table class="grid-table">
            <tr>
                <td class="label">Assigned Driver:</td>
                <td>{{ $pickupEvent->driver->name ?? 'System Driver' }}</td>
                <td class="label">Route Name:</td>
                <td>{{ $pickupEvent->route->name ?? 'Standard Route' }}</td>
            </tr>
            <tr>
                <td class="label">Assigned Vehicle:</td>
                <td>{{ $pickupEvent->route->vehicle->name ?? 'Default Collection Unit' }}</td>
                <td class="label">Timestamp:</td>
                <td>{{ $pickupEvent->occurred_at->toDateTimeString() }}</td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
        <tr>
            @if($hasPhotos)
            <td style="width: 32%; vertical-align: top; padding-right: 10px;">
                <div class="box-bottom-card">
                    <div class="box-title">Site Photos & Proof</div>
                    <div class="box-subtitle">Verification Photo</div>
                    <div class="img-slot">
                        @foreach($pickupEvent->photos as $photoPath)
                            @php $photoSrc = $getImageDataUri($photoPath); @endphp
                            @if($photoSrc)
                            <div style="display: inline-block; margin: 0 2px; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #0f172a; vertical-align: middle;">
                                <img src="{{ $photoSrc }}" style="height: 58px; width: 58px; object-fit: cover; display: block;" alt="Site Photo" />
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </td>
            @endif
            <td style="vertical-align: top; {{ $hasPhotos ? 'padding-right: 10px;' : 'padding-right: 10px; width: 48%;' }}">
                <div class="box-bottom-card">
                    <div class="box-title">Driver Authorization</div>
                    <div class="box-subtitle">Driver: {{ $pickupEvent->driver->name ?? 'Driver' }}</div>
                    <div class="img-slot">
                        @if($driverSigSrc)
                            <img src="{{ $driverSigSrc }}" style="max-width: 100%; height: 58px; object-fit: contain; vertical-align: middle;" alt="Driver Signature" />
                        @else
                            <span style="color: #cbd5e1; font-style: italic; font-size: 11px;">[ Electronically Verified ]</span>
                        @endif
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; {{ $hasPhotos ? '' : 'width: 48%;' }}">
                <div class="box-bottom-card">
                    <div class="box-title">Customer Acknowledgment</div>
                    <div class="box-subtitle">Customer Representative</div>
                    <div class="img-slot">
                        @if($customerSigSrc)
                            <img src="{{ $customerSigSrc }}" style="max-width: 100%; height: 58px; object-fit: contain; vertical-align: middle;" alt="Customer Signature" />
                        @else
                            <span style="color: #cbd5e1; font-style: italic; font-size: 11px;">[ Representative ]</span>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        This document serves as an official proof of collection and regulatory manifest for JNC GreaseCycling.<br/>
        For questions or support, contact support@jncgreasecycling.com.
    </div>

    @if(request('print') || request('download'))
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>
</html>
