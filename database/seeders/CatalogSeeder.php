<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Servicio;
use App\Models\Ventanilla;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            [
                'area' => 'Atención al Ciudadano',
                'descripcion' => 'Información, orientación y trámites generales.',
                'servicios' => [
                    ['nombre' => 'Información general', 'prefijo' => 'INF', 'descripcion' => 'Consultas y orientación sobre trámites.'],
                    ['nombre' => 'Registro civil', 'prefijo' => 'RC', 'descripcion' => 'Certificados y documentos del registro civil.'],
                    ['nombre' => 'Atención preferencial', 'prefijo' => 'PREF', 'descripcion' => 'Atención prioritaria para grupos vulnerables.'],
                ],
                'ventanillas' => [
                    ['codigo' => 'V-01', 'nombre' => 'Información general'],
                    ['codigo' => 'V-02', 'nombre' => 'Registro civil'],
                    ['codigo' => 'V-03', 'nombre' => 'Atención preferencial'],
                ],
            ],
            [
                'area' => 'Licencias y Permisos',
                'descripcion' => 'Emisión y renovación de licencias y permisos.',
                'servicios' => [
                    ['nombre' => 'Licencia de conducir', 'prefijo' => 'LC', 'descripcion' => 'Emisión, renovación y duplicados.'],
                    ['nombre' => 'Permiso de funcionamiento', 'prefijo' => 'PF', 'descripcion' => 'Apertura y renovación de establecimientos.'],
                ],
                'ventanillas' => [
                    ['codigo' => 'V-04', 'nombre' => 'Licencias'],
                    ['codigo' => 'V-05', 'nombre' => 'Permisos'],
                ],
            ],
            [
                'area' => 'Recaudación y Pagos',
                'descripcion' => 'Cobros, impuestos y servicios municipales.',
                'servicios' => [
                    ['nombre' => 'Pago de impuestos', 'prefijo' => 'IMP', 'descripcion' => 'Impuestos y contribuciones.'],
                    ['nombre' => 'Pago de servicios', 'prefijo' => 'PS', 'descripcion' => 'Servicios básicos y tasas.'],
                ],
                'ventanillas' => [
                    ['codigo' => 'V-06', 'nombre' => 'Caja única'],
                ],
            ],
        ];

        foreach ($catalogo as $item) {
            $area = Area::query()->updateOrCreate(
                ['nombre' => $item['area']],
                ['descripcion' => $item['descripcion'], 'activo' => true]
            );

            foreach ($item['servicios'] as $servicio) {
                Servicio::query()->updateOrCreate(
                    ['prefijo' => $servicio['prefijo']],
                    [
                        'area_id' => $area->id,
                        'nombre' => $servicio['nombre'],
                        'descripcion' => $servicio['descripcion'],
                        'activo' => true,
                    ]
                );
            }

            foreach ($item['ventanillas'] as $ventanilla) {
                Ventanilla::query()->updateOrCreate(
                    ['codigo' => $ventanilla['codigo']],
                    [
                        'area_id' => $area->id,
                        'nombre' => $ventanilla['nombre'],
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
