<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BateriaEquipo;
use Illuminate\Support\Facades\Validator;

class BateriaEquipoController extends Controller
{
    public function index()
    {
        $bateriaEquipos = BateriaEquipo::with(['equipo', 'bateria'])->whereNull('deleted_at')->get();
        return response()->json($bateriaEquipos, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'equ_id' => 'required|exists:equipo,equ_id',
            'bat_id' => 'required|exists:bateria,bat_id',
            'beq_fecha' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $bateriaEquipo = BateriaEquipo::create($request->all());

        return response()->json([
            'message' => 'Batería de equipo creada exitosamente',
            'bateriaEquipo' => $bateriaEquipo
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'equ_id' => 'sometimes|required|exists:equipo,equ_id',
            'bat_id' => 'sometimes|required|exists:bateria,bat_id',
            'beq_fecha' => 'sometimes|required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $bateriaEquipo = BateriaEquipo::findOrFail($id);
        $bateriaEquipo->update($request->all());

        return response()->json([
            'message' => 'Batería de equipo actualizada exitosamente',
            'bateriaEquipo' => $bateriaEquipo
        ], 200);
    }

    public function destroy($id)
    {
        $bateriaEquipo = BateriaEquipo::findOrFail($id);
        $bateriaEquipo->delete();

        return response()->json([
            'message' => 'Batería de equipo eliminada exitosamente'
        ], 200);
    }
}
