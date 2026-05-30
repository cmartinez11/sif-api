<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Picking - {{ $pedido->numero }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo {
            width: 150px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 11px;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
            display: table;
        }
        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="30%">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @else
                    <h1 style="color: #27E86D; margin: 0;">PLÁSTICOS FÉNIX</h1>
                @endif
            </td>
            <td width="40%" class="title">
                HOJA DE PICKING<br>
                <span style="font-size: 14px; font-weight: normal; text-decoration: none;">(Tratadas)</span>
            </td>
            <td width="30%" style="text-align: right;">
                <p style="margin: 0; font-weight: bold;">PEDIDO N°: {{ $pedido->numero }}</p>
                <p style="margin: 0;">Fecha: {{ date('d/m/Y') }}</p>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Cliente:</strong></td>
            <td>{{ $pedido->cotizacion->cliente->nombre }}</td>
            <td width="15%"><strong>RUC:</strong></td>
            <td>{{ $pedido->cotizacion->cliente->ruc }}</td>
        </tr>
        <tr>
            <td><strong>Vendedor:</strong></td>
            <td>{{ $pedido->cotizacion->vendedor->name ?? 'N/A' }}</td>
            <td><strong>Fecha de Despacho:</strong></td>
            <td>{{ $pedido->fecha_entrega_confirmada ? \Carbon\Carbon::parse($pedido->fecha_entrega_confirmada)->format('d/m/Y') : 'Por confirmar' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="15%">CÓDIGO</th>
                <th>PRODUCTO</th>
                <th width="12%" style="text-align: center;">CANTIDAD</th>
                <th width="12%" style="text-align: center;">U/M</th>
                <th width="18%" style="background-color: #e6fffa; color: #0f5132; text-align: center; font-size: 10px;">CANTIDAD A DESPACHAR (FARDOS)</th>
                <th width="15%" style="text-align: center;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items as $item)
                @php 
                    $campos = json_decode($item->campos_json, true);
                    
                    $despachosRaw = $pedido->cantidades_despachadas;
                    $despachos = is_string($despachosRaw) ? json_decode($despachosRaw, true) : ($despachosRaw ?? []);
                    $isBackorder = str_contains($pedido->numero, '-');
                    $tieneAjuste = false;
                    $ajusteQty = null;

                    if (is_array($despachos) && count($despachos) > 0) {
                        if (array_key_exists($item->id, $despachos)) {
                            $tieneAjuste = true;
                            $ajusteQty = (float) $despachos[$item->id];
                        } else {
                            static $prodMap = null;
                            if ($prodMap === null) {
                                $prodMap = [];
                                if ($pedido->cotizacion && $pedido->cotizacion->items) {
                                    foreach ($pedido->cotizacion->items as $cotItem) {
                                        if (array_key_exists($cotItem->id, $despachos)) {
                                            $prodMap[$cotItem->producto_id] = $despachos[$cotItem->id];
                                        }
                                    }
                                }
                            }
                            if (array_key_exists($item->producto_id, $prodMap)) {
                                $tieneAjuste = true;
                                $ajusteQty = (float) $prodMap[$item->producto_id];
                            }
                        }
                    } else {
                        if ($isBackorder) {
                            $tieneAjuste = true;
                        }
                    }

                    if ($isBackorder && !$tieneAjuste) {
                        continue;
                    }

                    $fardosOriginales = (float)($campos['fardo'] ?? 0);
                    $cantidadFinal = ($ajusteQty !== null) ? $ajusteQty : $fardosOriginales;

                    $cantidadPorMillar = (float)($campos['cantidad_millar'] ?? 0);
                    $totalDerivado = $cantidadFinal * $cantidadPorMillar; 

                    $umLogistica = $item->producto->unidad_medida_logistica ?? 'Millares';
                @endphp
                <tr>
                    <td>{{ $item->producto->codigo }}</td>
                    <td>
                        {{ $item->producto->nombre }}
                        @if($tieneAjuste)
                            <span style="color: red; font-size: 8px;">(Ajustado)</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold; background-color: #f9f9f9;">
                        {{ number_format((float)$cantidadFinal, 2) }}
                    </td>
                    <td style="text-align: center;">{{ $umLogistica }}</td>
                    <td style="text-align: center; font-weight: bold; background-color: #e6fffa; color: #0f5132; border: 1px solid #badbcc;">
                        {{ number_format($item->cantidad_fardos_picking, 2) }} {{ $item->producto->unidad_medida_logistica ?: 'FARDOS' }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format((float)$totalDerivado, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Preparado por:</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Chofer:</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Despachado por:</p>
        </div>
    </div>

    <div style="margin-top: 30px; font-style: italic; color: #666; font-size: 10px;">
        * Esta hoja de picking muestra las cantidades asignadas a este despacho específico (Pedido N° {{ $pedido->numero }}).
    </div>
</body>
</html>
