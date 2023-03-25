<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Auth\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Simtabi\Laranail\Nails\Auth\Auth;

trait HasAuth
{

    private ?string         $userEmail = null;
    private string|null|int $userId    = null;
    private ?string         $guard     = null;

    /**
     * @param string|null $userEmail
     * @return static
     */
    public function setUserEmail(?string $userEmail): static
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * @param int|string|null $userId
     * @return static
     */
    public function setUserId(int|string|null $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param string|null $guard
     * @return static
     */
    public function setGuard(?string $guard): static
    {
        $this->guard = $guard;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    /**
     * @return int|string|null
     */
    public function getUserId(): int|string|null
    {
        return $this->userId;
    }

    /**
     * @return string|null
     */
    public function getGuard(): ?string
    {
        return $this->guard;
    }

    /**
     * Get the current user of the application.
     *
     * @return Model|Authenticatable|null
     * @throws Exception
     */
    public function getUserProperty(): Model | Authenticatable|null
    {
        return $this->authHelper($this->guard)->user();
    }

    /**
     * @param string|null $guard
     * @return Auth
     * @throws Exception
     */
    public function authHelper(?string $guard = null): Auth
    {
        return Auth::invoke($this->guard ?? $guard);
    }

}