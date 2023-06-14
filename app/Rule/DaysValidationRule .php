<?php

use Illuminate\Contracts\Validation\Rule;

class DaysValidationRule implements Rule
{
    protected $isPreOrder;
    protected $isDaily;

    public function __construct($isPreOrder, $isDaily)
    {
        $this->isPreOrder = $isPreOrder;
        $this->isDaily = $isDaily;
    }

    public function passes($attribute, $value)
    {
        return ($this->isPreOrder == 1 && $this->isDaily == 1) ? !empty($value) : true;
    }

    public function message()
    {
        return 'The days field is required';
    }
}
