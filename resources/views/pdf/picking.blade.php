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
            font-bold: bold;
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
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            margin-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
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
                HOJA DE PICKING
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
                <th width="5%">N°</th>
                <th width="12%">CÓDIGO</th>
                <th>DESCRIPCIÓN DEL PRODUCTO</th>
                <th width="12%">U.M.</th>
                <th width="12%">CANTIDAD</th>
                <th width="12%">TOTAL</th>
                <th width="10%">PREPARADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items as $index => $item)
                @php 
                    $campos = json_decode($item->campos_json, true);
                    $plantilla = $pedido->cotizacion->plantilla->nombre;
                    
                    // CORRECCIÓN: Forzar la conversión de String (JSON) a Array
                    $despachosRaw = $pedido->cantidades_despachadas;
                    $despachos = is_string($despachosRaw) ? json_decode($despachosRaw, true) : ($despachosRaw ?? []);
                    
                    $tieneAjuste = array_key_exists($item->id, $despachos);

                    $isBackorder = str_contains($pedido->numero, '-');
                    if ($isBackorder && !$tieneAjuste) {
                        continue;
                    }

                    // Cantidad original (para calcular promedios si es necesario)
                    $fardosOriginales = ($plantilla === 'Bolsas de Polipropileno por kilos') 
                                        ? (float)($campos['cantidad_fardos'] ?? 1) 
                                        : (float)($campos['fardo'] ?? ($campos['cantidad'] ?? 1));

                    // Cantidad física final que se va a despachar (Fardos/Cajas/Sacos)
                    $cantidadFinal = $tieneAjuste ? (float)$despachos[$item->id] : $fardosOriginales;

                    // Valor derivado por defecto (Millares o Kilos originales)
                    $totalDerivado = $campos['total_kilos'] ?? ($campos['total_millares'] ?? 0);

                    // Unidad de Medida Técnica
                    $umTecnica = 'Und';
                    if($plantilla === 'Tratadas' || $plantilla === 'Pets') {
                        $umTecnica = 'Millares';
                    } elseif($plantilla === 'Bolsas de Polipropileno' || $plantilla === 'Bolsas de Polipropileno por kilos') {
                        $umTecnica = 'Kilos';
                    }

                    // RECÁLCULO PARA EL PICKING
                    if (in_array($plantilla, ['Tratadas', 'Pets'])) {
                        $cantidadPorMillar = (float) ($campos['cantidad_millar'] ?? 0);
                        $totalDerivado = $cantidadFinal * $cantidadPorMillar; 
                    } elseif ($plantilla === 'Bolsas de Polipropileno por kilos') {
                        $kilosOriginales = (float)($campos['total_kilos'] ?? 0);
                        $pesoPromedio = $fardosOriginales > 0 ? ($kilosOriginales / $fardosOriginales) : 0;
                        $totalDerivado = $cantidadFinal * $pesoPromedio;
                    } elseif ($plantilla === 'Universal') {
                        $totalDerivado = $cantidadFinal;
                        $umTecnica = $item->producto->unidad_medida ?? 'Und';
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->producto->codigo }}</td>
                    <td>
                        {{ $item->producto->nombre }}
                        @if($tieneAjuste)
                            <span style="color: red; font-size: 8px;">(Ajustado)</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $umTecnica }}</td>
                    <td style="text-align: center; font-weight: bold; background-color: #f9f9f9;">
                        {{ number_format((float)$cantidadFinal, 2) }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format((float)$totalDerivado, 2) }}
                    </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Preparado por:</p>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-line"></div>
            <p>Despachado por:</p>
        </div>
    </div>

    <div style="margin-top: 30px; font-style: italic; color: #666; font-size: 10px;">
        * Esta hoja de picking muestra las cantidades asignadas a este despacho específico (Pedido N° {{ $pedido->numero }}).
    </div>
</body>
</html>
