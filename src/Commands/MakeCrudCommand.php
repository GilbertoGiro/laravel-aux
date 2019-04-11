<?php

namespace LaravelAux\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a basic CRUD (Controller, Service, Repository, Request...)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prepare structure
        $model = ucfirst($this->argument('model'));
        $this->makeMigration($model);
        $this->makeModel($model);
        $this->makeRepository($model);
        $this->makeService($model);
        $this->makeRequest($model);
        $this->makeController($model);
        $this->appendRoute($model);

        // Success Message
        $this->info('Vê se segue os padrões heein!');
    }

    /**
     * Method to append Routes to api.php file (Laravel)
     *
     * @param $model
     */
    private function appendRoute($model)
    {
        $plural = strtolower(Str::plural($model));
        $route = <<<EOF

/*
|--------------------------------------------------------------------------
| {$model} Routes
|--------------------------------------------------------------------------
*/
Route::resource('{$plural}', '{$model}Controller');
EOF;
        file_put_contents(app_path() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php', $route . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Method to make Controller based on passed Model
     *
     * @param string $model
     */
    private function makeController(string $model)
    {
        $service = $model . 'Service';
        $request = $model . 'Request';
        $controller = <<<EOF
<?php

namespace App\Http\Controllers\Api;

use App\Services\\$service;
use App\Http\Requests\\$request;
use LaravelAux\BaseController;

class {$model}Controller extends BaseController
{
    /**
     * UserController constructor.
     *
     * @param {$service} \$service
     * @param {$request} \$request
     */
    public function __construct({$service} \$service)
    {
        parent::__construct(\$service, new {$request});
    }
}
EOF;
        file_put_contents(base_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Api' . DIRECTORY_SEPARATOR . $model . 'Controller.php', $controller);
    }

    /**
     * Method to make Request based on passed Model
     *
     * @param string $model
     */
    private function makeRequest(string $model)
    {
        $request = <<<EOF
<?php

namespace App\Http\Requests;

use LaravelAux\BaseRequest;

class {$model}Request extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'description' => 'required'
        ];
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required' => ':attribute é obrigatório',
        ];
    }

    /**
     * Attributes Name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'title' => 'Título',
            'description' => 'Descrição'
        ];
    }
}
EOF;
        file_put_contents(base_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests' . DIRECTORY_SEPARATOR . $model . 'Request.php', $request);
    }

    /**
     * Method to make Service based on passed Model
     *
     * @param string $model
     */
    private function makeService(string $model)
    {
        $repository = $model . 'Repository';
        $service = <<<EOF
<?php

namespace App\Services;

use App\Repositories\\$repository;
use LaravelAux\BaseService;

class {$model}Service extends BaseService
{
    /**
     * UserService constructor.
     *
     * @param {$repository} \$repository
     */
    public function __construct({$repository} \$repository)
    {
        parent::__construct(\$repository);
    }
}
EOF;
        file_put_contents(base_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . $model . 'Service.php', $service);
    }

    /**
     * Method to make Repository based on passed Model
     *
     * @param string $model
     */
    private function makeRepository(string $model)
    {
        $repository = <<<EOF
<?php

namespace App\Repositories;

use App\Models\\$model;
use LaravelAux\BaseRepository;

class {$model}Repository extends BaseRepository
{
    /**
     * UserService constructor.
     *
     * @param {$model} \$model
     */
    public function __construct({$model} \$model)
    {
        parent::__construct(\$model);
    }
}
EOF;
        file_put_contents(base_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Repositories' . DIRECTORY_SEPARATOR . $model . 'Repository.php', $repository);
    }

    /**
     * Method to make Eloquent Model
     *
     * @param string $model
     */
    private function makeModel(string $model)
    {
        $modelContent = <<<EOF
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$model} extends Model
{
    protected \$guarded = [
        'id'
    ];

    protected \$fillable = [
        'title', 'description'
    ];
}
EOF;
        file_put_contents(base_path() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $model . '.php', $modelContent);
    }

    /**
     * Method to make Migration based on Model
     *
     * @param string $model
     */
    public function makeMigration(string $model)
    {
        $plural = Str::plural($model);
        $lower = strtolower($plural);
        $migration = <<<EOF
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create{$plural}Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{$lower}', function (Blueprint \$table) {
            \$table->increments('id');
            \$table->string('title');
            \$table->string('description');
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{$lower}');
    }
}
EOF;
        file_put_contents(base_path() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . date('Y_m_d_His') . '_create_' . $lower . '_table.php', $migration);
    }
}
