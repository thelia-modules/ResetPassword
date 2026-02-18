<?php

declare(strict_types=1);

namespace ResetPassword\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Thelia\Model\Customer;

class PasswordResetSuccessEvent extends Event
{
    public const NAME = 'reset_password.password_reset_success';

    public function __construct(
        private readonly Customer $customer,
    ) {
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }
}
