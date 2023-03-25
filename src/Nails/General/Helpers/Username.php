<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\General\Helpers;

use Illuminate\Database\Eloquent\Model;

class Username
{

    public function __construct()
    {
    }

    private function isAvailable(Model $model, string $username, string $column = 'username', string $operand = '='): bool
    {

        $result = $model->where($column, $operand, $username)->get()->first();

        return empty($result);
    }

    /**
     * https://stackoverflow.com/questions/35037149/generate-random-username-based-on-full-name-php
     *
     * @param Model $model
     * @param string $firstname
     * @param string|null $lastname
     * @param string $column
     * @param string $operand
     * @param bool $extended
     * @param int $total
     * @return string|array|bool
     */
    public function name2username(Model $model, string $firstname, ?string $lastname = null, string $column = 'username', string $operand = '=', bool $extended = true, int $total = 200): string|array|bool
    {

        $usernames = pheg()->name()->name2username($firstname, $lastname, $extended, $total);

        if (!empty($usernames) && count($usernames) >= 1)
        {

            $maxIndex = count($usernames) - 1;
            $index    = 0;

            // loop through all the usernames and find the one that is available
            do {

                $status = $this->isAvailable($model, $usernames[$index], $column, $operand);
                $limit  = $index >= $maxIndex;
                $index += 1;

                if($limit){
                    break;
                }

            } while (!$status);

            // if all of them is not available concatenate the first name with a unique number
            if(!$status) {
                return $firstname . rand(10, 999);
            }

            return $usernames;
        }

        return false;
    }


}