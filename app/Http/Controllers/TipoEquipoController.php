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
        $tiposEquipos = TipoEquipo::whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'tipo_equipo' => $tiposEquipos
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
        // Validar los datos
        $validatedData = $request->validate([
            'teq_id' => 'unique:tipo_equipo',
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
        $tipoEquipo = TipoEquipo::findOrFail($id);

        if ($this->tipoEquipoTieneDependencias($id)) {
            return response()->json([
                'message' => 'No se puede eliminar el tipo de equipo porque tiene equipos asociados.'
            ], 409);
        }

        $tipoEquipo->delete();

        return response()->json([
            'message' => 'Tipo de equipo eliminado exitosamente'
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        try {
        
           
            $validatedData = $request->validate([
                '*.teq_id' => 'required|exists:tipo_equipo,teq_id', 
            ]);

            $ids = collect($validatedData)->pluck('teq_id')->all();

            $tiposBloqueados = collect($ids)
                ->filter(fn ($id) => $this->tipoEquipoTieneDependencias($id))
                ->values()
                ->all();

            if (!empty($tiposBloqueados)) {
                return response()->json([
                    'message' => 'No se pueden eliminar los tipos de equipo que tienen equipos asociados.',
                    'tipos_bloqueados' => $tiposBloqueados
                ], 409);
            }

            TipoEquipo::whereIn('teq_id', $ids)->delete();

            return response()->json([
                'message' => 'Tipos de Equipo eliminados exitosamente',
                'eliminados' => $ids 
            ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(), 
                'teq_id_recibidos' => $request->all() 
            ], 422);
        
        } catch (\Exception $e) {
  
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar los tipos de equipo',
                'error' => $e->getMessage(),
                'teq_id_recibidos' => $request->all()
            ], 500);
        }
    }

    private function tipoEquipoTieneDependencias(string $id): bool
    {
        return \DB::table('equipo')
            ->where('teq_id', $id)
            ->whereNull('deleted_at')
            ->exists();
    }
}
