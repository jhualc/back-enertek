<?php
namespace App\Http\Controllers;

use App\Models\Bateria;
use Illuminate\Http\Request;

class BateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las baterías
        $baterias = Bateria::all();

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
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'bat_id' => 'required|unique:baterias',
            'bat_modelo' => 'required|string|max:255',
            'bat_voltaje' => 'required|numeric',
            'bat_capacidad' => 'required|numeric',
            'mar_id' => 'required|exists:marcas,mar_id', 
        ]);

        // Crear una nueva batería
        $bateria = Bateria::create($validatedData);

        return response()->json([
            'message' => 'Batería creada exitosamente',
            'data' => $bateria
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar una batería específica
        $bateria = Bateria::findOrFail($id);
        return response()->json($bateria);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'bat_modelo' => 'required|string|max:255',
            'bat_voltaje' => 'required|numeric',
            'bat_capacidad' => 'required|numeric',
            'mar_id' => 'required|exists:marcas,mar_id',
        ]);

        // Encontrar y actualizar la batería
        $bateria = Bateria::findOrFail($id);
        $bateria->update($validatedData);

        return response()->json([
            'message' => 'Batería actualizada exitosamente',
            'data' => $bateria
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar la batería
        $bateria = Bateria::findOrFail($id);
        $bateria->delete();

        return response()->json([
            'message' => 'Batería eliminada exitosamente'
        ]);
    }
}
