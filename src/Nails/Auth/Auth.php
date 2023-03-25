<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Exception;

class Auth
{

    private Guard|StatefulGuard $auth;
    private ?string             $guard = null;

    /**
     * @throws Exception
     */
    private function __construct(string $guard)
    {
        if (empty($guard)) {
            throw new Exception("You must provide a guard before you can use the Auth support helper class");
        }

        $this->guard = $guard;
        $this->auth  = AuthFacade::guard($guard);
    }

    /**
     * @throws Exception
     */
    public static function invoke(string $guard): self
    {
        return new self($guard);
    }

    /**
     * @return string|null
     */
    public function guard(): ?string
    {
        return $this->guard;
    }

    /**
     * @return Guard|StatefulGuard
     */
    public function auth(): Guard|StatefulGuard
    {
        return $this->auth;
    }

    /**
     * @return Authenticatable|null
     */
    public function user(): Authenticatable|null
    {
        return $this->auth->user();
    }

    /**
     * @return int|string|null
     * @throws Exception
     */
    public function id(): int|string|null
    {
        return $this->user()->id ?? null;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function email(): string|null
    {
        return $this->user()->email ?? null;
    }

}