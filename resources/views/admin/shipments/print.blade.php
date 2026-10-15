<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thermal Label - {{ $shipment->tracking_number }}</title>

    <!-- Local Offline Assets -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">

    <style>
        body {
            background-color: #f1f5f9;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .thermal-label {
            width: 100mm;
            min-height: 148mm;
            background: #fff;
            margin: 0 auto;
            padding: 8mm;
            border: 2px solid #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 11px;
            line-height: 1.3;
        }

        .border-b-2 { border-bottom: 2px solid #000; }
        .border-t-2 { border-top: 2px solid #000; }
        .border-b-1 { border-bottom: 1px solid #000; }
        .border-r-1 { border-right: 1px solid #000; }

        .barcode-container {
            text-align: center;
            padding: 4px 0;
        }

        .routing-box {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 1px;
            text-align: center;
            background: #000;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .cod-box {
            border: 2px dashed #000;
            padding: 6px;
            text-align: center;
            font-weight: 900;
            background: #f8f8f8;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .thermal-label {
                width: 100mm !important;
                min-height: 148mm !important;
                margin: 0 auto !important;
                border: 2px solid #000 !important;
                box-shadow: none !important;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar (Hidden on Print) -->
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 fw-bold me-2">
            <i class="fa-solid fa-print me-1"></i> Print Thermal Label (4x6" / 100x150mm)
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary">
            Close Window
        </button>
    </div>

    <!-- 100x150mm Thermal Label Canvas -->
    <div class="thermal-label">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center pb-2 border-b-2">
            <div>
                <h4 class="fw-bolder mb-0 tracking-tight" style="letter-spacing: -0.5px;">COURIER<span class="text-danger">.</span></h4>
                <div style="font-size: 9px; font-weight: bold;">EXPRESS COURIER & LOGISTICS</div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark text-white px-2 py-1 fs-6 fw-bold">
                    {{ $shipment->service->code }}
                </span>
                <div style="font-size: 9px;">{{ $shipment->service->name }}</div>
            </div>
        </div>

        <!-- Routing Code & Date -->
        <div class="row g-0 py-2 border-b-2 align-items-center">
            <div class="col-8 pe-2">
                <div class="routing-box">
                    {{ $shipment->routing_code }}
                </div>
            </div>
            <div class="col-4 text-end" style="font-size: 10px;">
                <div><strong>HUB:</strong> {{ $shipment->originBranch?->code ?? 'CGK' }}</div>
                <div><strong>DEST:</strong> {{ $shipment->destinationBranch?->code ?? 'BDG' }}</div>
                <div>{{ $shipment->booking_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Barcode (JsBarcode offline) -->
        <div class="barcode-container border-b-2">
            <svg id="barcodeLabel"></svg>
        </div>

        <!-- Shipper & Recipient Section -->
        <div class="row g-0 border-b-2 py-2">
            <!-- Consignee (Receiver - Prominent) -->
            <div class="col-7 pe-2 border-r-1">
                <div style="font-size: 9px; font-weight: bold; text-transform: uppercase;">TO (PENERIMA):</div>
                <div style="font-size: 13px; font-weight: bold;">{{ $shipment->recipient_name }}</div>
                <div style="font-size: 11px; font-weight: bold;">{{ $shipment->recipient_phone }}</div>
                <div style="font-size: 10px; margin-top: 3px;">
                    {{ $shipment->recipient_address }}
                </div>
                <div style="font-size: 11px; font-weight: bold; margin-top: 2px;">
                    {{ $shipment->recipient_city }}, {{ $shipment->recipient_province }} {{ $shipment->recipient_postal_code }}
                </div>
            </div>

            <!-- Shipper (Sender) -->
            <div class="col-5 ps-2">
                <div style="font-size: 9px; font-weight: bold; text-transform: uppercase;">FROM (PENGIRIM):</div>
                <div style="font-size: 11px; font-weight: bold;">{{ $shipment->sender_name }}</div>
                <div style="font-size: 10px;">{{ $shipment->sender_phone }}</div>
                <div style="font-size: 9px; margin-top: 2px;">
                    {{ $shipment->sender_address }}, {{ $shipment->sender_city }}
                </div>
            </div>
        </div>

        <!-- COD Box (If Applicable) -->
        @if($shipment->isCod())
            <div class="my-2 cod-box">
                <div style="font-size: 11px; letter-spacing: 1px;">CASH ON DELIVERY (COD)</div>
                <div style="font-size: 18px;">Rp {{ number_format($shipment->cod_amount) }}</div>
                <div style="font-size: 9px; font-weight: normal;">Collect exact cash from recipient prior to parcel handover</div>
            </div>
        @endif

        <!-- Package Specs & Financials -->
        <div class="row g-0 py-2 border-b-2" style="font-size: 10px;">
            <div class="col-4">
                <div><strong>Charged Wt:</strong> {{ $shipment->chargeable_weight_kg }} Kg</div>
                <div><strong>Act. Wt:</strong> {{ $shipment->actual_weight_kg }} Kg</div>
                <div><strong>Dims:</strong> {{ $shipment->length_cm }}x{{ $shipment->width_cm }}x{{ $shipment->height_cm }} cm</div>
            </div>
            <div class="col-4 border-r-1 border-start px-2">
                <div><strong>Pieces:</strong> {{ $shipment->piece_count }} Pcs</div>
                <div><strong>Type:</strong> {{ ucfirst($shipment->package_type) }}</div>
                <div class="text-truncate"><strong>Content:</strong> {{ $shipment->package_description ?? 'Goods' }}</div>
            </div>
            <div class="col-4 text-end ps-2">
                <div><strong>Payment:</strong> {{ strtoupper($shipment->payment_method) }}</div>
                <div><strong>Freight:</strong> Rp {{ number_format($shipment->shipping_cost) }}</div>
                <div><strong>Total:</strong> Rp {{ number_format($shipment->total_cost) }}</div>
            </div>
        </div>

        <!-- Special Instructions -->
        @if($shipment->special_instructions)
            <div class="py-1 border-b-1" style="font-size: 9px;">
                <strong>Remarks:</strong> {{ $shipment->special_instructions }}
            </div>
        @endif

        <!-- Handover Signatures -->
        <div class="row g-0 pt-2 text-center" style="font-size: 9px;">
            <div class="col-6 border-r-1 pe-2">
                <div>Courier Pick-up / Signature:</div>
                <div style="height: 35px;"></div>
                <div class="border-top pt-1">( ____________________ )</div>
            </div>
            <div class="col-6 ps-2">
                <div>Recipient Signature & Date:</div>
                <div style="height: 35px;"></div>
                <div class="border-top pt-1">( ____________________ )</div>
            </div>
        </div>

        <div class="text-center mt-2 pt-1 border-t-2" style="font-size: 8px;">
            Tracking available on internal network & online portal: {{ config('app.url') }}/track/{{ $shipment->tracking_number }}
        </div>
    </div>

    <!-- Local Offline JS -->
    <script src="{{ asset('assets/vendor/jsbarcode.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode("#barcodeLabel", "{{ $shipment->tracking_number }}", {
                    format: "CODE128",
                    width: 2,
                    height: 50,
                    displayValue: true,
                    fontSize: 14,
                    margin: 5
                });
            }
        });
    </script>
</body>
</html>
