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

    public function dispatchSuccessMessage(string $message)
    {
        $this->dispatchFlashMessage('success', $message);
    }

    public function dispatchErrorMessage(string $message)
    {
        $this->dispatchFlashMessage('error', $message);
    }
}
