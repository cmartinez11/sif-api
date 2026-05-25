<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Registrar el observer de Pedido
        \App\Models\Pedido::observe(\App\Observers\PedidoObserver::class);

        // Registrar hook para capturar cambios de cantidad en items de pedido
        \App\Models\PedidoItem::updating(function ($item) {
            $oldQty = \App\Observers\PedidoObserver::getQtyFromCampos($item->getOriginal('campos_json'));
            $newQty = \App\Observers\PedidoObserver::getQtyFromCampos($item->campos_json);

            if ($oldQty != $newQty) {
                $producto = $item->producto;
                $productoNombre = $producto ? $producto->nombre : 'Producto #' . $item->producto_id;

                \App\Observers\PedidoObserver::$itemChanges[$item->pedido_id][] = [
                    'producto' => $productoNombre,
                    'old_qty'  => $oldQty,
                    'new_qty'  => $newQty,
                ];
            }
        });
    }
}
