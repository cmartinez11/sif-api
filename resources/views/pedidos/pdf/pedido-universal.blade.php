<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido de Compra - Plásticos Fénix</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0px; background-color: #fff; }
        .page-container { padding: 30px; background-color: #ffffff; position: relative; }
        
        .text-green { color: #0CC954; }
        .bg-green { background-color: #0CC954; color: #000; }
        .text-gold { color: #d4af37; }
        .bg-gold { background-color: #d4af37; color: #0CC954; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-items th { background-color: #0CC954; color: white; border: 1px solid #0CC954; padding: 8px; text-transform: uppercase; font-size: 9px; }
        .table-items td { border: 1px solid #cccccc; padding: 8px; text-align: left; background-color: #fff; }
        
        .border-box { border: 1px solid #d1d5db; padding: 12px; border-radius: 8px; background-color: #fff; margin-bottom: 20px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .logo-box { margin-bottom: 5px; }
        
        /* Logistics Block */
        .logistics-box { width: 60%; float: left; border: 1px solid #d1d5db; padding: 10px; border-radius: 8px; background-color: #fff; }
        .logistics-title { font-size: 8px; font-weight: bold; color: #6b7280; text-transform: uppercase; margin-bottom: 5px; }
        .logistics-row { margin-bottom: 3px; border-bottom: 1px solid #e5e7eb; overflow: hidden; }
        .logistics-label { font-weight: bold; color: #0CC954; width: 80px; display: inline-block; }
        
       /* Totals Block */
        .totals-box { width: 35%; float: right; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; background-color: #fff; }
        .totals-row { padding: 15px 10px; border-bottom: 1px solid #e5e7eb; overflow: hidden; }
        .totals-label { float: left; width: 50%; font-weight: bold; color: #4b5563; font-size: 9px; text-transform: uppercase; }
        .totals-value { line-height: 1.2; float: right; width: 50%; text-align: right; font-weight: bold; color: #111827; }
        .totals-final { background-color: #f9fafb; border-top: 1px solid #e5e7eb; }
        .totals-soles { background-color: #0CC954; color: white; padding: 15px 10px; border-top: 1px solid #0CC954; }
        .footer { margin-top: 30px; border-top: 2px solid #0CC954; padding-top: 10px; }
        
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="page-container">
        {{-- Header --}}
        <table style="border: none;">
            <tr>
                <td style="border: none; width: 60%; vertical-align: middle;">
                    <div class="logo-box">
                        @if(isset($logoBase64) && $logoBase64)
                            <img src="{{ $logoBase64 }}" style="width: 180px; height: auto;">
                        @else
                            <span style="font-weight: 900; color: #0CC954; font-size: 24px; letter-spacing: -1px;">PLÁSTICOS FÉNIX</span>
                        @endif
                    </div>
                    <p style="margin: 0; font-size: 9px; color: #666;">GRUPO FÉNIX S.A.C - RUC: 20522086704</p>
                    <p style="margin: 0; font-size: 8px; color: #888;">Pasaje Loreto Mypes De Villa Sol MZ. A Lote. 10 - Jicamarca</p>
                </td>
                <td style="border: none; width: 40%; text-align: right; vertical-align: middle;">
                    <div style="background-color: #fff; border: 2px solid #0CC954; padding: 10px; border-radius: 10px; display: inline-block; min-width: 150px;">
                        <h3 style="margin: 0; color: #0CC954; font-size: 14px;">PEDIDO</h3>
                        <h2 style="margin: 5px 0 0 0; color: #d4af37; font-size: 18px;">N° {{ $pedido->numero }}</h2>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Client and General Info --}}
        <div class="border-box">
            <table style="border: none; margin: 0;">
                <tr>
                    <td style="border: none; width: 60%; vertical-align: top;">
                        <span class="text-green font-bold">CLIENTE:</span> {{ strtoupper($pedido->cotizacion->cliente?->nombre ?? 'N/A') }}<br>
                        <span class="text-green font-bold">RUC/DNI:</span> {{ $pedido->cotizacion->cliente?->ruc ?? 'N/A' }}<br>
                        <span class="text-green font-bold">DIRECCIÓN:</span> {{ $pedido->cotizacion->cliente?->direccion ?? 'CIUDAD' }}<br>
                        <span class="text-green font-bold">CONDICIÓN PAGO:</span> {{ $pedido->cotizacion->condicion_pago ?? $pedido->cotizacion->cliente?->condicion_pago ?? 'CONTADO' }}<br>
                        <span class="text-green font-bold">CONTACTO:</span> {{ $pedido->cotizacion->cliente?->contacto?->nombre ?? 'DEPARTAMENTO DE COMPRAS' }}<br>
                        @if(!$esPedidoDirecto)
                            <span class="text-green font-bold">REFERENCIA:</span> Cotización N° {{ $pedido->cotizacion?->numero }}<br>
                        @else
                            <span class="text-green font-bold">TIPO DE VENTA:</span> Venta Directa de Almacén<br>
                        @endif
                    </td>
                    <td style="border: none; width: 40%; vertical-align: top;">
                        @if(!$esPedidoDirecto)
                            <span class="text-green font-bold">FECHA COTIZACIÓN:</span> {{ \Carbon\Carbon::parse($pedido->cotizacion?->fecha_emision)->format('d/m/Y') }}<br>
                        @endif
                        <span class="text-green font-bold">FECHA EMISIÓN:</span> {{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y') }}<br>
                        <span class="text-green font-bold">FECHA DE ENTREGA:</span> {{ $pedido->fecha_entrega_confirmada ? \Carbon\Carbon::parse($pedido->fecha_entrega_confirmada)->format('d/m/Y') : ($pedido->cotizacion?->fecha_entrega_estimada ? \Carbon\Carbon::parse($pedido->cotizacion->fecha_entrega_estimada)->format('d/m/Y') : 'Por confirmar') }}<br>
                        <span class="text-green font-bold">MONEDA:</span> {{ strtoupper($pedido->cotizacion->moneda) }}<br>
                        @if($pedido->cotizacion->moneda == 'dolares')
                            <span class="text-green font-bold">T.C.:</span> {{ number_format($pedido->cotizacion->tipo_cambio, 3) }}<br>
                        @endif
                        <span class="text-green font-bold">ATENDIDO POR:</span> {{ $pedido->cotizacion->vendedor?->name ?? 'DPTO. VENTAS' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Items Table --}}
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width: 5%">ITEM</th>
                    <th style="width: 15%">CÓDIGO</th>
                    <th style="width: 35%">PRODUCTO</th>
                    <th style="width: 10%">CANTIDAD</th>
                    <th style="width: 10%">U/M</th>
                    <th style="width: 10%">P. UNIT</th>
                    <th style="width: 15%">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->items as $index => $item)
                @php $dt = json_decode($item->campos_json); @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $item->producto?->codigo ?? '-' }}</td>
                    <td>{{ $item->producto?->nombre ?? 'Sin nombre' }}</td>
                    <td class="text-center">{{ $dt->cantidad ?? $item->cantidad ?? 0 }}</td>
                    <td class="text-center">{{ strtoupper($item->producto?->unidad_medida ?? 'UND') }}</td>
                    <td class="text-right">{{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->precio_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Logistics and Totals --}}
        <div style="margin-top: 20px;">
            <div class="logistics-box">
                <div class="logistics-title">Resumen Logístico</div>
                <div class="logistics-row">
                    <span class="logistics-label">Agencia:</span>
                    <span>{{ $pedido->cotizacion->agencia ?? '-' }}</span>
                </div>
                <div class="logistics-row">
                    <span class="logistics-label">Dirección:</span>
                    <span>{{ $pedido->cotizacion->direccion_agencia ?? '-' }}</span>
                </div>
                <div class="logistics-row" style="border: none;">
                    <span class="logistics-label">Obs:</span>
                    <span style="font-style: italic; color: #666;">{{ $pedido->cotizacion->observaciones ?? '-' }}</span>
                </div>
            </div>

            <div class="totals-box">
                <div class="totals-row">
                    <div class="totals-label">Sub Total</div>
                    <div class="totals-value">{{ $pedido->cotizacion->moneda == 'soles' ? 'S/ ' : '$ ' }} {{ number_format($pedido->subtotal, 2) }}</div>
                </div>
                <div class="totals-row">
                    <div class="totals-label">IGV (18%)</div>
                    <div class="totals-value">{{ $pedido->cotizacion->moneda == 'soles' ? 'S/ ' : '$ ' }} {{ number_format($pedido->igv, 2) }}</div>
                </div>
                <div class="totals-row totals-final">
                    <div class="totals-label text-green" style="font-size: 11px;">Total Final</div>
                    <div class="totals-value text-green" style="font-size: 13px;">{{ $pedido->cotizacion->moneda == 'soles' ? 'S/ ' : '$ ' }} {{ number_format($pedido->total, 2) }}</div>
                </div>
                
                @if($pedido->cotizacion->moneda == 'dolares' && $pedido->cotizacion->tipo_cambio > 0)
                    <div class="totals-soles">
                        <div style="float: left; font-size: 8px; text-transform: uppercase;">Monto en Soles <br> (T.C. {{ number_format($pedido->cotizacion->tipo_cambio, 3) }})</div>
                        <div style="float: right; font-size: 14px; font-weight: 900;">S/ {{ number_format($pedido->cotizacion->total * $pedido->cotizacion->tipo_cambio, 2) }}</div>
                        <div class="clear"></div>
                    </div>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <table style="border: none; margin: 0; width: 100%; table-layout: fixed;">
                <tr>
                    {{-- Columna Izquierda: Bancos --}}
                    <td style="border: none; border-right: 1px solid #e5e7eb; width: 55%; vertical-align: top; padding-right: 15px;">
                        <h4 style="color: #0CC954; font-size: 11px; margin: 0 0 8px 0; font-weight: bold;">CUENTAS BANCARIAS</h4>
                        <div style="font-size: 9px; line-height: 1.3; color: #333;">
                            <p style="margin: 0 0 2px 0;"><strong>BCP - Cuenta Corriente:</strong></p>
                            <p style="margin: 0;"><strong>SOLES:</strong> 191-2246547-0-92</p>
                            <p style="margin: 0;"><strong>SOLES CCI:</strong> 002-191-002246547092-54</p>
                            <p style="margin: 0;"><strong>DÓLARES:</strong> 191-2227322-1-11</p>
                            <p style="margin: 0 0 8px 0;"><strong>DÓLARES CCI:</strong> 002-191-002227322111-53</p>
                            
                            <p style="margin: 0 0 2px 0;"><strong>BBVA - Cuenta Corriente:</strong></p>
                            <p style="margin: 0;"><strong>SOLES:</strong> * CTA RECAUDADORA N°18637 * PLASTICOS FENIX E.I.R.L.</p>
                            <p style="margin: 0;"><strong>SOLES CCI:</strong> 011-261-000100028190-55</p>
                            <p style="margin: 0;"><strong>DÓLARES:</strong> * CTA RECAUDADORA N° 19408 * PLASTICOS FENIX EIRL ME</p>
                            <p style="margin: 0;"><strong>DÓLARES CCI:</strong> 011-261-000100028204-54</p>
                        </div>
                    </td>

                    {{-- Columna Derecha: Redes y Contacto --}}
                    <td style="border: none; width: 45%; vertical-align: top; padding-left: 15px;">
                        <div style="background-color: #f9fafb; padding: 12px; border-radius: 6px; text-align: center;">
                            <p style="margin: 0 0 10px 0; font-weight: bold; color: #000; font-size: 11px;">"TU MARCA SIEMPRE RELEVANTE"</p>
                            
                            <table style="width: 100%; border: none; margin: 0; font-size: 9px; color: #4b5563; text-align: left;">
                                <tr>
                                    <td style="border: none; padding: 3px 0; width: 50%;">
                                        <strong style="color: #1877F2;">FB:</strong> @plasticosfenix
                                    </td>
                                    <td style="border: none; padding: 3px 0;">
                                        <strong style="color: #000000;">TK:</strong> @plasticosfenix
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; padding: 3px 0;">
                                        <strong style="color: #0CC954;">WEB:</strong> plasticosfenix.com
                                    </td>
                                    <td style="border: none; padding: 3px 0;">
                                        <strong style="color: #9ca3af;">MAIL:</strong> comercial@plasticosfenix.com
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
