<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Models\Log;

class PedidoObserver
{
    /**
     * Static array to track item quantity updates during the request lifecycle.
     * Format: [pedido_id => [[producto => name, old_qty => X, new_qty => Y], ...]]
     */
    public static $itemChanges = [];

    /**
     * Helper to safely extract quantity from fields JSON structure of a PedidoItem.
     */
    public static function getQtyFromCampos($campos)
    {
        if (is_string($campos)) {
            $campos = json_decode($campos, true);
        }
        $campos = $campos ?: [];

        // Check template-specific keys first
        if (isset($campos['fardo']) && $campos['fardo'] !== '') {
            return (float)$campos['fardo'];
        }
        if (isset($campos['cantidad_fardos']) && $campos['cantidad_fardos'] !== '') {
            return (float)$campos['cantidad_fardos'];
        }
        if (isset($campos['cantidad']) && $campos['cantidad'] !== '') {
            return (float)$campos['cantidad'];
        }
        if (isset($campos['total_kilos']) && $campos['total_kilos'] !== '') {
            return (float)$campos['total_kilos'];
        }
        if (isset($campos['total_millares']) && $campos['total_millares'] !== '') {
            return (float)$campos['total_millares'];
        }

        return 0.0;
    }

    /**
     * Handle the Pedido "created" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function created(Pedido $pedido)
    {
        $userId = auth()->id();
        $userName = auth()->user() ? auth()->user()->name : 'Sistema';
        
        $ipAddress = null;
        if (!app()->runningInConsole()) {
            try {
                $ipAddress = request() ? request()->ip() : null;
            } catch (\Exception $e) {
                $ipAddress = null;
            }
        }

        Log::create([
            'user_id' => $userId,
            'accion' => 'CREAR',
            'modulo' => 'Pedidos',
            'registro_id' => $pedido->id,
            'descripcion' => "El usuario {$userName} creó el pedido {$pedido->numero}.",
            'historial_json' => null,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Handle the Pedido "updated" event.
     *
     * @param  \App\Models\Pedido  $pedido
     * @return void
     */
    public function updated(Pedido $pedido)
    {
        $oldEstado = $pedido->getOriginal('estado');
        $newEstado = $pedido->estado;
        
        $estadoCambiado = ($oldEstado !== $newEstado);
        
        $pedidoId = $pedido->id;
        $itemsCambiados = !empty(self::$itemChanges[$pedidoId]);

        if (!$estadoCambiado && !$itemsCambiados) {
            return;
        }

        $userId = auth()->id();
        $userName = auth()->user() ? auth()->user()->name : 'Sistema';
        
        $ipAddress = null;
        if (!app()->runningInConsole()) {
            try {
                $ipAddress = request() ? request()->ip() : null;
            } catch (\Exception $e) {
                $ipAddress = null;
            }
        }

        // Determine Action
        $accion = 'MODIFICAR';
        if ($estadoCambiado && $newEstado === 'Aprobado') {
            $accion = 'APROBAR';
        }

        // Build Historial JSON and Description
        $historial = [];
        $descParts = [];

        if ($estadoCambiado) {
            $historial['estado'] = [
                'antes' => $oldEstado,
                'despues' => $newEstado,
            ];
            
            if ($newEstado === 'Aprobado') {
                $descParts[] = "aprobó el pedido {$pedido->numero}";
            } else {
                $descParts[] = "actualizó el estado del pedido {$pedido->numero} de '{$oldEstado}' a '{$newEstado}'";
            }
        }

        if ($itemsCambiados) {
            $historial['items'] = self::$itemChanges[$pedidoId];
            
            $itemDescriptions = [];
            foreach (self::$itemChanges[$pedidoId] as $change) {
                $itemDescriptions[] = "{$change['producto']} (de {$change['old_qty']} a {$change['new_qty']})";
            }
            
            $descParts[] = "modificó cantidades de los ítems: " . implode(', ', $itemDescriptions);
        }

        // Combine descriptions cleanly
        $descripcion = "El usuario {$userName} " . implode(' y ', $descParts) . ".";

        Log::create([
            'user_id' => $userId,
            'accion' => $accion,
            'modulo' => 'Pedidos',
            'registro_id' => $pedido->id,
            'descripcion' => $descripcion,
            'historial_json' => $historial,
            'ip_address' => $ipAddress,
        ]);

        // Clean static cache for this pedido
        unset(self::$itemChanges[$pedidoId]);
    }
}
