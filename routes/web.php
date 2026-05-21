<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('contactos/search', [App\Http\Controllers\ContactoController::class, 'search'])->name('contactos.search');
    Route::resource('contactos', App\Http\Controllers\ContactoController::class);
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
    
    // RUTAS DE STOCK DIARIO (SUBIDAS AQUÍ PARA EVITAR EL CHOQUE CON EL RESOURCE)
    Route::get('/productos/descargar-plantilla-stock', [App\Http\Controllers\ProductoController::class, 'descargarPlantillaStock'])->name('productos.descargar_plantilla');
    Route::post('/productos/cargar-stock-diario', [App\Http\Controllers\ProductoController::class, 'cargarStockDiario'])->name('productos.cargar_stock_diario');
    
    // RESOURCE DE PRODUCTOS
    Route::resource('productos', App\Http\Controllers\ProductoController::class);
    Route::get('/api/productos/{id}/monitoreo-stock', [App\Http\Controllers\ProductoController::class, 'monitoreoStock'])->name('productos.monitoreo_stock');
    
    Route::post('cotizaciones/import-universal', [App\Http\Controllers\CotizacionController::class, 'importUniversal'])->name('cotizaciones.import_universal');
    Route::get('cotizaciones/download-template-universal', [App\Http\Controllers\CotizacionController::class, 'downloadTemplateUniversal'])->name('cotizaciones.download_template_universal');
    
    Route::post('cotizaciones/import-tratadas', [App\Http\Controllers\CotizacionController::class, 'importTratadas'])->name('cotizaciones.import_tratadas');
    Route::get('cotizaciones/download-template-tratadas', [App\Http\Controllers\CotizacionController::class, 'downloadTemplateTratadas'])->name('cotizaciones.template_tratadas');

    Route::post('cotizaciones/import-pps', [App\Http\Controllers\CotizacionController::class, 'importPps'])->name('cotizaciones.import_pps');
    Route::get('cotizaciones/download-template-pps', [App\Http\Controllers\CotizacionController::class, 'downloadTemplatePps'])->name('cotizaciones.download_template_pps');

    Route::post('cotizaciones/import-pets', [App\Http\Controllers\CotizacionController::class, 'importPets'])->name('cotizaciones.import_pets');
    Route::get('cotizaciones/download-template-pets', [App\Http\Controllers\CotizacionController::class, 'downloadTemplatePets'])->name('cotizaciones.download_template_pets');

    Route::post('cotizaciones/import-polipropileno-kilos', [App\Http\Controllers\CotizacionController::class, 'importPolipropilenoKilos'])->name('cotizaciones.import_polipropileno_kilos');
    Route::get('cotizaciones/download-template-polipropileno-kilos', [App\Http\Controllers\CotizacionController::class, 'downloadTemplatePolipropilenoKilos'])->name('cotizaciones.download_template_polipropileno_kilos');

    Route::get('cotizaciones/{cotizacion}/pdf', [App\Http\Controllers\CotizacionController::class, 'generatePdf'])->name('cotizaciones.pdf');
    Route::get('cotizaciones/{cotizacion}/jpg', [App\Http\Controllers\CotizacionController::class, 'descargarJpg'])->name('cotizaciones.jpg');
    
    Route::get('pedidos/crear/{tipo}', [App\Http\Controllers\PedidoController::class, 'crearDirecto'])->name('pedidos.crear');
    Route::post('pedidos/store-directo/{tipo}', [App\Http\Controllers\PedidoController::class, 'storeDirecto'])->name('pedidos.store_directo');
    Route::resource('pedidos', App\Http\Controllers\PedidoController::class);
    Route::post('pedidos/{pedido}/estado', [App\Http\Controllers\PedidoController::class, 'updateEstado'])->name('pedidos.update_estado');
    Route::post('pedidos/{pedido}/ajustar-cantidades', [App\Http\Controllers\PedidoController::class, 'ajustarCantidades'])->name('pedidos.ajustar_cantidades');
    Route::post('pedidos/{pedido}/aprobar', [App\Http\Controllers\PedidoController::class, 'aprobar'])->name('pedidos.aprobar');
    Route::get('pedidos/{pedido}/picking', [App\Http\Controllers\PedidoController::class, 'descargarPicking'])->name('pedidos.picking');
    Route::get('pedidos/{pedido}/pdf', [App\Http\Controllers\PedidoController::class, 'descargarPdf'])->name('pedidos.pdf');
    Route::post('pedidos/{pedido}/confirmar-fecha', [App\Http\Controllers\PedidoController::class, 'confirmarFecha'])->name('pedidos.confirmar_fecha');
    Route::post('pedidos/{pedido}/cancelar-backorder', [App\Http\Controllers\PedidoController::class, 'cancelarBackorder'])->name('pedidos.cancelar_backorder');
    Route::post('pedidos/{pedido}/revertir-a-cotizacion', [App\Http\Controllers\PedidoController::class, 'revertirACotizacion'])->name('pedidos.revertir_a_cotizacion');

    // Reporte de Cierre Diario de Stock
    Route::get('/reportes/cierre-diario', [App\Http\Controllers\ReporteController::class, 'reporteCierreDiario'])
        ->middleware('role:Administrador|Supervisor')
        ->name('reportes.cierre_diario');
    Route::get('/reportes/cierre-diario/descargar', [App\Http\Controllers\ReporteController::class, 'descargarCierreDiario'])
        ->middleware('role:Administrador|Supervisor')
        ->name('reportes.cierre_diario.descargar');

    Route::resource('users', App\Http\Controllers\UserController::class);

    Route::middleware('role:Administrador')->group(function () {
        Route::get('importar', [App\Http\Controllers\ImportController::class, 'index'])->name('importacion.index');
        Route::post('importar/clientes', [App\Http\Controllers\ImportController::class, 'importClientes'])->name('importacion.clientes');
        Route::post('importar/productos', [App\Http\Controllers\ImportController::class, 'importProductos'])->name('importacion.productos');
        Route::post('importar/contactos', [App\Http\Controllers\ImportController::class, 'importContactos'])->name('importacion.contactos');
        Route::get('importar/template/{type}', [App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('importacion.template');
    });

    Route::middleware(['auth', 'role:Administrador|Vendedor|Supervisor'])->group(function(){
        Route::resource('cotizaciones', App\Http\Controllers\CotizacionController::class);
        Route::post('cotizaciones/{cotizacion}/anular', [App\Http\Controllers\CotizacionController::class, 'anular'])->name('cotizaciones.anular');
        Route::get('cotizaciones/{id}/duplicar', [App\Http\Controllers\CotizacionController::class, 'duplicar'])->name('cotizaciones.duplicar');
    });

    Route::prefix('crm')->group(function () {
        Route::post('perfil/guardar', [App\Http\Controllers\CrmController::class, 'storePerfil'])->name('crm.perfil.store');
        Route::post('competencia/guardar', [App\Http\Controllers\CrmController::class, 'storeCompetencia'])->name('crm.competencia.store');
        Route::post('cotizacion/{id}/perdida', [App\Http\Controllers\CrmController::class, 'registrarPerdida'])->name('crm.cotizacion.perdida');
    });
});

require __DIR__.'/auth.php';