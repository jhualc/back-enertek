<?php
namespace App\Http\Controllers;

use App\Models\ContratoEquipo;
use Illuminate\Http\Request;

class ContratoEquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los registros
        $contratosEquipos = ContratoEquipo::whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'contrato-equipo' => $contratosEquipos
            ], 201);
       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Generalmente, para las APIs, este método no es necesario
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'coe_id' => 'required',
            'equ_id' => 'required',
            'con_id' => 'required',
            'coe_periodicidad' => 'required',
 
        ]);

        // Crear nuevo registro
        $contratoEquipo = ContratoEquipo::create($validatedData);

        return response()->json([
            'message' => 'Contrato Equipo creado exitosamente',
            'data' => $contratoEquipo
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar un registro específico
        $contratoEquipo = ContratoEquipo::findOrFail($id);
        return response()->json($contratoEquipo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Generalmente, para las APIs, este método no es necesario
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'coe_id' => 'required',
            'equ_id' => 'required',
            'con_id' => 'required',
            'coe_periodicidad' => 'required',

        ]);

        // Encontrar y actualizar el registro
        $contratoEquipo = ContratoEquipo::findOrFail($id);
        $contratoEquipo->update($validatedData);

        return response()->json([
            'message' => 'Contrato Equipo actualizado exitosamente',
            'data' => $contratoEquipo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar el registro
        $contratoEquipo = ContratoEquipo::findOrFail($id);
        $contratoEquipo->delete();

        return response()->json([
            'message' => 'Contrato Equipo eliminado exitosamente'
        ]);
    }

      /**
     * Eliminar múltiples registros de Contrato equipo.
     */
    public function destroyMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.coe_id' => 'required|exists:contratoEquipo,coe_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ids = collect($request->all())->pluck('coe_id')->all();
        ContratoEquipo::whereIn('coe_id', $ids)->delete();

        return response()->json([
            'message' => 'Relaciones contrato Equipo eliminadas exitosamente',
            'eliminadas' => $ids
        ], 200);
    }
}
