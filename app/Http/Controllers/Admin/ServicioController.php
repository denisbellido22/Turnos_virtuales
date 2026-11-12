<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServicioRequest;
use App\Models\Area;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $areaId = $request->integer('area_id');

        $servicios = Servicio::query()
            ->with('area')
            ->withCount('turnos')
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->when($areaId > 0, fn ($query) => $query->where('area_id', $areaId))
            ->orderBy('area_id')
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        $areas = Area::query()->orderBy('nombre')->get();

        return view('admin.servicios.index', compact('servicios', 'areas', 'buscar', 'areaId'));
    }

    public function create(): View
    {
        return view('admin.servicios.create', [
            'servicio' => new Servicio(['activo' => true]),
            'areas' => $this->areas(),
        ]);
    }

    public function store(ServicioRequest $request): RedirectResponse
    {
        $servicio = Servicio::create($request->validated());

        Log::info('Servicio creado', ['servicio_id' => $servicio->id, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.servicios.index')
            ->with('success', "Servicio {$servicio->nombre} creado correctamente.");
    }

    public function edit(Servicio $servicio): View
    {
        return view('admin.servicios.edit', [
            'servicio' => $servicio->load('area'),
            'areas' => $this->areas(),
        ]);
    }

    public function update(ServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $servicio->update($request->validated());

        Log::info('Servicio actualizado', ['servicio_id' => $servicio->id, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.servicios.index')
            ->with('success', "Servicio {$servicio->nombre} actualizado correctamente.");
    }

    public function destroy(Request $request, Servicio $servicio): RedirectResponse
    {
        if ($servicio->turnos()->exists()) {
            return back()->with('error', 'No se puede eliminar el servicio porque tiene turnos asociados. Puedes desactivarlo.');
        }

        $nombre = $servicio->nombre;
        $servicio->delete();

        Log::info('Servicio eliminado', ['servicio' => $nombre, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.servicios.index')
            ->with('success', "Servicio {$nombre} eliminado.");
    }

    private function areas()
    {
        return Area::query()->orderBy('nombre')->get();
    }
}
