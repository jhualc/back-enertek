<?php
namespace App\Http\Controllers;

use App\Models\Bateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las baterías
        $baterias = Bateria::with(['marca'])->whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'bateria' => $baterias
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bat_modelo' => 'required|string|max:255',
            'bat_voltaje' => 'required|numeric',
            'bat_capacidad' => 'required|numeric',
            'mar_id' => 'required|exists:marca,mar_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bateria = Bateria::create($request->all());

        return response()->json([
            'message' => 'Batería creada exitosamente',
            'bateria' => $bateria
        ], 201);
    }

    /**
     * Mostrar un registro específico de Batería.
     */
    public function show($id)
    {
        $bateria = Bateria::find($id);

        if (!$bateria || $bateria->deleted_at) {
            return response()->json(['message' => 'Batería no encontrada'], 404);
        }

        return response()->json($bateria, 200);
    }

    /**
     * Actualizar un registro específico de Batería.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bat_modelo' => 'string|max:255',
            'bat_voltaje' => 'numeric',
            'bat_capacidad' => 'numeric',
            'mar_id' => 'exists:marca,mar_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bateria = Bateria::find($id);

        if (!$bateria || $bateria->deleted_at) {
            return response()->json(['message' => 'Batería no encontrada'], 404);
        }

        $bateria->update($request->all());

        return response()->json([
            'message' => 'Batería actualizada exitosamente',
            'bateria' => $bateria
        ], 200);
    }

    /**
     * Eliminar un registro específico de Batería.
     */
    public function destroy($id)
    {
        $bateria = Bateria::find($id);

        if (!$bateria || $bateria->deleted_at) {
            return response()->json(['message' => 'Batería no encontrada'], 404);
        }

        $bateria->delete();

        return response()->json(['message' => 'Batería eliminada exitosamente'], 200);
    }

    /**
     * Eliminar múltiples registros de Batería.
     */
    public function destroyMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.bat_id' => 'required|exists:bateria,bat_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ids = collect($request->all())->pluck('bat_id')->all();
        Bateria::whereIn('bat_id', $ids)->delete();

        return response()->json([
            'message' => 'Baterías eliminadas exitosamente',
            'eliminadas' => $ids
        ], 200);
    }
}
