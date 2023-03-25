<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits\Multimedia;

use Illuminate\Support\Facades\Storage;

trait HasAvatar
{

    /*
    |--------------------------------------------------------------------------
    | Image formatters
    |--------------------------------------------------------------------------
    |
    | Formats gravatar
    |
    */

    public function gravatar()
    {
        return pheg()->avatar()->getGravatar($this->email);
    }

    /**
     * Modify the avatar attribute to use Gravatar if null
     *
     * @return string|null
     */
    public function getAvatarAttribute() : string|null
    {

        if (property_exists($this, 'getAvatarField'))
        {
            if (empty($this->{$this->getAvatarField()}))
            {
                return $this->getGravatar();
            }

            return $this->getAvatarUrl($this->{$this->getAvatarField()});
        }

        return null;
    }

    /**
     * Get the avatar URL from Storage
     *
     * @param string $path
     * @return string
     */
    public function getAvatarUrl(string $path) : string
    {
        if (Storage::exists($path)) {
            return Storage::url($path);
        }
        return $path;
    }

    /**
     * Get the Avatar for the User using Gravatar
     *
     * @param integer $size
     * @param string $d
     * @param string $r
     * @param array $atts
     * @return string
     */
    public function getGravatar($size = 128, $d = 'mp', $r = 'g', $atts = []): string
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($this->{$this->getEmailField()})));
        $url .= "?s=$size&d=$d&r=$r";
        return $url;
    }

    /**
     * Get the avatar field to check
     *
     * @return string
     */
    private function getAvatarField() : string
    {
        return 'avatar';
    }

    /**
     * Get the email field to be used for Gravatar
     *
     * @return string
     */
    private function getEmailField(): string
    {
        return 'email';
    }

}
