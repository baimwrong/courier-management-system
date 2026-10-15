<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Courier Run-Sheet - {{ $manifest->manifest_number }}</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">

    <style>
        body {
            background-color: #f8fafc;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            padding: 20px;
        }

        .print-canvas {
            max-width: 210mm; /* A4 */
            margin: 0 auto;
            background: #fff;
            padding: 15mm;
            border: 1px solid #ccc;
        }

        .table-print th, .table-print td {
            border: 1px solid #000 !important;
            padding: 4px 6px;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-canvas {
                max-width: 100% !important;
                border: none !important;
                padding: 5mm !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 fw-bold me-2">
            <i class="fa-solid fa-print me-1"></i> Print Courier Run-Sheet / Handover (A4)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary">Close</button>
    </div>

    <div class="print-canvas">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
            <div>
                <h3 class="fw-bolder mb-0">COURIER LOGISTICS</h3>
                <div class="fw-bold text-uppercase" style="font-size: 13px;">COURIER DELIVERY RUN-SHEET & HANDOVER MANIFEST</div>
                <div class="small text-muted">Nationwide Logistics Network &bull; Intranet Operations</div>
            </div>
            <div class="text-end">
                <svg id="manifestBarcode"></svg>
                <div class="fw-bold">{{ $manifest->manifest_number }}</div>
            </div>
        </div>

        <!-- Meta Grid -->
        <table class="table table-sm table-borderless mb-3 small">
            <tr>
                <td style="width: 15%;"><strong>Origin Node:</strong></td>
                <td style="width: 35%;">{{ $manifest->originBranch?->name }} ({{ $manifest->originBranch?->code }})</td>
                <td style="width: 15%;"><strong>Dispatch Date:</strong></td>
                <td style="width: 35%;">{{ $manifest->created_at->format('d/m/Y H:i') }} WIB</td>
            </tr>
            <tr>
                <td><strong>Destination:</strong></td>
                <td>{{ $manifest->destinationBranch?->name ?? 'Local Delivery Territory' }}</td>
                <td><strong>Courier / Driver:</strong></td>
                <td>{{ $manifest->courier?->name ?? 'Unassigned' }}</td>
            </tr>
            <tr>
                <td><strong>Vehicle Plate:</strong></td>
                <td>{{ $manifest->vehicle_plate ?? 'N/A' }} ({{ ucfirst($manifest->vehicle_type ?? 'Van') }})</td>
                <td><strong>Total Packages:</strong></td>
                <td><strong>{{ $manifest->total_shipments }} pkgs</strong> ({{ $manifest->total_weight_kg }} Kg)</td>
            </tr>
        </table>

        <!-- Packages Table -->
        <table class="table table-print align-middle mb-4">
            <thead>
                <tr class="table-light text-uppercase" style="font-size: 9px;">
                    <th style="width: 25px;">#</th>
                    <th style="width: 120px;">AWB / Resi</th>
                    <th>Recipient (Name & Phone)</th>
                    <th>Delivery Address</th>
                    <th style="width: 45px;">Weight</th>
                    <th style="width: 75px;">COD Amount</th>
                    <th style="width: 110px;">Recipient Sign & Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manifest->items as $idx => $it)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong style="font-size: 11px;">{{ $it->shipment->tracking_number }}</strong>
                            <div style="font-size: 8px;">{{ $it->shipment->service->code }} &bull; {{ $it->shipment->routing_code }}</div>
                        </td>
                        <td>
                            <strong>{{ $it->shipment->recipient_name }}</strong>
                            <div>{{ $it->shipment->recipient_phone }}</div>
                        </td>
                        <td style="font-size: 9px;">
                            {{ $it->shipment->recipient_address }}, {{ $it->shipment->recipient_city }}
                        </td>
                        <td class="text-center">{{ $it->shipment->chargeable_weight_kg }}k</td>
                        <td class="text-end">
                            @if($it->shipment->isCod())
                                <strong>Rp {{ number_format($it->shipment->cod_amount) }}</strong>
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td style="height: 38px;"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures & Handover -->
        <div class="row text-center mt-4 pt-3 border-top" style="font-size: 10px;">
            <div class="col-4">
                <div>Prepared by (Dispatcher):</div>
                <div style="height: 45px;"></div>
                <div>( _____________________ )</div>
            </div>
            <div class="col-4">
                <div>Accepted by (Courier/Driver):</div>
                <div style="height: 45px;"></div>
                <div>( {{ $manifest->courier?->name ?? '_____________________' }} )</div>
            </div>
            <div class="col-4">
                <div>Receiving Hub Manager:</div>
                <div style="height: 45px;"></div>
                <div>( _____________________ )</div>
            </div>
        </div>
    </div>

    <!-- Local Offline JS -->
    <script src="{{ asset('assets/vendor/jsbarcode.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode("#manifestBarcode", "{{ $manifest->manifest_number }}", {
                    format: "CODE128",
                    width: 1.6,
                    height: 35,
                    displayValue: false,
                    margin: 2
                });
            }
        });
    </script>
</body>
</html>
