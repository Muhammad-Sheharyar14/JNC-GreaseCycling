<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Responsable
{
    /**
     * Redirect to the unified driver portal login page after logout.
     */
    public function toResponse($request): RedirectResponse
    {
        return redirect('/driver/');
    }
}
