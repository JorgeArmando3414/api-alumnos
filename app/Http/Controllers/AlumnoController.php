<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlumnoFormRequest;
use App\Http\Requests\UpdateAlumnoFormRequest;
use App\Http\Resources\AlumnoCollection;
use App\Http\Resources\AlumnoResource;
use App\Models\Alumno;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alumnos = Alumno::all();
        //return response()->json($alumnos);
        return new AlumnoCollection($alumnos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AlumnoFormRequest $request)
    {
        $datos = $request->input("data.attributes");
        $alumno = new Alumno($datos);
        $alumno->save();
        return new AlumnoResource($alumno);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $resource = Alumno::find($id);
        if(!$resource){
            return response()->json([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Resource Not Found',
                        'details' => 'The requested resource does not exist or could not be found.'
                    ]
                ]
            ], 404);
        }
        return new AlumnoResource($resource);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $alumno = Alumno::find($id);
        if(!$alumno){
            return response()->json([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Resource Not Found',
                        'details' => "$id not found."
                    ]
                ]
            ], 404);
        }
        $verbo=$request->method();
        if ($verbo =="PUT"){
            $rules=["data.attributes.nombre"=>"required|min:5",
                    "data.attributes.direccion"=>"required",
                    "data.attributes.email"=>["required","email",Rule::unique("alumnos","email")->ignore($alumno)]];
        }else{
            if ($request->has("data.attributes.nombre"))
                $rules["data.attributes.nombre"]=["required","min:5"];
            if ($request->has("data.attributes.direccion"))
                $rules["data.attributes.direccion"]=["required"];
            if ($request->has("data.attributes.email"))
                $rules["data.attributes.email"]=["required","email",Rule::unique("alumnos","email")->ignore($alumno)];
        }

        $datos_validados = $request->validate($rules);
        foreach ($datos_validados['data']['attributes'] as $campo=>$valor){
            $datos[$campo]=$valor;
        }
        $alumno->update($datos);
        return new AlumnoResource($alumno);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $alumno = Alumno::find($id);
        if(!$alumno){
            return response()->json([
                'errors' => [
                    [
                        'status' => '404',
                        'title' => 'Resource Not Found',
                        'details' => "$id Not Found"
                    ]
                ]
            ], 404);
        }
        $alumno->delete();
        return response()->json(null,204);
    }
}
