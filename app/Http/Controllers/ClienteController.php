<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los clientes
        $clientes = Cliente::whereNull('deleted_at')->get();

        return response()->json([
            'message' => 'Respuesta Ok',
            'cliente' => $clientes
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
            'cli_nombre' => 'required|string|max:255',
            'cli_identificacion' => 'required|string|max:50|unique:clientes',
            'cli_tipo_identificacion' => 'required|string|max:50',
            
        ]);

        // Crear un nuevo cliente
        $cliente = Cliente::create($validatedData);

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'data' => $cliente
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar un cliente específico
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
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
            'cli_nombre' => 'required|string|max:255',
            'cli_identificacion' => 'required|string|max:50|unique:clientes,cli_identificacion,' . $id . ',cli_id',
            'cli_tipo_identificacion' => 'required|string|max:50',
           
        ]);

        // Encontrar y actualizar el cliente
        $cliente = Cliente::findOrFail($id);
        $cliente->update($validatedData);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'data' => $cliente
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Encontrar y eliminar el cliente
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }
}
