<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateFormat implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $explode = explode("/", $value);
        if(count($explode) != 3){
          return false;
        }

        if(strlen($explode[0]) != 4){
          return false;
        }

        if(strlen($explode[1])!= 2){
          return false;
        }

        if(strlen($explode[2])!= 2){
          return false;
        }
        foreach($explode as $number){
          if(preg_match("/^\pN+$/u", $number) == 0){
            return false;
          }
          if(!checkdate($explode[1], $explode[2], $explode[0])){
            return false;
          }
        }



        return true;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El formato de la fecha debe ser AAAA/MM/DD';
    }
}
