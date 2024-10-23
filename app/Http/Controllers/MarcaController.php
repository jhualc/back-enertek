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
        try {
        

            \Log::info('Datos recibidos: ' . json_encode($request->all()));
            $validatedData = $request->validate([
                '*.mar_id' => 'required|exists:marca,mar_id', 
            ]);

            
            \Log::info('Datos validados: ' . json_encode($validatedData));

        
            $ids = collect($validatedData)->pluck('mar_id')->all();
        
            
            \Log::info('Marcas a eliminar: ' . implode(', ', $ids));

            
            Marca::whereIn('mar_id', $ids)->forceDelete();

            \Log::info('Marcas eliminadas');

            
            return response()->json([
                'message' => 'Marcas eliminadas exitosamente',
                'eliminados' => $ids 
            ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura errores de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(), 
                'mar_id_recibidos' => $request->all() 
            ], 422);
        
        } catch (\Exception $e) {
            
            \Log::error('Error al eliminar marcas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar las marcas',
                'error' => $e->getMessage(), // Opcional: devuelve el mensaje del error
                'mar_id_recibidos' => $request->all() // Devuelve los mar_id recibidos para validación
            ], 500);
        }
    }

    
    
    
    
}
