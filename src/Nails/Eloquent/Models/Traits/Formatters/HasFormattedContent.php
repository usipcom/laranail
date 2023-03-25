<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Eloquent\Models\Traits\Formatters;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Mews\Purifier\Facades\Purifier;

trait HasFormattedContent
{
    /*
    |--------------------------------------------------------------------------
    | Content formatters
    |--------------------------------------------------------------------------
    |
    | Formats content fields
    |
    */

    public function textTitle() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $attribute['title'] ?? $value;
            },
            set: function($value) {
                return [
                    'title' => ucwords(trim($value)),
                ];
            },
        );
    }

    public function textDescription() :Attribute
    {
        return new Attribute(
            get: function($value, $attribute) {
                return $attribute['description'] ?? $value;
            },
            set: function($value) {
                return [
                    'description' => ucfirst(strtolower(trim($value))),
                ];
            },
        );
    }

    /**
     * Updates the content and html attribute of the given model.
     *
     * @param string $rawHtml
     * @param $purifierConfig
     * @return HasFormattedContent $this
     */
    public function purifyTextContent(string $rawHtml, $purifierConfig): self
    {
        $this->content = Purifier::clean($rawHtml, ['HTML.Allowed' => '']);
        $this->html    = Purifier::clean($rawHtml, $purifierConfig);

        return $this;
    }

    /**
     * Cuts the content of a post content if it's too long.
     *
     * @param string $attr
     * @param int $maxlength
     *
     * @return string
     */
    public function shortenTextContent(string $attr = 'content', int $maxlength = 50): string
    {

        $content = $this->{$attr};
        if (strlen($content) > $maxlength) {
            return substr($content, 0, $maxlength).'...';
        }

        return $content;
    }
}