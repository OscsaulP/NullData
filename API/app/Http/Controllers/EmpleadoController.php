<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $empleados = Empleado::all();

        return response()->json($empleados);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar los datos del empleado
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:empleados',
            'puesto' => 'required|string',
            'fecha_nacimiento' => 'required|date_format:d/m/Y',
            'domicilio' => 'required|string',
            'skills' => 'required|array|min:1',
            'skills.*.nombre' => 'required|string',
            'skills.*.calificacion' => 'required|integer|min:1|max:5',
        ]);

        // Obtener las coordenadas del domicilio utilizando Google Maps API
        $client = new Client();
        $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'query' => [
                'address' => $validatedData['domicilio'],
                'key' => 'TU_API_KEY',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $location = $data['results'][0]['geometry']['location'];
        $latitude = $location['lat'];
        $longitude = $location['lng'];

        // Crear el empleado
        $empleado = Empleado::create([
            'nombre' => $validatedData['nombre'],
            'email' => $validatedData['email'],
            'puesto' => $validatedData['puesto'],
            'fecha_nacimiento' => \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['fecha_nacimiento'])->format('Y-m-d'),
            'domicilio' => $validatedData['domicilio'],
            'latitud' => $latitude,
            'longitud' => $longitude,
        ]);

        // Asociar los skills al empleado
        foreach ($validatedData['skills'] as $skillData) {
            $empleado->skills()->create([
                'nombre' => $skillData['nombre'],
                'calificacion' => $skillData['calificacion'],
            ]);
        }

        return response()->json($empleado, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    
    public function show($id)
    {
        // Obtener el empleado por su ID
        $empleado = Empleado::with('skills')->findOrFail($id);

        // Crear una instancia del cliente de Guzzle
        $client = new Client();

        // Obtener la dirección del domicilio del empleado
        $direccion = $empleado->domicilio;

        // Hacer una solicitud GET a la API de Google Maps
        $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'query' => [
                'address' => $direccion,
                'key' => 'TU_API_KEY'
            ]
        ]);

        // Decodificar la respuesta JSON
        $data = json_decode($response->getBody(), true);

        // Obtener las coordenadas del primer resultado de geocoding
        $coordenadas = $data['results'][0]['geometry']['location'];

        // Añadir las coordenadas al objeto del empleado
        $empleado->coordenadas = $coordenadas;

        // Devolver el empleado como respuesta JSON
        return response()->json($empleado);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validar los datos del empleado
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:empleados,email,' . $id,
            'puesto' => 'required|string',
            'fecha_nacimiento' => 'required|date_format:d/m/Y',
            'domicilio' => 'required|string',
            'skills' => 'required|array|min:1',
            'skills.*.nombre' => 'required|string',
            'skills.*.calificacion' => 'required|integer|min:1|max:5',
        ]);

        // Obtener el empleado por su ID
        $empleado = Empleado::findOrFail($id);

        // Actualizar los datos del empleado
        $empleado->nombre = $validatedData['nombre'];
        $empleado->email = $validatedData['email'];
        $empleado->puesto = $validatedData['puesto'];
        $empleado->fecha_nacimiento = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['fecha_nacimiento'])->format('Y-m-d');
        $empleado->domicilio = $validatedData['domicilio'];
        $empleado->save();

        // Actualizar los skills del empleado
        $empleado->skills()->delete();
        foreach ($validatedData['skills'] as $skillData) {
            $empleado->skills()->create([
                'nombre' => $skillData['nombre'],
                'calificacion' => $skillData['calificacion'],
            ]);
        }

        return response()->json($empleado);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Obtener el empleado por su ID y eliminarlo
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return response()->json(null, 204);
    }
}
