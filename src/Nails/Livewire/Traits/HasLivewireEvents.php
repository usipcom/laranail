<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Livewire\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Simtabi\Larabell\HasLarabell;
use Simtabi\Laramodal\Traits\HasLaramodal;
use Simtabi\Laranail\Nails\Auth\Auth;

trait HasLivewireEvents
{

    use WithPagination;
    use WithFileUploads;
    use HasLarabell;
    use HasLaramodal;

    protected $validationAttributes        = [];
    protected $validationMessages          = [];
    protected $validationRules             = [];
    protected $queryStringParams           = [];
    protected $paginationTheme             = 'bootstrap';
    protected $contextMessages             = [];

    private   string $successMessageKey    = 'success';
    private   string $errorMessageKey      = 'error';
    protected array  $responseMessage      = [];

    protected $changeTriggered             = 0;
    protected $duration                    = 3000;

    protected $eventListeners              = [
        'executesCustomEvents' => 'executesCustomEvents',
        'updateInputValue'     => 'updateInputValue',
        'hasErrors'            => 'hasErrors',
        'deleted'              => 'deleted',
        'refresh'              => '$refresh',
        'delete'               => 'delete',
        'saved'                => 'saved',
        'save'                 => 'save',
    ];

    protected $messages                    = [];
    protected $rules                       = [];
    public    $args                        = [];
    protected $hydrates                    = [];

    public array   $modelBinding        = [];    // model data binding
    public ?string $redirectToActionUrl = null;


    public function setContextMessages(array $messages): static
    {
        if (is_array($this->contextMessages) && (count($this->contextMessages) >= 1)) {
            $this->contextMessages = array_merge($this->contextMessages, $messages);
        } else {
            $this->contextMessages = $messages;
        }
        return $this;
    }

    public function getContextMessages($key = null): array
    {
        return !empty($key) ? pheg()->arr()->fetch($key, $this->contextMessages) : $this->contextMessages;
    }

    public function setResponseMessage(string $message, bool $isError = true): static
    {
        $this->responseMessage[$isError ? $this->errorMessageKey : $this->successMessageKey] = $message;

        return $this;
    }

    public function getResponseMessage(bool $isError = true, bool $all = false): array|string|null
    {
        if ($all)
        {
            return $this->responseMessage;
        }

        return $this->responseMessage[($isError ? $this->errorMessageKey : $this->successMessageKey)] ?? null;
    }

    public function getContextMessage($key, $value = null): ?string
    {
        return sprintf(pheg()->arr()->fetch($key, $this->contextMessages),
            (!empty($value) ? ('<strong>'. $value .'</strong>') : $value)
        );
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setPaginationTheme(string $paginationTheme): static
    {
        $this->paginationTheme = $paginationTheme;
        return $this;
    }

    public function getPaginationTheme(): string
    {
        return $this->paginationTheme;
    }

    public function setQueryStringParams(array $queryStringParams): static
    {
        if (is_array($this->queryStringParams) && (count($this->queryStringParams) >= 1)) {
            $this->queryStringParams = array_merge($this->queryStringParams, $queryStringParams);
        } else {
            $this->queryStringParams = $queryStringParams;
        }
        return $this;
    }

    public function getQueryStringParams(): array
    {
        return $this->queryStringParams;
    }

    public function setValidationMessageAttributes(array $attributes): static
    {
        if (is_array($this->validationAttributes) && (count($this->validationAttributes) >= 1)) {
            $this->validationAttributes = array_merge($this->validationAttributes, $attributes);
        } else {
            $this->validationAttributes = $attributes;
        }
        return $this;
    }

    public function getValidationMessageAttributes(): array
    {
        return $this->validationAttributes;
    }

    public function setValidationMessages(array $messages): static
    {
        if (is_array($this->validationMessages) && (count($this->validationMessages) >= 1)) {
            $this->validationMessages = array_merge($this->validationMessages, $messages);
        } else {
            $this->validationMessages = $messages;
        }
        return $this;
    }

    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }

    public function setValidationRules(array $validationRules): static
    {
        if (is_array($this->validationRules) && (count($this->validationRules) >= 1)) {
            $this->validationRules = array_merge($this->validationRules, $validationRules);
        } else {
            $this->validationRules = $validationRules;
        }
        return $this;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function setArgs(array $args, ?string $key = null): static
    {
        $args = json_decode(json_encode($args),true);

        if (!empty($key)) {
            $this->args[$key] = $args;
        }else{
            $this->args = $args;
        }

        return $this;
    }

    public function getArgs(bool $asObject = true): array|object
    {
        return $asObject ? pheg()->transfigure()->toObject($this->args) : $this->args;
    }

    public function setHydrates(array $hydrates): static
    {
        if (is_array($this->hydrates) && (count($this->hydrates) >= 1)) {
            $this->hydrates = array_merge($this->hydrates, $hydrates);
        } else {
            $this->hydrates = $hydrates;
        }

        return $this;
    }

    public function getHydrates(): array
    {
        return $this->hydrates;
    }

    public function setChangeTriggered(bool $changeTriggered = true): self
    {
        $this->changeTriggered = !$changeTriggered ? 1 : 0;

        return $this;
    }

    public function isChangeTriggered(): bool
    {
        return (bool) $this->changeTriggered;
    }

    public function setEventListeners(array $listeners): static
    {
        if (is_array($this->eventListeners) && (count($this->eventListeners) >= 1)) {
            $this->eventListeners = array_merge($this->eventListeners, $listeners);
        } else {
            $this->eventListeners = $listeners;
        }
        return $this;
    }

    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }


    /*
    |--------------------------------------------------------------------------
    | General helpers
    |--------------------------------------------------------------------------
    |
    |
    */

    public function hasErrors(): bool
    {
        $messages = $this->getErrorBag()->getMessages();
        return (bool) is_array($messages) && (count($messages) >= 1);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getPaginationLoopCounter($paginator, $loop)
    {

        if ($paginator instanceof LengthAwarePaginator)
        {
            return $loop->iteration + (($paginator->currentPage() -1) * $paginator->perPage());
        }

        return $loop->iteration;
    }

    /*
    |--------------------------------------------------------------------------
    | Component initialization
    |--------------------------------------------------------------------------
    |
    |
    */

    public function initComponent()
    {

        // Get and merge with default listeners
        if (is_array($this->eventListeners) && (count($this->eventListeners) >= 1))
        {

            if (method_exists($this, 'getListeners')) {
                if (is_array($this->listeners) && (count($this->listeners) >= 1)) {
                    $this->listeners = array_merge($this->listeners, $this->getListeners());
                } else {
                    $this->listeners = $this->getListeners();
                }
            }

            // Merge with incoming
            if (is_array($this->listeners) && (count($this->listeners) >= 1)) {
                $this->listeners = array_merge($this->listeners, $this->eventListeners);
            } else {
                $this->listeners = $this->eventListeners;
            }

        }

        // Get and merge with default query strings
        if (is_array($this->queryStringParams) && (count($this->queryStringParams) >= 1))
        {

            $queryStringMethod = [];
            if (method_exists($this, 'queryString')) {
                $queryStringMethod = array_merge($this->queryStringParams, $this->queryString());
            }else{
                $queryStringMethod = $this->queryStringParams;
            }

            $queryStringProperty = [];
            if (property_exists($this, 'queryString')) {
                $queryStringProperty = array_merge($this->queryStringParams, $this->queryString);
            }else{
                $queryStringProperty = $this->queryStringParams;
            }

            $queryString = array_merge($queryStringMethod, $queryStringProperty);

            // Merge with incoming
            if (is_array($this->queryString) && (count($this->queryString) >= 1)) {
                $this->queryString = array_merge($this->queryString, $queryString);
            } else {
                $this->queryString = $queryString;
            }
        }

        // Get and merge with default validation message attributes
        if (is_array($this->validationAttributes) && (count($this->validationAttributes) >= 1))
        {

            if (property_exists($this, 'validationAttributes')) {
                $attributes = array_merge($this->validationAttributes, $this->validationAttributes);
            }else{
                $attributes = $this->validationAttributes;
            }

            // Merge with incoming
            if (is_array($this->validationAttributes) && (count($this->validationAttributes) >= 1)) {
                $this->validationAttributes = array_merge($this->validationAttributes, $attributes);
            } else {
                $this->validationAttributes = $attributes;
            }
        }

        // Get and merge with default validation messages
        if (is_array($this->validationMessages) && (count($this->validationMessages) >= 1))
        {

            if (property_exists($this, 'messages')) {
                $messages = array_merge($this->validationMessages, $this->messages);
            }else{
                $messages = $this->validationMessages;
            }

            // Merge with incoming
            if (is_array($this->messages) && (count($this->messages) >= 1)) {
                $this->messages = array_merge($this->messages, $messages);
            } else {
                $this->messages = $messages;
            }
        }

        // Get and merge with default validation rules
        if (is_array($this->validationRules) && (count($this->validationRules) >= 1))
        {

            if (property_exists($this, 'rules')) {
                $rules = array_merge($this->validationRules, $this->rules);
            }else{
                $rules = $this->validationRules;
            }

            // Merge with incoming
            if (is_array($this->rules) && (count($this->rules) >= 1)) {
                $this->rules = array_merge($this->rules, $rules);
            } else {
                $this->rules = $rules;
            }
        }

        return $this;
    }

    public function updateInputValue($value, $key)
    {
        if (property_exists($this, $key) || isset($this->{$key}))
        {
            $this->{$key} = $value;
        }

        return $this;
    }

    public function hydrate()
    {
        foreach ($this->hydrates as $hydrate)
        {
            $this->emit($hydrate);
        }

        $this->dispatchBrowserEvent('componentUpdated', ['status' => true]);

        return $this;
    }

    protected function addToModelBinding(string $key, mixed $value)
    {
        $this->modelBinding[trim($key)] = $value;
    }

    public function getModelBindingByKey(string $key, mixed $default = null)
    {
        return $this->modelBinding[trim($key)] ?? $default;
    }

    public function getModelBinding()
    {
        return $this->modelBinding;
    }

    protected function initModelBinding(string $classNamespace, string|int $recordId = null, array $columns = [])
    {
        // init model and get fillable
        $model   = new $classNamespace();

        // get incoming columns merge
        $columns = count($columns) >= 1 ? array_merge($model->getFillable(), $columns) : $model->getFillable();

        // remove empty and duplicates
        $columns = array_filter(array_unique($columns));

        $data = [];
        foreach ($columns as $column)
        {
            $column = trim($column);
            if (!empty($column))
            {
                $data[$column] = null;
            }
        }

        // sort
        ksort($data, SORT_REGULAR);

        // assign sorted model columns
        $this->modelBinding = $data;

        // prefill ticket data if ticket id has been provided
        if (!empty($recordId))
        {
            if ($query = $model::find($recordId)->first())
            {
                $this->modelBinding = $query->toArray();
            }
        }
    }

    public function isBoundModelColumnEmpty($column): bool
    {
        if (property_exists($this, $column)){
            if (empty($this->{$column})) {
                return true;
            }
        }elseif (is_null($this->getModelBindingByKey($column)) || empty($this->getModelBindingByKey($column))){
            return true;
        }

        return false;
    }

    public function refreshComponents(array $components)
    {
        $this->dispatchBrowserEvent('refreshComponents', $components);

        return $this;
    }

    public function refreshJsComponents(...$args)
    {
        $this->dispatchBrowserEvent('refreshJsComponents', $args);

        return $this;
    }

    public function refreshComponent(?string $componentName = null, bool $toParent = false, ...$params)
    {
        return $this->triggerEvent('refresh', $componentName, $toParent, ...$params);
    }

    public function executesCustomEvents()
    {
        if (method_exists($this, 'eventsToBeExecuted'))
        {
            $this->eventsToBeExecuted();
        }

        return $this;
    }

    public function fireCustomEvents(?string $componentName = null, bool $toParent = false, ...$params)
    {
        return $this->triggerEvent('executesCustomEvents', $componentName, $toParent, ...$params);
    }

    protected function triggerEvent($event, ?string $componentName = null, bool $toParent = false, ...$params)
    {

        if (!empty($componentName)) {
            if ($toParent) {
                $this->emitUp($event, $params);
            }else{
                $this->emitTo($componentName, $event, $params);
            }
        }else{
            $this->emitSelf($event, $params);
        }

        return $this;
    }


    public function getAuthenticatedUser(): Authenticatable|Model|bool|null
    {
        $fromAuthHelper = function ($object)
        {
            if (!empty($object) && isset($object->guard) && !empty($object->guard)) {
                $invoke = Auth::invoke($object->guard);
                if ($invoke->auth()->check()) {
                    return $invoke->user();
                }
            }
            return false;
        };

        $fromAuthModel = function ($object): Model|false|null
        {
            if (!empty($object)) {
                $object = app($object->class)->find($object->user_id);
                if ($object) {
                    return $object;
                }
            }
            return false;
        };

        // initialize auth helper with logged-in user based on the given guard
        $data = $this->getArgs(true)->auth ?? null;

        return $fromAuthHelper($data) ?? $fromAuthModel($data);
    }

    public function setRedirectToActionUrl(?string $url): static
    {
        $this->redirectToActionUrl = $url;
        return $this;
    }

    public function getRedirectToActionUrl(): ?string
    {
        return $this->redirectToActionUrl;
    }

    public function redirectToActionUrl(): static
    {
        $redirectUrl = $this->redirectToActionUrl;
        if (!empty($redirectUrl))
        {
            $this->redirect($redirectUrl);
        }

        return $this;
    }

    public function isModalComponent(): bool
    {
        return $this->getArgs()->is_modal ?? true;
    }

    public function isAuthenticated(): bool
    {
        return !empty($this->getAuthenticatedUser()) && $this->getAuthenticatedUser();
    }

    public function getAuthId()
    {
        return $this->getAuthenticatedUser()->id ?? null;
    }

    public function getAuthEmail()
    {
        return $this->getAuthenticatedUser()->email ?? null;
    }

    public function seconds2milliseconds(int $seconds): int
    {
        return round($seconds * 1000);
    }

}
