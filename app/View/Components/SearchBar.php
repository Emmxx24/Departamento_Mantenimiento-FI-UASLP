<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Edificio;
use App\Models\Lugar;
use App\Models\Servicio;

class SearchBar extends Component
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
    public function render()
    {
        $edificios          = Edificio::where('activo', 1)->get();
        $edificiosInactivos = Edificio::where('activo', 0)->get();
        $servicios          = Servicio::where('activo', 1)->get();
        $serviciosInactivos = Servicio::where('activo', 0)->get();
        $lugares            = Lugar::where('activo', 1)->get();
        $lugaresInactivos   = Lugar::where('activo', 0)->get();

        return view('components.search-bar', compact(
            'edificios',
            'edificiosInactivos',
            'servicios',
            'serviciosInactivos',
            'lugares',
            'lugaresInactivos'
        ));
    }
}
