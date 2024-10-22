<?php
namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las marcas
        $marcas = Marca::whereNull('deleted_at')->get();

        return response()->json([
         'message' => 'Respuesta Ok',
         'marca' => $marcas
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
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'mar_id' => 'unique:marca',
            'mar_descripcion' => 'required|string|max:255',
        ]);

        // Crear la nueva marca
        $marca = Marca::create($validatedData);

        return response()->json([
            'message' => 'Marca creada exitosamente',
            'data' => $marca
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar una marca específica
        $marca = Marca::findOrFail($id);
        return response()->json($marca);
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
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'mar_descripcion' => 'required|string|max:255',
        ]);

        // Encontrar y actualizar la marca
        $marca = Marca::findOrFail($id);
        $marca->update($validatedData);

        return response()->json([
            'message' => 'Marca actualizada exitosamente',
            'data' => $marca
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar la marca
        $marca = Marca::findOrFail($id);
        $marca->delete();

        return response()->json([
            'message' => 'Marca eliminada exitosamente'
        ]);
    }

    public function destroyMultiple(Request $request)
{
    \Log::info('Entró al método destroyMultiple');
    
 
    $validatedData = $request->validate([
        '*.mar_id' => 'required|exists:marca,mar_id', 
    ]);

   
    $ids = collect($validatedData)->pluck('mar_id')->all();

    
    Marca::whereIn('mar_id', $ids)->delete();

    return response()->json([
        'message' => 'Marcas eliminadas exitosamente'
    ]);
}

    
    
}
