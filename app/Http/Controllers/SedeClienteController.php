<?php

namespace App\Http\Controllers;

use App\Models\ClienteSede;
use Illuminate\Http\Request;

class SedeClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las sedes de clientes
        $clienteSedes = ClienteSede::with('cliente')->whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Sedes de clientes obtenidas exitosamente',
            'data' => $clienteSedes
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'cls_descripcion' => 'required|string|max:255',
            'cls_direccion' => 'required|string|max:255',
            'cli_id' => 'required|exists:cliente,cli_id'
        ]);

        // Crear la sede de cliente
        $clienteSede = ClienteSede::create($validatedData);

        return response()->json([
            'message' => 'Sede de cliente creada exitosamente',
            'data' => $clienteSede
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Obtener una sede de cliente específica
        $clienteSede = ClienteSede::with('cliente')->findOrFail($id);

        return response()->json([
            'message' => 'Sede de cliente obtenida exitosamente',
            'data' => $clienteSede
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'cls_descripcion' => 'required|string|max:255',
            'cls_direccion' => 'required|string|max:255',
            'cli_id' => 'required|exists:cliente,cli_id'
        ]);

        // Encontrar y actualizar la sede de cliente
        $clienteSede = ClienteSede::findOrFail($id);
        $clienteSede->update($validatedData);

        return response()->json([
            'message' => 'Sede de cliente actualizada exitosamente',
            'data' => $clienteSede
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar la sede de cliente
        $clienteSede = ClienteSede::findOrFail($id);
        $clienteSede->delete();

        return response()->json([
            'message' => 'Sede de cliente eliminada exitosamente'
        ], 200);
    }

    /**
     * Remove multiple resources from storage.
     */
    public function destroyMultiple(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validatedData = $request->validate([
                '*.cls_id' => 'required|exists:cliente_sede,cls_id'
            ]);

            $ids = collect($validatedData)->pluck('cls_id')->all();

            ClienteSede::whereIn('cls_id', $ids)->delete();

            return response()->json([
                'message' => 'Sedes de clientes eliminadas exitosamente',
                'eliminados' => $ids
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Capturar errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
                'ids_recibidos' => $request->all()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar las sedes de clientes',
                'error' => $e->getMessage(),
                'ids_recibidos' => $request->all()
            ], 500);
        }
    }
}
