<?php
namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $validatedData = $request->validate([
            'cls_id' => 'nullable|integer|exists:cliente_sedes,cls_id'
        ]);

        $query = Equipo::with(['marca', 'tipoEquipo', 'sede.cliente'])->whereNull('deleted_at');

        if (!empty($validatedData['cls_id'])) {
            $query->where('cls_id', $validatedData['cls_id']);
        }

        $equipos = $query->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'equipo' => $equipos,
            'filters' => [
                'cls_id' => $validatedData['cls_id'] ?? null
            ]
        ], 200);
    }

    /**
     * Display a listing of the resource filtered by sede id from the route.
     */
    public function bySede(string $cls_id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'cls_id' => $cls_id,
        ], [
            'cls_id' => 'required|integer|exists:cliente_sedes,cls_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $equipos = Equipo::with(['marca', 'tipoEquipo', 'sede.cliente'])
            ->whereNull('deleted_at')
            ->where('cls_id', (int) $cls_id)
            ->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'equipo' => $equipos,
            'filters' => [
                'cls_id' => (int) $cls_id
            ]
        ], 200);
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
            'equ_id' => 'nullable|unique:equipo',
            'equ_modelo' => 'required|string|max:255',
            'equ_serial' => 'required|string|max:255|unique:equipo',
            'equ_qr_code' => 'nullable|string|max:255',
            'equ_potencia' => 'nullable|string|max:255',
            'mar_id' => 'required|integer|exists:marca,mar_id',
            'teq_id' => 'required|integer|exists:tipo_equipo,teq_id',
            'cls_id' => 'required|integer|exists:cliente_sedes,cls_id',
            'equ_cant_baterias' => 'required|integer',
            'equ_ubicacion' => 'nullable|string|max:255'
        ]);

        // Crear el registro
        $equipo = Equipo::create($validatedData);

        return response()->json([
            'message' => 'Equipo creado exitosamente',
            'data' => $equipo->load(['marca', 'tipoEquipo', 'sede'])
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
            'equ_modelo' => 'sometimes|required|string|max:255',
            'equ_serial' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('equipo')->ignore($id, 'equ_id')],
            'equ_qr_code' => 'nullable|string|max:255',
            'equ_potencia' => 'nullable|string|max:255',
            'mar_id' => 'sometimes|required|integer|exists:marca,mar_id',
            'teq_id' => 'sometimes|required|integer|exists:tipo_equipo,teq_id',
            'cls_id' => 'sometimes|required|integer|exists:cliente_sedes,cls_id',
            'equ_cant_baterias' => 'sometimes|required|integer',
            'equ_ubicacion' => 'nullable|string|max:255'
        ]);

        // Encontrar y actualizar el equipo
        $equipo = Equipo::findOrFail($id);
        $equipo->update($validatedData);

        return response()->json([
            'message' => 'Equipo actualizado exitosamente',
            'data' => $equipo->load(['marca', 'tipoEquipo', 'sede'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $equipo = Equipo::findOrFail($id);

        if ($this->equipoTieneDependencias($id)) {
            return response()->json([
                'message' => 'No se puede eliminar el equipo porque tiene baterías o contratos asociados.'
            ], 409);
        }

        $equipo->delete();

        return response()->json([
            'message' => 'Equipo eliminado exitosamente'
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        try {
        
           
            $validatedData = $request->validate([
                '*.equ_id' => 'required|exists:equipo,equ_id', 
            ]);

            $ids = collect($validatedData)->pluck('equ_id')->all();

            $equiposBloqueados = collect($ids)
                ->filter(fn ($id) => $this->equipoTieneDependencias($id))
                ->values()
                ->all();

            if (!empty($equiposBloqueados)) {
                return response()->json([
                    'message' => 'No se pueden eliminar los equipos que tienen baterías o contratos asociados.',
                    'equipos_bloqueados' => $equiposBloqueados
                ], 409);
            }

            Equipo::whereIn('equ_id', $ids)->delete();

            return response()->json([
                'message' => 'Equipos eliminados exitosamente',
                'eliminados' => $ids 
            ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(), 
                'equ_id_recibidos' => $request->all() 
            ], 422);
        
        } catch (\Exception $e) {
  
            return response()->json([
                'message' => 'Ocurrió un error al intentar eliminar los equipos',
                'error' => $e->getMessage(),
                'equ_id_recibidos' => $request->all()
            ], 500);
        }
    }

    private function equipoTieneDependencias(string $id): bool
    {
        if (\DB::table('bateria_equipo')
            ->where('equ_id', $id)
            ->whereNull('deleted_at')
            ->exists()) {
            return true;
        }

        if (\DB::table('contrato_equipo')
            ->where('equ_id', $id)
            ->whereNull('deleted_at')
            ->exists()) {
            return true;
        }

        return false;
    }
}
