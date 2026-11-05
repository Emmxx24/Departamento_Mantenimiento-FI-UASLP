<?php

namespace App\View\Components;

use App\Models\Configuracion;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Footer extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $config = Configuracion::obtener();

        return view('components.footer', compact('config'));
    }
}
