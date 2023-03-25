<?php declare(strict_types=1);

namespace Simtabi\Laranail\Core;

use Collective\Html\HtmlFacade;
use ErrorException;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Simtabi\Laranail\Nails\Auth\Auth;
use Simtabi\Laranail\Nails\General\Helpers\Username;
use Simtabi\Laranail\Nails\Laravel\Helpers\Environment;
use Simtabi\Pheg\Core\Exceptions\PhegException;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\App;
use Illuminate\Http\File as IlluminateFile;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\SplFileInfo;

class Laranail
{

    public function __construct()
    {

    }

    /**
     * @param string $guard
     * @return Auth
     * @throws Exception
     */
    public function authHelper(string $guard): Auth
    {
        return Auth::invoke($guard);
    }

    public function username(): Username
    {
        return new Username();
    }


    public function getAppUrl(): string
    {
        return rtrim(config('laranail.app_url'), '/').'/';
    }

    public function modifyTimestamps(array $dates, EloquentModel $model): bool
    {
        if (!empty($dates)) {
            $model->timestamps = false;
            foreach ($dates as $column => $date)    {
                $model->$column = $date;
            }
            if ($model->save()) {
                return true;
            }
        }
        return false;
    }

    public function setMorphClassNames(array $aliases, App $app)
    {
        $oldAliases = $app->make('config')->get('app.aliases');

        $app->make('config')->set(['app.aliases' => array_merge($oldAliases, $aliases)]);
    }



    /**
     * Appends passed value if condition is true
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function existsInFilterKey($key, $value = null): bool
    {
        return collect(explode("&", $key))->contains($value);
    }

    /**
     * Appends passed value if condition is true
     *
     * @param mixed ...$value
     *
     * @return string
     */
    public function joinInFilterKey(...$value): string
    {
        //remove empty values
        $value = array_filter($value, function ($item) {
            return isset($item);
        });

        return collect($value)->implode('&');
    }

    /**
     * Appends passed value if condition is true
     *
     * @param $key
     * @param $oldValues
     * @param $value
     *
     * @return string|null
     */
    public function removeFromFilterKey($key, $oldValues, $value): ?string
    {
        $newValues = array_diff(
            array_values(
                explode("&", $oldValues)
            ), [
            $value, 'page'
        ]);

        if (count($newValues) === 0) {
            Arr::except(Request::query(), [$key, 'page']);
            return null;
        }

        return collect($newValues)->implode('&');
    }

    /**
     * Return a string with array as dot notation
     * Example: myfield[one][name] => myfield.one.name
     * @param  string $string s$tring
     */
    public function arrayToDotNotation(string $string): string
    {
        return (string) Str::of($string)->replace('[]', '')->replace('[', '.')->replace(']', '');
    }

    /**
     * Sort parents before children
     * @param array|Collection $list
     * @param array $result
     * @param int|string|null $parent
     * @param int $depth
     * @return array
     */
    public function sortItemWithChildren(array|Collection $list, array &$result = [], int|string $parent = null, int $depth = 0): array
    {
        if ($list instanceof Collection) {
            $listArr = [];
            foreach ($list as $item) {
                $listArr[] = $item;
            }
            $list = $listArr;
        }

        foreach ($list as $key => $object) {
            if ((int)$object->parent_id === (int)$parent) {
                array_push($result, $object);
                $object->depth = $depth;
                unset($list[$key]);
                $this->sortItemWithChildren($list, $result, $object->id, $depth + 1);
            }
        }

        return $result;
    }

    public function getErrorBagMessage($key, $errorMsgClass = 'error-msg', $wrapperClass = 'has-error', $bag = 'errors'): ?string
    {
        if(Session::has($bag)) {
            $errors = Session::get($bag, new MessageBag());
            if ($errors->has($key)) {
                return $errors->first($key, "
                                <div class='$wrapperClass'>
                                     <p class='help-block $errorMsgClass'>:message</p>
                                </div>
                ");
            }
        }
        return '';
    }

    public function getErrorBagMessageClass($key, $passedClass = 'success', $failedClass = 'error', $bag = 'errors'): ?string
    {
        if(Session::has($bag) && Session::get($bag, new MessageBag())->has($key)) {
            return Session::get($bag, new MessageBag())->has($key) ? " $failedClass " : '';
        }
        return " $passedClass ";
    }

    public function getHasErrorCssClass($key, $passedClass = 'has-success', $failedClass = 'has-error', $bag = 'errors'): ?string
    {
        if(Session::has($bag) && Session::get($bag, new MessageBag())->has($key)) {
            return " $failedClass ";
        }
        return " $passedClass ";
    }

    /**
     * @param $oldValue
     * @param $key
     * @return string
     */
    public function getCheckboxStatus($oldValue, $key): ?string
    {
        return (old($key) === $oldValue) && !empty($oldValue) ? ' checked ' : '';
    }

    /**
     * @return object
     */
    public function getCurrentRouteInfo(): object
    {
        return pheg()->transfigure()->toObject([
            'request' => [
                'name'   => Request::route()->getName(),
                'path'   => Request::path(),
            ],
            'method'  => Request::method(),
            'action'  => Route::currentRouteAction(),
            'name'    => Route::current()->getName(),
        ]);
    }

    /**
     * @param $request
     * @return bool
     */
    public function isRoute($request): bool
    {
        return (Request::route()->getName() == Route::current()->getName()) && Route::current()->getName() == $request ? true : false;
    }

    /**
     * If given route is the current route
     *
     * @param $routeName
     * @return bool
     */
    public function isCurrentRoute($routeName): bool
    {
        return Route::currentRouteName() === $routeName;
    }

    public function isUrlSegment($segment, bool $strict = false, $paramKey = null, $paramValue = null): bool
    {

        // request params
        $segment = trim($segment);
        $segment = rtrim($segment,"/");

        // get current route
        $route       = Route::current();
        $prefix      = trim($route->getPrefix(), '/');
        $prefix      = !empty($prefix) ? "$prefix/" : '';
        $parameters  = request()->query();
        $parameter   = $parameters[$paramKey] ?? null;
        $paramStatus = !empty($parameter) && ($parameter == $paramValue) ? true : false;

        // validate by checking entire string in the request object
        $isRequest = function ($segment, $strict, $route, $prefix)
        {
            $segments = implode('/', Request::segments());

            if ($strict) {
                return Request::is($prefix."$segment", $prefix."$segment/");
            }else{
                return Request::is($prefix."$segment", $prefix."$segment/", $prefix."$segment/*");
            }
        };

        if (!$paramStatus) {
            return $isRequest($segment, $strict, $route, $prefix) && empty($parameter) && empty($paramValue);
        }

        return true;
    }

    public function isLastUrlSegment($segment): bool
    {
        // get all url segments from the request,
        $segments = Request::segments();

        // and check if it matches the given segment
        return (bool) end($segments) == $segment;
    }


    /**
     * @param $request
     * @param null $method
     * @return bool
     */
    public function isEditURI($request, $method = null): ?bool
    {
        $value = Request::is($request);

        if (!empty($method) && $method->exists()){
            $method = $method::find($value);
            if (!count($method) || count($method) == 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function isUrlRequestSegment($segment, $position = 0): bool
    {
        return Request::segment($position) == $segment ? true : false;
    }

    /**
     * @param $request
     * @param string $class
     * @param bool $returnBool
     * @return bool|string
     */
    public function isRequest($request, string $class = "", bool $returnBool = false): bool|string
    {
        $status = (bool) Request::is($request);

        if ($returnBool) {
            return $status;
        }

        return $status ? " $class " : "";
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function isRequestParameter($key, $value): bool
    {
        $parameters = request()->all();
        $keyValue   = $parameters[$key] ?? null;

        return !empty($keyValue) && ($keyValue == $value) ? true : false;
    }

    /**
     * Check's whether request url/route matches passed link
     *
     * @param string $link
     * @param string $type
     * @return bool
     */
    public function isRequestOnPage(string $link, string $type = "name"): bool
    {
        return match ($type) {
            "url"   => ($link == Request::fullUrl()),
            default => ($link == Request::route()->getName()),
        };
    }

    public function getRequestParameterValue($key): mixed
    {
        $parameters = request()->all();
        return $parameters[trim($key)] ?? null;
    }

    public function getActiveCssClassForRoute($routeName, $class = 'active'): ?string
    {
        return $this->isCurrentRoute($routeName) ? $class : '';
    }

    public function getActiveCssClassForUrlParameter($value, $segment = null, $key = 'tab', $class = 'active'): ?string
    {
        $currentUrl = Request::fullUrl();
        $currentUrl = pheg()->intel()->getUrlInfo($currentUrl);
        $parameter  = $currentUrl->getQueryParameter($key);
        $status     = false;

        if (empty($value) && !empty($segment)) {
            $status = $this->isLastUrlSegment($segment);
        }elseif (!empty($value) && !empty($parameter) && ($parameter == $value)){
            $status = $this->isRequestParameter($key, $value);
        }

        return $status ? " $class " : '';
    }

    public function getAnchorLink($url, $title = null, $attributes = [], $secure = null, $escape = true): ?string
    {
        return HtmlFacade::link($url, $title, $attributes, $secure, $escape);
    }

    public function getModelItem($key, EloquentModel $model, $default = ''): ?string
    {
        return $model->$key ?? $default;
    }

    public function getFormableUsersList(EloquentModel $usersModel): ?array
    {
        $results = [];
        $query   = $usersModel::select('*')->orderby('id', 'asc')->get()->toArray();

        if (count($query) < 1) {
            return $results;
        } else{
            foreach ($query as $item) {
                $username             = isset($item['username']) ? ucfirst($item['username']) : strtolower($item['email']);
                $results[$item['id']] = $username;
            }
            return $results;
        }

    }

    public function getUserItem($request, $id, EloquentModel $usersModel): ?string
    {
        return $this->getModelItem($request, $usersModel::find($id), '');
    }

    public function getUserData($userId, EloquentModel $usersModel){
        return $usersModel::select('*')->where('id', '=', trim($userId))->orderby('id', 'desc')->get();
    }

    public function getUserDataFromUsername($username, EloquentModel $usersModel): bool
    {
        if (!$this->isUserExists($username, $usersModel, 'username')) {
            return false;
        }
        return $usersModel::select('*')->where('username', '=', trim($username))->orderby('username', 'desc')->get();
    }

    public function getRouteNameFromUrl($url)
    {
        return app('router')->getRoutes()->match(app('request')->create($url))->getName();
    }

    public function isUserExists($value, EloquentModel $usersModel, $key = 'id'): bool
    {
        return $usersModel::where($key, '=', $value)->exists() ? true : false;
    }

    public function getUsersFromModel(EloquentModel $model, bool $keyed = true, bool $asJson = false): array|JsonResponse
    {

        $table = $model->getTable();
        $query = $model->select("*", DB::raw("CONCAT($table.first_name,' ',$table.last_name) as name"));
        $form  = $query->get()->keyBy(function ($item){
            return ucwords(strtolower($item['name']));
        })->pluck('name','id');

        $data  = $form->map(function ($item, $key){
            return [
                'name' => $item,
                'id'   => $key,
            ];
        })->values()->all();

        if ($keyed) {
            $data = collect($data)->mapWithKeys(function ($item){
                return [$item['id'] => ucwords(strtolower($item['name']))];
            })->toArray();
        }

        return ($asJson === true) ? Response::json($data) : $data;
    }

    public function generateLivewireComponentKey($componentName): string
    {
        return uniqid().Str::slug($componentName, '_').time();
    }

    public function mapKeyValuePairArray($data): Collection | EloquentCollection
    {
        $data = is_array($data) ? collect($data) : $data;
        return $data->select('key', 'value')->get()->mapWithKeys(function ($item)
        {
            return [$item['key'] => $item['value']];
        });
    }

    public function concatName(string $table): Expression
    {
        return DB::raw("CONCAT($table.first_name,' ',$table.last_name) as name");
    }

    public function oldInput($key, $Model = null, $default = null, $returnBool = false)
    {
        // lets get what we have stored in the database,
        // if we don't have any records,
        // return old form input value
        $value = old($key, $this->fetchModelData($key, $Model, $default));
        if ($returnBool){
            if (!empty($value)){
                return true;
            }else return false;
        }
        else return $value;
    }

    public function fetchModelData($request, $Model, $default = '')
    {
        $output = null;
        if (isset($Model->$request)){
            $output = $Model->$request;
        }else if (isset($Model[$request])){
            $output = $Model[$request];
        }
        return !empty($output) ? $output : $default;
    }

    public function saveJavaScriptCookies(string $cookieName, int $duration = 60 )
    {
        if (empty($cookieName)) {
            return false;
        }

        if (!Cookie::has($cookieName))
        {
            if ( isset($_COOKIE[$cookieName]) )
            {
                Cookie::put($cookieName, $_COOKIE[$cookieName], $duration);
            }
        }

        return Cookie::get($cookieName);
    }

    public function eloquent2selectbox(Collection|EloquentCollection|EloquentModel $data, string $columnName = 'name', string $idColumnName = 'id', ?string $placeholderText = 'Select something', string $emptyDataText = 'Nothing to select'): array
    {
        if (empty($data) || $data->isEmpty())
        {
            return ['' =>  $emptyDataText];
        }

        $array = $data->mapWithKeys(function ($value) use ($columnName, $idColumnName)
        {
            return [$value->{$idColumnName} => $value->{$columnName}];
        });

        if (!empty($placeholderText) && is_string($placeholderText))
        {
            return ['' => $placeholderText] + $array->toArray();
        }

        return $array->toArray();
    }

    public function checkIfFileExistsInStorage(string $pathToFile): bool
    {
        if (Storage::disk('local')->exists($pathToFile)) {
            return true;
        }

        return false;
    }

    public function generateRelationshipSyncData(string|array $ids, array $data = [], string $columnName = 'id'): array
    {
        $ids = !is_array($ids) ? [$ids] : $ids;
        $out = [];

        foreach ($ids as $id)
        {
            if (!empty($id))
            {
                // remove duplicates, and empty values
                $out[trim($id)] = array_filter(array_unique(array_merge([
                    $columnName => pheg()->uuid()->generate(),
                ], $data)));
            }
        }

        return $out;
    }

    /**
     * @param string $cacheName cache name
     * @param callable $body data that's to be cached
     * @param int|bool $cacheTtl in seconds 86400secs = 24hrs, if boolean is just TRUE use default cache ttl
     * @return mixed
     */
    public function cache(string $cacheName, callable $body, int|bool $cacheTtl = true): mixed
    {
        // use default cache ttl if we have a boolean value
        if ($cacheTtl === true) {
            $cacheTtl = 86400;
        }

        // if we have an integer value
        if (((is_int($cacheTtl) || ctype_digit($cacheTtl)) && (int) $cacheTtl >= 1 )) {
            return Cache::remember(Str::slug(Str::lower(trim(__METHOD__ . "-{$cacheName}"))), $cacheTtl, function () use ($body) {
                return $body();
            });
        }

        return $body();
    }


    /**
     * @param EloquentModel $object
     * @param string $sessionName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handleViewCount(EloquentModel $object, string $sessionName): bool
    {
        if (!array_key_exists($object->id, session()->get($sessionName, [])))
        {
            try {
                $object->newQuery()->increment('views');
                session()->put($sessionName . '.' . $object->id, time());
                return true;
            } catch (Exception $exception) {
                return false;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isValidDatabaseConnection(string $table = 'settings'): bool
    {
        try {
            return (bool) Schema::hasTable($table);
        } catch (Exception $exception) {
            return false;
        }
    }


    public static function clearCache(): bool
    {

        try {
            Event::dispatch('cache:clearing');
            Cache::flush();

            // delete framework cache files
            $path = app()->storagePath('framework/cache');
            if (File::exists($path))
            {
                foreach (File::files($path) as $file)
                {
                    /** @var SplFileInfo $file */
                    if (preg_match('/facade-.*\.php$/', $file))
                    {
                        File::delete($file);
                    }
                }
            }

            // delete bootstrap cache files
            $path = app()->basePath('bootstrap/cache');
            if (File::exists($path))
            {
                foreach (File::allFiles($path) as $file)
                {
                    /** @var SplFileInfo $file */
                    if ($file->isFile())
                    {
                        $file = $file->getRealPath();
                        if (preg_match('/.*\.php$/', $file))
                        {
                            File::delete($file);
                        }
                    }
                }
            }

            Event::dispatch('cache:cleared');
        } catch (Exception $exception) {
            info($exception->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function clearLogFiles(): bool
    {

        try {
            Event::dispatch('logs:clearing');
            Cache::flush();

            // delete log files from other directories
            $directories = [
                'clockwork',
                'debugbar',
                'logs',
            ];

            foreach ($directories as $directory)
            {
                $path = app()->storagePath($directory);
                if (File::exists($path))
                {
                    foreach (File::allFiles($path) as $file)
                    {
                        /** @var SplFileInfo $file */
                        if ($file->isFile())
                        {
                            $file = $file->getRealPath();
                            if (!preg_match('/.*\.gitignore$/', $file))
                            {
                                File::delete($file);
                            }
                        }
                    }
                }
            }

            Event::dispatch('logs:cleared');
        } catch (Exception $exception) {
            info($exception->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function deleteStorageSymlink(): bool
    {
        try {
            return pheg()->file()->delete(app()->publicPath('storage'));
        } catch (Exception|PhegException $exception) {
            info($exception->getMessage());
        }
        return false;
    }

    /**
     * Get the Composer file contents as an array
     * @return array
     */
    public function getComposerArray(): array
    {
        return pheg()->file()->getFileData(app()->basePath('composer.json'));
    }

    /**
     * Get Installed packages & their Dependencies
     *
     * @param array $packagesArray
     * @return array
     */
    public function getPackagesAndDependencies(array $packagesArray): array
    {
        $packages = [];
        foreach ($packagesArray as $key => $value) {
            $packageFile = app()->basePath('vendor/' . $key . '/composer.json');

            if ($key !== 'php' && File::exists($packageFile)) {
                $json2             = file_get_contents($packageFile);
                $dependenciesArray = json_decode($json2, true);
                $dependencies      = array_key_exists('require', $dependenciesArray)     ? $dependenciesArray['require']     : 'No dependencies';
                $devDependencies   = array_key_exists('require-dev', $dependenciesArray) ? $dependenciesArray['require-dev'] : 'No dependencies';

                $packages[]        = [
                    'name'             => $key,
                    'version'          => $value,
                    'dependencies'     => $dependencies,
                    'dev-dependencies' => $devDependencies,
                ];
            }
        }

        return $packages;
    }

    /**
     * Get System environment details
     *
     * @return array
     */
    public function getSystemEnv(): array
    {
        return [
            'version'  => App::version(),
            'timezone' => config('app.timezone'),
            'writable' => [
                'storage_dir' => File::isWritable(app()->basePath('storage')),
                'cache_dir'   => File::isReadable(app()->basePath('bootstrap/cache')),
            ],
            'app'      => [
                'size' => pheg()->humanizer()->formatBytes(pheg()->file()->getDirectorySize(app()->basePath())),
                'mode' => [
                    'debug' => config('app.debug'),
                ],
            ],
        ];
    }

    /**
     * Check if SSL is installed or not
     * @return boolean
     */
    public function isSslIsInstalled(): bool
    {
        return (bool) !empty(Request::server('HTTPS')) && Request::server('HTTPS') !== 'off';
    }

    /**
     * Get PHP/Server environment details
     * @return array
     */
    public function getServerEnv(): array
    {
        return [
            'version'                  => phpversion(),
            'server_software'          => Request::server('SERVER_SOFTWARE'),
            'server_os'                => function_exists('php_uname') ? php_uname() : 'N/A',
            'database_connection_name' => config('database.default'),
            'ssl_installed'            => $this->isSslIsInstalled(),
            'cache_driver'             => config('cache.default'),
            'session_driver'           => config('session.driver'),
            'queue_connection'         => config('queue.default'),
            'mbstring'                 => extension_loaded('mbstring'),
            'openssl'                  => extension_loaded('openssl'),
            'curl'                     => extension_loaded('curl'),
            'exif'                     => extension_loaded('exif'),
            'pdo'                      => extension_loaded('pdo'),
            'fileinfo'                 => extension_loaded('fileinfo'),
            'bcmath'                   => extension_loaded('bcmath'),
            'pdo_mysql'                => extension_loaded('pdo_mysql'),
            'xml'                      => extension_loaded('xml'),
            'ctype'                    => extension_loaded('ctype'),
            'json'                     => extension_loaded('json'),
            'gd'                       => extension_loaded('gd'),
            'cURL'                     => extension_loaded('cURL'),
            'xdebug'                   => extension_loaded('xdebug'),
        ];
    }


    public function sortSearchResults(array|Collection $collection, string $searchTerms, string $column): Collection
    {
        if (! $collection instanceof Collection) {
            $collection = collect($collection);
        }

        return $collection->sortByDesc(function ($item) use ($searchTerms, $column) {
            $searchTerms = explode(' ', $searchTerms);

            // The bigger the weight, the higher the record
            $weight      = 0;

            // Iterate through search terms
            foreach ($searchTerms as $term) {
                if (str_contains($item->{$column}, $term)) {
                    // Increase weight if the search term is found
                    $weight += 1;
                }
            }

            return $weight;
        });
    }

    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param string $table
     * @return bool
     */
    public function isJoined(QueryBuilder|EloquentBuilder $query, string $table): bool
    {
        $joins = $query->getQuery()->joins;

        if ($joins == null) {
            return false;
        }

        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }

        return false;
    }

    public function html(array|string|null $dirty, array|string $config = null, bool $enableLessSecureWeb = false): HtmlString
    {
        return new HtmlString(pheg()->html()->clean($dirty, $config, $enableLessSecureWeb));
    }

    public function getInputValueFromQueryString(string $name): string
    {
        $value = request()->input($name);

        if (! is_string($value)) {
            return '';
        }

        return $value;
    }

    /**
     * Checks if DB credentials are working
     *
     * @param $credentials
     * @return bool
     */
    public function isValidBbCredentials($credentials): bool
    {
        $this->setDatabaseCredentials($credentials);

        try {
            DB::statement("SHOW TABLES");
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param array $credentials
     */
    public function setDatabaseCredentials(array $credentials): void
    {
        $default = config('database.default');

        config([
            "database.connections.{$default}.host"     => $credentials['host'],
            "database.connections.{$default}.database" => $credentials['database'],
            "database.connections.{$default}.username" => $credentials['username'],
            "database.connections.{$default}.password" => $credentials['password'],
            "database.connections.{$default}.prefix"   => $credentials['prefix']
        ]);
    }

    /**
     * Load helpers from a directory
     *
     * @param string $directory
     *
     * @throws PhegException
     * @since 2.0
     */
    public function autoload(string $directory): void
    {
        pheg()->file()->autoload($directory);
    }

    /**
     * Returns keyed array values
     *
     * @param array $data
     * @param bool  $keyed
     * @param bool  $transform
     *
     * @return array
     */
    public function fetchKeyedArrayValues(array $data, bool $keyed = true, bool $transform = true): array
    {
        $data = array_keys($data);
        if ($keyed) {
            $data = collect($data)->mapWithKeys(function ($item) use ($transform) {
                if ($transform) {
                    return [strtolower($item) => ucwords($item)];
                } else {
                    return [$item => $item];
                }
            });
            $data = $data->toArray();
        }
        return $data;
    }

    /**
     * @param Collection $data
     * @param int        $getOne
     *
     * @return array|false|mixed
     */
    public function getRandomIdFromEloquentCollection(Collection $data, int $getOne = 1): mixed
    {

        $data = $data->pluck('id')->toArray();
        if (!empty($data))
        {
            $data = pheg()->arr()->randomizeArray($data, $getOne);
            return $getOne == 1 ? $data[0]['value'] : $data;
        }

        return false;
    }

    /**
     * Generates random numbers
     *
     * @param int   $from
     * @param int   $to
     * @param array $exceptions
     *
     * @return int
     */
    public function random(int $from, int $to, array $exceptions = []): int
    {
        sort($exceptions); // lets us use break; in the foreach reliably
        $number = rand($from, $to - count($exceptions)); // or mt_rand()

        foreach ($exceptions as $exception)
        {
            if ($number >= $exception) {
                $number++; // make up for the gap
            } else { /*if ($number < $exception)*/
                break;
            }
        }

        return $number;
    }

    public function ucWords(string $string): string
    {
        return ucwords(strtolower(trim($string)));
    }

    public function randomizeArray(Model|Collection|null $model = null, int $counter = 1)
    {

        $data = [];
        if (!empty($model) && (($model instanceof Model) || ($model instanceof Collection))) {
            $data = $model->pluck('id')->toArray();
        }

        $data = pheg()->arr()->randomizeArray($data, $counter);
        return $counter == 1 ? $data[0]['value'] : $data;
    }


    /**
     * @param Collection $data
     * @param int        $getOne
     *
     * @return array|false|mixed
     */
    public function getRandomIdFromCollection(Collection $data, int $getOne = 1): mixed
    {

        $data = $data->pluck('id')->toArray();
        if (!empty($data))
        {
            $data = pheg()->arr()->randomizeArray($data, $getOne);
            return $getOne == 1 ? $data[0]['value'] : $data;
        }

        return false;
    }

    public function generateUsername($email, Model $model): string
    {

        $fullName = pheg()->name();
        $username = $fullName->usernameFromEmail($email);

        $query    = $model->where('username', $username)->first();
        return !$query ? $username : $fullName->makeRandomUsername($username);
    }

    public function generateRandomSalutation($counter = 1): string|null|array
    {
        $salutation = pheg()->arr()->randomizeArray(pheg()->supports()->getSalutations(), $counter);
        return $counter == 1 ? $salutation[0]['key'] : $salutation;
    }

    public function generateEmailFromUsername(string $email): string
    {
        return pheg()->name()->makeRandomUsername(pheg()->name()->usernameFromEmail($email));
    }

    public function faker(): Generator
    {
        return Factory::create();
    }
    /**
     * Write to the console's output.
     *
     * @param string                    $component
     * @param ConsoleOutput             $output
     * @param array<int, string>|string ...$arguments
     *
     * @return void
     */
    public function writeToConsoleOutput(string $component, ConsoleOutput $output, ...$arguments): void
    {
        if ($output && class_exists($component)) {
            (new $component($output))->render(...$arguments);
        } else {
            foreach ($arguments as $argument) {
                if (is_callable($argument)) {
                    $argument();
                }
            }
        }
    }



    public function getRootPathForPublicFile(string|null $file, string $directory): string|bool
    {

        $file = 'public' .DS. $directory .DS. $file;

        if (Laranail::checkIfFileExistsInStorage(($file))) {
            return Storage::disk('local')->path($file);
        }

        return false;
    }

    public function getUrlForPublicFile(string|null $file, string $directory, bool $fullUrl = true): string|bool
    {

        $file = 'public' .DS. $directory .DS. $file;

        if (Laranail::checkIfFileExistsInStorage(($file))) {
            $url = Storage::disk('local')->url($file);

            return $fullUrl ? url($url) : $url;
        }

        return false;
    }

    public function getFileAsObject(string $path): IlluminateFile
    {
        return new IlluminateFile($path);
    }


    /**
     * Create an UploadedFile object from absolute path
     *
     * @param string $pathToFile
     * @param bool   $test default true
     *
     * @return UploadedFile (Illuminate\Http\UploadedFile)
     *
     * Based of Alexandre Thebaldi answer here:
     * https://stackoverflow.com/a/32258317/6411540
     */
    public function pathToUploadedFileInstance(string $pathToFile, bool $test = true ): UploadedFile
    {
        $filesystem = new Filesystem();

        return new UploadedFile(
            path: $pathToFile,
            originalName: $filesystem->name($pathToFile) . '.' . $filesystem->extension($pathToFile),
            mimeType: $filesystem->mimeType($pathToFile),
            error: null,
            test: $test
        );
    }

    /**
     * Helper function to quickly register an observer for
     * use in a service provider boot method
     *
     * @param string $modelClass
     * @param string $observerClass
     *
     * @return void
     */
    public function registerModelObserver(string $modelClass, string $observerClass): void
    {
        $modelClass::observe($observerClass);
    }

    public function environment(): Environment
    {
        return new Environment();
    }

}
