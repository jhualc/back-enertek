<?php
namespace App\Http\Controllers;

use App\Models\TipoEquipo;
use Illuminate\Http\Request;

class TipoEquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los tipos de equipos
        $tiposEquipos = TipoEquipo::all();
        return response()->json($tiposEquipos);
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
        // Validar los datos
        $validatedData = $request->validate([
            'teq_id' => 'required|unique:tipo_equipos',
            'teq_descripcion' => 'required|string|max:255',
        ]);

        // Crear el nuevo tipo de equipo
        $tipoEquipo = TipoEquipo::create($validatedData);

        return response()->json([
            'message' => 'Tipo de equipo creado exitosamente',
            'data' => $tipoEquipo
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar un tipo de equipo específico
        $tipoEquipo = TipoEquipo::findOrFail($id);
        return response()->json($tipoEquipo);
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
        // Validar los datos
        $validatedData = $request->validate([
            'teq_descripcion' => 'required|string|max:255',
        ]);

        // Encontrar y actualizar el tipo de equipo
        $tipoEquipo = TipoEquipo::findOrFail($id);
        $tipoEquipo->update($validatedData);

        return response()->json([
            'message' => 'Tipo de equipo actualizado exitosamente',
            'data' => $tipoEquipo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar el tipo de equipo
        $tipoEquipo = TipoEquipo::findOrFail($id);
        $tipoEquipo->delete();

        return response()->json([
            'message' => 'Tipo de equipo eliminado exitosamente'
        ]);
    }
}