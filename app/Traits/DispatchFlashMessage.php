<?php

namespace App\Traits;

trait DispatchFlashMessage
{
    public function dispatchFlashMessage(string $type, string $message)
    {
        $this->dispatch('show-message', [
            'type' => $type,
            'message' => __($message),
        ]);
    }
}
