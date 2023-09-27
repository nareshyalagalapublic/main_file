<?php

namespace Modules\Account\Events;

use Illuminate\Queue\SerializesModels;

class UpdateCustomer
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $customer;
    public function __construct($customer,$request)
    {
        $this->request = $request;
        $this->customer = $customer;
    }
}
