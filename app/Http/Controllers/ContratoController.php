<?php
namespace App\Http\Controllers;

use App\Models\Contrato;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los contratos
        $contratos = Contrato::whereNull('deleted_at')->get();


        return response()->json([
            'message' => 'Respuesta Ok',
            'contrato' => $contratos
            ], 201);
    
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'con_id' => 'unique:contrato',
            'con_tipo' => 'required|string|max:255',
            'con_valor' => 'required|numeric',
            'con_periodicidad' => 'required|string|max:50',
            'con_estado' => 'required|string|max:50',
            'cli_id' => 'required|exists:clientes,cli_id', 
        ]);

        // Crear un nuevo contrato
        $contrato = Contrato::create($validatedData);

        return response()->json([
            'message' => 'Contrato creado exitosamente',
            'data' => $contrato
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar un contrato específico
        $contrato = Contrato::findOrFail($id);
        return response()->json($contrato);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'con_tipo' => 'required|string|max:255',
            'con_valor' => 'required|numeric',
            'con_periodicidad' => 'required|string|max:50',
            'con_estado' => 'required|string|max:50',
            'cli_id' => 'required|exists:clientes,cli_id',
        ]);

        // Encontrar y actualizar el contrato
        $contrato = Contrato::findOrFail($id);
        $contrato->update($validatedData);

        return response()->json([
            'message' => 'Contrato actualizado exitosamente',
            'data' => $contrato
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar el contrato
        $contrato = Contrato::findOrFail($id);
        $contrato->delete();

        return response()->json([
            'message' => 'Contrato eliminado exitosamente'
        ]);
    }
}
