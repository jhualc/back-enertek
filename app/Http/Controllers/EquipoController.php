<?php
namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los registros de equipo
        $equipos = Equipo::all();
        return response()->json([
            'message' => 'Respuesta Ok',
            'marca' => $equipos
            ], 201);
      
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Generalmente, este método no es necesario para APIs
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'equ_id' => 'required',
            'equ_modelo' => 'required|string|max:255',
            'equ_serial' => 'required|string|max:255',
            'mar_id' => 'required',
            'teq_id' => 'required',
            'equ_cant_baterias' => 'required|integer',
            'ins_id' => 'required',
        ]);

        // Crear el registro
        $equipo = Equipo::create($validatedData);

        return response()->json([
            'message' => 'Equipo creado exitosamente',
            'data' => $equipo
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar un equipo específico
        $equipo = Equipo::findOrFail($id);
        return response()->json($equipo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Generalmente, este método no es necesario para APIs
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'equ_id' => 'required',
            'equ_modelo' => 'required|string|max:255',
            'equ_serial' => 'required|string|max:255',
            'mar_id' => 'required',
            'teq_id' => 'required',
            'equ_cant_baterias' => 'required|integer',
            'ins_id' => 'required',
        ]);

        // Encontrar y actualizar el equipo
        $equipo = Equipo::findOrFail($id);
        $equipo->update($validatedData);

        return response()->json([
            'message' => 'Equipo actualizado exitosamente',
            'data' => $equipo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar el equipo
        $equipo = Equipo::findOrFail($id);
        $equipo->delete();

        return response()->json([
            'message' => 'Equipo eliminado exitosamente'
        ]);
    }
}
