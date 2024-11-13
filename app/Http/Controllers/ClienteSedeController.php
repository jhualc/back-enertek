<?php
namespace App\Http\Controllers;

use App\Models\ClienteSede;
use Illuminate\Http\Request;

class ClienteSedeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los clientes
        $clienteSede = ClienteSede::whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'clienteSede' => $clienteSede
            ], 201);
       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Generalmente, este método no es necesario en APIs
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cls_id' => 'required|integer',
            'cli_descripcion' => 'required|string|max:255',
            'cls_direccion' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $clienteSede = ClienteSede::create($request->all());

        return response()->json([
            'message' => 'ClienteSede creado exitosamente',
            'cliente_sede' => $clienteSede
        ], 201);
    }
    public function show($id)
    {
        $clienteSede = ClienteSede::find($id);

        if (!$clienteSede || $clienteSede->deleted_at) {
            return response()->json(['message' => 'ClienteSede no encontrado'], 404);
        }

        return response()->json($clienteSede, 200);
    }

    /**
     * Actualizar un registro específico de ClienteSede.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cls_id' => 'integer',
            'cli_descripcion' => 'string|max:255',
            'cls_direccion' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $clienteSede = ClienteSede::find($id);

        if (!$clienteSede || $clienteSede->deleted_at) {
            return response()->json(['message' => 'ClienteSede no encontrado'], 404);
        }

        $clienteSede->update($request->all());

        return response()->json([
            'message' => 'ClienteSede actualizado exitosamente',
            'cliente_sede' => $clienteSede
        ], 200);
    }

    /**
     * Eliminar un registro específico de ClienteSede.
     */
    public function destroy($id)
    {
        $clienteSede = ClienteSede::find($id);

        if (!$clienteSede || $clienteSede->deleted_at) {
            return response()->json(['message' => 'ClienteSede no encontrado'], 404);
        }

        $clienteSede->delete();

        return response()->json(['message' => 'ClienteSede eliminado exitosamente'], 200);
    }

    /**
     * Eliminar múltiples registros de ClienteSede.
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
        ClienteSede::whereIn('cli_id', $ids)->delete();

        return response()->json([
            'message' => 'Clientes eliminados exitosamente',
            'eliminados' => $ids
        ], 200);
    }
}
