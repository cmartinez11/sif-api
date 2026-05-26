<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cierre Diario - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
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
            font-size: 18px;
            font-weight: bold;
            color: #047857; /* green-700 */
        }
        .meta-info {
            text-align: right;
            font-size: 10px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .report-table th {
            background-color: #059669; /* emerald-600 */
            color: #ffffff;
            border: 1px solid #047857;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .report-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-blue {
            color: #2563eb; /* blue-600 */
        }
        .text-red {
            color: #dc2626; /* red-600 */
        }
        .bg-red-light {
            background-color: #fee2e2; /* red-100 */
        }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #666;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="30%">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @else
                    <h1 style="color: #059669; margin: 0; font-size: 20px;">PLÁSTICOS FÉNIX</h1>
                @endif
            </td>
            <td width="40%" class="title">
                AUDITORÍA DE CIERRE DIARIO<br>
                <span style="font-size: 11px; font-weight: normal; color: #666;">Control de Stock de 24 Horas</span>
            </td>
            <td width="30%" class="meta-info">
                <p style="margin: 0; font-weight: bold;">FECHA: {{ date('d/m/Y') }}</p>
                <p style="margin: 0;">Generado: {{ date('H:i:s') }}</p>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th width="10%">Código</th>
                <th>Producto</th>
                <th width="12%">Línea</th>
                <th width="6%" class="text-center">U/M</th>
                <th width="14%" class="text-right">Subido Hoy</th>
                <th width="14%" class="text-right">Vendido Hoy</th>
                <th width="14%" class="text-right">Saldo SIF</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productosReporte as $producto)
                @php
                    $stockActual = (float) $producto->stock;
                    $vendidoHoy = (float) $producto->vendido_hoy;
                    $deudaArrastrada = (float) ($producto->deuda_arrastrada ?? 0.000);
                    $subidoHoy = $stockActual - $deudaArrastrada + $vendidoHoy;
                    $comprometido = (float) ($producto->stock_comprometido ?? 0.000);
                    $saldoSif = $stockActual - $comprometido;
                    $isRuptura = ($saldoSif <= 0.0);
                @endphp
                <tr>
                    <td class="font-bold">{{ $producto->codigo }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->linea ?? 'N/A' }}</td>
                    <td class="text-center">{{ $producto->unidad_medida_logistica ?? 'N/A' }}</td>
                    <td class="text-right font-mono font-bold">
                        {{ number_format($subidoHoy, 3, '.', ',') }}
                    </td>
                    <td class="text-right font-mono text-blue font-bold">
                        {{ number_format($vendidoHoy, 3, '.', ',') }}
                    </td>
                    <td class="text-right font-mono font-bold {{ $isRuptura ? 'bg-red-light text-red' : '' }}">
                        @if ($isRuptura)
                            [RUPTURA] 
                        @endif
                        {{ number_format($saldoSif, 3, '.', ',') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">
                        No se encontraron registros de stock diario en el sistema.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        * Documento de auditoría interna de control de inventario de Plásticos Fénix. Generado de forma automática por el SIF.
    </div>
</body>
</html>
