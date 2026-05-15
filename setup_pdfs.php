<?php

$dir = __DIR__ . '/resources/views/pdf';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$baseLayoutP1 = <<<'EOD'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotización</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header-table { w-full; margin-bottom: 20px; }
        .text-green { color: #1a472a; }
        .bg-green { background-color: #1a472a; color: #FFF; }
        .bg-gold { background-color: #FFD700; color: #000; font-weight: bold; }
        .border-box { border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        table th { background-color: #1a472a; color: white; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .logo-box { width: 100px; height: 100px; background: #eee; text-align: center; line-height: 100px; margin-bottom: 5px; }
        .green-stripe { background: #1a472a; color: white; padding: 8px; border-radius: 4px; }
        .footer-table { width: 100%; margin-top: 30px; font-size: 11px; }
        .footer-left { width: 50%; padding-right: 15px; }
        .footer-right { width: 50%; padding-left: 15px; text-align: center; }
        .totals-box { width: 250px; float: right; border: 1px solid #ddd; }
        .totals-row { padding: 5px; border-bottom: 1px solid #ddd; clear: both; overflow: hidden; }
        .totals-row:last-child { border-bottom: none; }
        .totals-label { float: left; width: 60%; }
        .totals-value { float: right; width: 40%; text-align: right; }
    </style>
</head>
<body>
    <table style="width: 100%; border: none; margin-bottom: 10px;">
        <tr>
            <td style="border: none; width: 50%;">
                <div class="logo-box">Grupo Fénix</div>
            </td>
            <td style="border: none; width: 50%; text-align: right;">
                <h1 class="text-green" style="margin: 0;">COTIZACIÓN</h1>
                <h3 style="margin: 5px 0;">N° - {{ $cotizacion->numero }}</h3>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border: none;">
        <tr>
            <td class="bg-green" style="border: none; width: 60%; padding: 10px; border-radius: 5px 0 0 5px;">
                <strong style="font-size: 14px;">RUC: 20522086704</strong><br>
                Dirección empresa
            </td>
            <td class="bg-green" style="border: none; width: 40%; text-align: right; padding: 10px; border-radius: 0 5px 5px 0;">
                <span style="font-size: 20px;">📅</span> FECHA DE EMISIÓN<br>
                <strong>{{ \Carbon\Carbon::parse($cotizacion->fecha_emision)->format('d \d\e F \d\e Y') }}</strong>
            </td>
        </tr>
    </table>

    <div class="border-box" style="background: #f5f5f5; margin-bottom: 20px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td colspan="2" style="border: none; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 10px; font-size: 14px; color: #333;">
                    <strong style="color: #1a472a">CLIENTE:</strong> {{ strtoupper($cotizacion->cliente->nombre) }}
                </td>
            </tr>
            <tr>
                <td style="border: none; width: 50%;">
                    <strong>RUC:</strong> {{ $cotizacion->cliente->ruc }}<br>
                    <strong>DIRECCIÓN:</strong> {{ $cotizacion->cliente->direccion ?? 'N/A' }}<br>
                    <strong>CONDICIÓN PAGO:</strong> {{ $cotizacion->cliente->condicion_pago ?? 'CONTADO' }}
                </td>
                <td style="border: none; width: 50%;">
                    <strong>PROVINCIA:</strong> {{ $cotizacion->cliente->provincia ?? 'N/A' }}<br>
                    <strong>MONEDA:</strong> {{ strtoupper($cotizacion->moneda) }}<br>
                    <strong>ATENDIDO POR:</strong> {{ $cotizacion->vendedora->name ?? 'Vendedora' }}
                </td>
            </tr>
        </table>
    </div>

EOD;

$baseLayoutP2 = <<<'EOD'

    <div style="clear: both; margin-bottom: 40px;">
        <div class="totals-box">
            <div class="totals-row">
                <div class="totals-label">SUB TOTAL</div>
                <div class="totals-value">{{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($cotizacion->subtotal, 2) }}</div>
            </div>
            <div class="totals-row">
                <div class="totals-label">IGV 18%</div>
                <div class="totals-value">{{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($cotizacion->igv, 2) }}</div>
            </div>
            <div class="totals-row bg-gold">
                <div class="totals-label">TOTAL</div>
                <div class="totals-value">{{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($cotizacion->total, 2) }}</div>
            </div>
        </div>
    </div>

    <table class="footer-table" style="border: none;">
        <tr>
            <td class="footer-left" style="border: none; border-top: 1px solid #ccc;">
                <h4 class="text-green" style="margin-bottom: 5px;">CUENTAS BANCARIAS</h4>
                <strong>BCP SOLES:</strong> 191-00000000-0-00<br>
                <strong>BCP DÓLARES:</strong> 191-00000000-1-00
            </td>
            <td class="footer-right" style="border: none; border-top: 1px solid #ccc; background: #fafafa;">
                <strong style="color: #666;">"TU MARCA SIEMPRE RELEVANTE"</strong><br><br>
                Síguenos: @plasticosfenix<br>
                comercial@plasticosfenix.com
            </td>
        </tr>
    </table>
</body>
</html>
EOD;

$pdfTratadas = $baseLayoutP1 . <<<'EOD'
    <table>
        <thead>
            <tr>
                <th style="width: 5%">ITEM</th>
                <th style="width: 15%">CÓDIGO</th>
                <th style="width: 25%; text-align: left;">PRODUCTO</th>
                <th style="width: 10%">CANT.xMILLAR</th>
                <th style="width: 10%">FARDO</th>
                <th style="width: 10%">TOT.MILLARES</th>
                <th style="width: 10%">P.UNIT</th>
                <th style="width: 15%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->items as $index => $item)
            @php $dt = json_decode($item->campos_json); @endphp
            <tr style="background-color: {{ $index % 2 == 0 ? '#fff' : '#f9f9f9' }};">
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->producto->codigo ?? '' }}</td>
                <td>{{ $item->producto->nombre ?? '' }}</td>
                <td class="text-center">{{ $dt->cantidad_millar ?? 0 }}</td>
                <td class="text-center">{{ $dt->fardo ?? 0 }}</td>
                <td class="text-right">{{ number_format($dt->total_millares ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->precio_unitario, 5) }}</td>
                <td class="text-right">{{ number_format($item->precio_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
EOD;
$pdfTratadas .= $baseLayoutP2;

file_put_contents(__DIR__ . '/resources/views/pdf/cotizacion-tratadas.blade.php', $pdfTratadas);
// Placeholder for others: pps, pets, universal (using same base)
file_put_contents(__DIR__ . '/resources/views/pdf/cotizacion-pps.blade.php', $pdfTratadas);
file_put_contents(__DIR__ . '/resources/views/pdf/cotizacion-pets.blade.php', $pdfTratadas);
file_put_contents(__DIR__ . '/resources/views/pdf/cotizacion-universal.blade.php', $pdfTratadas);

echo "PDF Views generated.";
