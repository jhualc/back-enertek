<?php
namespace App\Http\Controllers;

use App\Models\BateriaEquipo;
use Illuminate\Http\Request;

class BateriaEquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los registros de baterías asociadas a equipos
        $bateriasEquipos = BateriaEquipo::all();

        return response()->json([
            'message' => 'Respuesta Ok',
            'marca' => $bateriasEquipos
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
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'equ_id' => 'required|exists:equipos,equ_id',  // Asegúrate de que 'equ_id' exista en la tabla 'equipos'
            'bat_id' => 'required|exists:baterias,bat_id', // Asegúrate de que 'bat_id' exista en la tabla 'baterias'
            'beq_fecha' => 'required|date',
        ]);

        // Crear una nueva relación entre batería y equipo
        $bateriaEquipo = BateriaEquipo::create($validatedData);

        return response()->json([
            'message' => 'Batería asociada al equipo exitosamente',
            'data' => $bateriaEquipo
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar una relación específica entre batería y equipo
        $bateriaEquipo = BateriaEquipo::findOrFail($id);
        return response()->json($bateriaEquipo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Generalmente, este método no es necesario en APIs
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'equ_id' => 'required|exists:equipos,equ_id',
            'bat_id' => 'required|exists:baterias,bat_id',
            'beq_fecha' => 'required|date',
        ]);

        // Encontrar y actualizar la relación entre batería y equipo
        $bateriaEquipo = BateriaEquipo::findOrFail($id);
        $bateriaEquipo->update($validatedData);

        return response()->json([
            'message' => 'Asociación de batería con equipo actualizada exitosamente',
            'data' => $bateriaEquipo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar la relación entre batería y equipo
        $bateriaEquipo = BateriaEquipo::findOrFail($id);
        $bateriaEquipo->delete();

        return response()->json([
            'message' => 'Asociación de batería con equipo eliminada exitosamente'
        ]);
    }
}
