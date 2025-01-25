<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra una lista de los clientes.
     */
    public function index()
    {
        $clientes = Cliente::whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'cliente' => $clientes
        ], 200);
    }

    /**
     * Almacena un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cli_nombre' => 'required|string|max:255',
            'cli_identificacion' => 'required|string|max:50|unique:cliente,cli_identificacion',
            'cli_tipo_identificacion' => 'required|string|max:50',
        ]);

        $cliente = Cliente::create($validatedData);

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'data' => $cliente
        ], 201);
    }

    /**
     * Muestra un cliente específico.
     */
    public function show(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
    }

    /**
     * Actualiza un cliente específico en la base de datos.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'cli_nombre' => 'required|string|max:255',
            'cli_identificacion' => 'required|string|max:50|unique:cliente,cli_identificacion,' . $id . ',cli_id',
            'cli_tipo_identificacion' => 'required|string|max:50',
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($validatedData);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'data' => $cliente
        ]);
    }

    /**
     * Elimina un cliente de forma lógica (soft delete).
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }

    
    /**
     * Eliminar múltiples registros de Batería.
     */
    public function destroyMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.cli_id' => 'required|exists:cliente,cli_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ids = collect($request->all())->pluck('cli_id')->all();
        Cliente::whereIn('cli_id', $ids)->delete();

        return response()->json([
            'message' => 'Clientes eliminados exitosamente',
            'eliminadas' => $ids
        ], 200);
    }
}
