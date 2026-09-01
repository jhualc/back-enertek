<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteSede;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Muestra una lista de los clientes.
     */
    public function index()
    {
        $cliente = Cliente::whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'cliente' => $cliente
        ], 200);
    }

    /**
     * Almacena un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
{
    \Log::info('Request recibido en store:', $request->all());

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

        if ($this->clienteTieneDependencias($id)) {
            return response()->json([
                'message' => 'No se puede eliminar el cliente porque tiene sedes o equipos asociados.'
            ], 409);
        }

        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }

    
    /**
     * Eliminar múltiples registros de CLiente.
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

        $clientesBloqueados = collect($ids)
            ->filter(fn ($id) => $this->clienteTieneDependencias($id))
            ->values()
            ->all();

        if (!empty($clientesBloqueados)) {
            return response()->json([
                'message' => 'No se pueden eliminar los clientes que tienen sedes o equipos asociados.',
                'clientes_bloqueados' => $clientesBloqueados
            ], 409);
        }

        Cliente::whereIn('cli_id', $ids)->delete();

        return response()->json([
            'message' => 'Clientes eliminados exitosamente',
            'eliminadas' => $ids
        ], 200);
    }

    private function clienteTieneDependencias(string $id): bool
    {
        if (ClienteSede::where('cli_id', $id)->exists()) {
            return true;
        }

        if (DB::table('cliente_equipo')
            ->where('cli_id', $id)
            ->whereNull('deleted_at')
            ->exists()) {
            return true;
        }

        return Equipo::whereIn('cls_id', function ($query) use ($id) {
            $query->select('cls_id')
                ->from('cliente_sedes')
                ->where('cli_id', $id)
                ->whereNull('deleted_at');
        })->exists();
    }
}
