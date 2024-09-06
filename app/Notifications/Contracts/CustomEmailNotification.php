<?php

declare(strict_types=1);

namespace App\Notifications\Contracts;

interface CustomEmailNotification
{
    public function getEmailAddress(): string;
}
