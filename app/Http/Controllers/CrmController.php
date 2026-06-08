<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerfilCliente;
use App\Models\Competencia;
use App\Models\Cotizacion;

class CrmController extends Controller
{
    /**
     * Guarda o actualiza el perfil técnico del cliente.
     */
    public function storePerfil(Request $request)
    {
        // Validaciones solicitadas
        $validated = $request->validate([
            'cliente_id'           => 'required|integer|exists:clientes,id',
            'tipo_preforma'        => 'nullable|string|max:255',
            'gramaje'              => 'nullable|string|max:255',
            'cuello'               => 'nullable|string|max:255',
            'aplicacion'           => 'nullable|string|max:255',
            'cant_maquinas'        => 'nullable|numeric',
            'vol_mensual'          => 'nullable|numeric',
            'vol_proyectado'       => 'nullable|numeric',
            'frecuencia_compra'    => 'nullable|string|max:255',
            'urgencias_frecuentes' => 'nullable|boolean',
            'observaciones'        => 'nullable|string',
        ]);

        // Utilizamos updateOrCreate para actualizar o crear el perfil
        $perfil = PerfilCliente::updateOrCreate(
            ['cliente_id' => $validated['cliente_id']],
            $validated
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil del cliente guardado exitosamente.',
                'data' => $perfil
            ]);
        }

        return redirect()->back()->with('success', 'Perfil del cliente guardado exitosamente.');
    }

    /**
     * Guarda información de la competencia (vía AJAX).
     */
    public function storeCompetencia(Request $request)
    {
        try {
            // Validaciones para la competencia
            $validated = $request->validate([
                'cliente_id'        => 'required|integer|exists:clientes,id',
                'producto_id'       => 'required|integer|exists:productos,id',
                'proveedor_nombre'  => 'required|string|max:255',
                'precio_ofrecido'   => 'nullable|numeric',
                'unidad_volumen'    => 'nullable|string|max:255',
                'fecha_dato'        => 'nullable|date',
                'motivo_perdida'    => 'nullable|string|max:255',
                'entrega_proveedor' => 'nullable|string|max:255',
                'entrega_nuestra'   => 'nullable|string|max:255',
                'detalle_perdida'   => 'nullable|string',
            ]);

            // Fallback para campos no nulos en base de datos
            if (!isset($validated['precio_ofrecido']) || $validated['precio_ofrecido'] === '') {
                $validated['precio_ofrecido'] = 0;
            }

            if (empty($validated['fecha_dato'])) {
                $validated['fecha_dato'] = date('Y-m-d');
            }

            $competencia = Competencia::create($validated);

            // Retorna respuesta JSON de éxito
            return response()->json([
                'status' => 'success',
                'message' => 'Dato registrado',
                'data' => $competencia
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra una cotización como perdida con sus motivos.
     */
    public function registrarPerdida(Request $request, $id)
    {
        // Validación de los motivos de pérdida
        $validated = $request->validate([
            'motivo_perdida'    => 'required|string|max:255',
            'detalle_perdida'   => 'nullable|string',
            'proveedor_ganador' => 'nullable|string|max:255', // Campo opcional agregado en la migración
        ]);

        $cotizacion = Cotizacion::findOrFail($id);
        
        // Cambiar el estado y guardar los datos
        $cotizacion->estado = 'Cancelada/Perdida';
        $cotizacion->motivo_perdida = $validated['motivo_perdida'];
        $cotizacion->detalle_perdida = $validated['detalle_perdida'] ?? null;
        
        if (isset($validated['proveedor_ganador'])) {
            $cotizacion->proveedor_ganador = $validated['proveedor_ganador'];
        }
        
        $cotizacion->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'La cotización ha sido registrada como Perdida.'
            ]);
        }

        // Redireccionar al índice de cotizaciones con mensaje (si no es AJAX)
        return redirect()->route('cotizaciones.index')->with('success', 'La cotización ha sido registrada como Cancelada/Perdida.');
    }
}
