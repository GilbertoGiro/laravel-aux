<?php

namespace LaravelAux\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
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
        file_put_contents(app_path() . '/../routes/api.php', $route . PHP_EOL, FILE_APPEND | LOCK_EX);
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
    public function __construct({$service} \$service, {$request} \$request)
    {
        parent::__construct(\$service, \$request);
    }
}
EOF;
        Storage::disk('app')->put('Http/Controllers/Api/' . $model . 'Controller.php', $controller);
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
}
EOF;
        Storage::disk('app')->put('Http/Requests/' . $model . 'Request.php', $request);
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
        Storage::disk('app')->put('Services/' . $model . 'Service.php', $service);
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
        Storage::disk('app')->put('Repositories/' . $model . 'Repository.php', $repository);
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
    protected \$fillable = [
        'id'
    ];
}
EOF;
        Storage::disk('app')->put('Models/' . $model . '.php', $modelContent);
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
        Storage::disk('database')->put('migrations/' . date('Y_m_d_His') . '_create_' . $lower . '_table.php', $migration);
    }
}