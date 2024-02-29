# <p style="text-align: center;">Realización de API en Laravel<p>

Se crea un nuevo proyecto de laravel

```bash
laravel new NombreProyecto
```
Se escogen las opciones por **defecto** y como Base de datos **Mysql**

<hr>

## Creación de modelo y factory

```bash
php artisan make:model Nombre --api -fm  
```

<hr>

## Archivos .env y .yaml para la Base de Datos

### <p style="text-align: center;">.env<p>

```php
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:2LsZA1WpEGaIoFovyisJ8NXwi+oFyqCmn9nRmLk/Sxg=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=23306
DB_DATABASE=instituto
DB_USERNAME=alumno
DB_PASSWORD=alumno
DB_PASSWORD_ROOT=root
DB_PORT_PHPMYADMIN=8080

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

LANG_FAKE ="es_ES"
```

### <p style="text-align: center;">docker-compose.yaml<p>

```php
#Nombre de la version
version: "3.8"
services:
  mysql:
    # image: mysql <- Esta es otra opcion si no hacemos el build
    image: mysql

    # Para no perder los datos cuando borremos el contenedor, se guardara en ese derectorio
    volumes:
      - ./datos:/var/lib/mysql
    ports:
      - ${DB_PORT}:3306
    environment:
      - MYSQL_USER=${DB_USERNAME}
      - MYSQL_PASSWORD=${DB_PASSWORD}
      - MYSQL_DATABASE=${DB_DATABASE}
      - MYSQL_ROOT_PASSWORD=${DB_PASSWORD_ROOT}

    phpmyadmin:
        image: phpmyadmin
        container_name: phpmyadmin  #Si no te pone por defecto el nombre_directorio-nombre_servicio
        ports:
        - ${DB_PORT_PHPMYADMIN}:80
        depends_on:
        - mysql
        environment:
        PMA_ARBITRARY: 1 #Para permitir acceder a phpmyadmin desde otra maquina
        PMA_HOST: mysql
```

<hr>

## Docker

Se levanta el docker con la base de datos

```bash
docker compose up
```

Si existe algun conflicto con otro contenedor se puede eliminar o cambiar el nombre y volver a levantarlo

<hr>

## Levantar el Servidor

```bash
php artisan serve
```

No necesitamos herramientas como Vite ya que en este proyecto solo se trabaja con el Back-End

<hr>

## Población de la Base de Datos

### <p style="text-align: center;">2024_02_20_092559_create_NombreModelo_table.php<p>

**Ubicación**: database/migrations/2024_02_20_092559_create_NombreModelo_table.php


```php
        Schema::create('nombre_plural', function (Blueprint $table) {
                $table->id();
                $table->string("nombre");
                $table->string("direccion");
                $table->string("email");
                $table->timestamps();
            });
```

### <p style="text-align: center;">NombreFactory.php<p>

**Ubicación**: database/factories/NombreFactory.php

```php
public function definition(): array
{
    return [
    "nombre" =>fake()->name(),
    "direccion" =>fake()->address(),
    "email" =>fake()->email()
    ];
}
```

### <p style="text-align: center;">DatabaseSeeder.php<p>

**Ubicación**: database/seeders/DatabaseSeeder.php

```php
public function run(): void
{
    NombreModelo::factory(20)->create();
}
```

Se ejecuta el siguiente comando para poblar la base de datos
```bash
php artisan migrate --seed 
```

El idioma de los datos se puede configurar en  **config/app.php**

```php
'faker_locale' => 'es_ES',  
```

<hr>

## Resource, Collection y Request

```bash 
php artisan make:request NombreFormRequest
```
```bash
php artisan make:resource NombreResource
```
```bash
php artisan make:resource NombreCollection
```

### <p style="text-align: center;">NombreResource.php<p>

**Ubicación**: app/Http/Resources/NombreResource.php

```php
public function toArray(Request $request): array{
    return[
        "id"=>$this->id,
        "type" => "Nombre",
        "attributes" => [
            "nombre"=>$this->nombre,
            "direccion"=>$this->direccion,
            "email"=>$this->email,
        ],
        "link"=>url('api/nombre_plural'.$this->id)
        ];
    }
public function with(Request $request):array{
        return [
            "jsonapi"=>[
                "version"=>"1.0"
            ]
        ];
    }
```

El Resource devolverá los datos de la instancia deseada

### <p style="text-align: center;">NombreCollection.php<p>

**Ubicación**: app/Http/Resources/NombreCollection.php

```php
public function toArray(Request $request): array{
    return parent::toArray($request);
}

public function with(Request $request){
    return[
        "jsonapi" => [
            "version"=>"1.0"
        ]
    ];
}
```
El Collection devolverá la colección de todas las instancias del modelo. 
<br><br>
La función with() añade la respuesta al final del recurso o colección, se sobreescribe si se encuentra en ambas partes para no duplicar datos innecesariamente

### <p style="text-align: center;">NombreRequest.php<p>

**Ubicación**: app/Http/Requests/NombreRequest.php

```php
public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        "data.attributes.nombre"=>"required|min:5",
        "data.attributes.direccion"=>"required",
        "data.attributes.email"=>"required|email|unique:alumnos, email"
    ];
}
```
El Request servirá para validar datos al crear una instancia del modelo

<hr>

## Ruta

### <p style="text-align: center;">api.php<p>

**Ubicación**: routes/api.php

```php
use \App\Http\Controllers\NombreController;

Route::apiResource("nombre_plural",NombreController::class);
```

<hr>

## Función index() y show()

### <p style="text-align: center;">NombreController.php<p>

**Ubicación**: app/Http/Controllers/NombreController.php

```php
public function index(){
    $nombre_plural = Nombre::all();
    return new NombreCollection($nombre_plural);
}

public function show(int $id)
    {
        $resource = NombreModelo::find($id);
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
        return new NombreResource($resource);
    }
```

La función show() devuelve un Resource de la instancia con el **id** indicado o un json con error 404 en caso de no existir<br>
La función index() devuelve una colección con todas las instancias del modelo
<hr>

## Función store()

### <p style="text-align: center;">NombreModelo.php<p>

**Ubicación**: app/http/Models/NombreModelo.php

```php
protected $fillable = ["nombre","direccion","email"];
```

### <p style="text-align: center;">NombreController.php<p>

**Ubicación**: app/http/Controllers/NombreController.php

```php
public function store(NombreFormRequest $request){
    $datos = $request->input("data.attributes");
    $nombre = new Nombre($datos);
    $nombre->save();
    return new NombreResource($nombre);
}
```
Ahora la función store() guarda en la base de datos los datos introducidos y devuelve un resource de la instancia creada
<br> Los datos introducidos **seran validados por el Request** hecho anteriormente

<hr>

## Función destroy()

### <p style="text-align: center;">NombreController.php<p>

**Ubicación**: app/http/Controllers/NombreController.php

```php
public function destroy(int $id){
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
```
Se le pasa el **id** de la instancia a borrar, si no existe devuelve un json con error **404**, si existe la borra y devuelve un json vacio con el estado 204

<hr>

## Función update()

### <p style="text-align: center;">NombreController.php<p>

**Ubicación**: app/http/Controllers/NombreController.php

```php
public function update(Request $request, int $id){
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
```
Se le pasa el **id** de la instancia a actualizar (si no existe devuelve un json con error **404**) si el metodo es '**PUT**' significa que se tienen que actualizar todos los datos
asi que crea un array con todas las reglas de validación, si no es 'PUT' significa que es '**PATCH**'
y se actualizaran unicamente los datos introducidos asi que las reglas de validación se añaden al array dependiendo de si
su atributo existe en la petición. <br><br>
Por último guarda los valores validados en un array, actualiza la instancia deseada y devuelve un Resource para comprobar que se
ha realizado la actualización.

<hr>

## Control de Excepciones
### <p style="text-align: center;">Handler.php<p>
**Ubicación**: app/Exceptions/Handler.php
```php
protected function invalidJson($request, ValidationException $exception):JsonResponse {
        return response()->json([
            'errors' => collect($exception->errors())->map(function ($message, $field) use
            ($exception) {
                return [
                    'status' => '422',
                    'title' => 'Validation Error',
                    'details' => $message[0],
                    'source' => [
                        'pointer' => '/data/attributes/' . $field
                    ]
                ];
            })->values()
        ], $exception->status);
    }

public function render($request, Throwable $exception){
if($exception instanceof ValidationException)
return $this->invalidJson($request, $exception);
if ($exception instanceof QueryException) {
return response()->json([
'errors' => [
[
'status' => '500',
'title' => 'Database Error',
'detail' => 'Error procesando la respuesta. Inténtelo más tarde.'
]
]
], 500);
}
return parent::render($request, $exception);
}
```
Si se produce un error de validación la funcion **invalidJson()** devolvera un json con todos los errores encontrados <br>
Si se produce un error de base de datos se devuelve un json con un mensaje personalizado y el estado 500

<hr>

## Middleware

```bash 
php artisan make:middleware HeaderMiddleware
```

### <p style="text-align: center;">HeaderMiddleware.php<p>

**Ubicación**: app/Http/Middleware/HeaderMiddleware.php

```php  
public function handle(Request $request, Closure $next): Response {
        if ($request->header('accept') != 'application/vnd.api+json') {
            return response()->json([
                'errors' =>[
                    "status"=>406,
                    "title"=>"Not Acceptable",
                    "details"=>"Content File not specified"
                ]
            ], 406);
        }

        return $next($request);
    }
```
Si el Header 'Accept' no tiene el valor especificado se devuelve un error 406 <br>
Para implementarlo hace falta añadirlo a Kernel.php <br>

### <p style="text-align: center;">Kernel.php<p>
**Ubicación**: app/Http/Kernel.php

```php
protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            HeaderMiddleware::class //Solo Hace falta añadir esta linea
        ],
    ];
```
<hr>

## Realización de pruebas

Para la realización de pruebas se usará **Postman** <br><br>
<img src=".\public\header.png"/><br>
Se crea el Header 'Accept' con su correspondiente valor para poder empezar.<br>

### <p style="text-align:center">index()</p>

<img src=".\public\index.png"/><br>

### <p style="text-align:center">show()</p>

<img src=".\public\show.png"/><br>

### <p style="text-align:center">destroy()</p>

<img src=".\public\destroy.png"/><br>
<img src=".\public\destroy2.png"/><br>

### <p style="text-align:center">update()</p>

<img src=".\public\update.png"/><br>
