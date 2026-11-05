<?php

namespace Database\Seeders;

use App\Models\Aula;
use App\Models\Mensaje;
use App\Models\Servicio;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener usuarios por correo
        $superAdmin    = User::where('email', 'a323352@alumnos.uaslp.mx')->first(); // Emmanuel
        $adminFernando = User::where('email', 'a355521@alumnos.uaslp.mx')->first(); // Fernando
        $adminChristian= User::where('email', 'a353262@alumnos.uaslp.mx')->first(); // Christian
        $solicLeslie   = User::where('email', 'a355231@alumnos.uaslp.mx')->first(); // Leslie
        $solicAntonio  = User::where('email', 'a256316@alumnos.uaslp.mx')->first(); // Antonio

        // Obtener servicios por nombre
        $servicios = Servicio::pluck('id_servicio', 'nombre');

        // Definición de los 12 tickets basados en el catálogo existente
        $ticketsData = [
            // 1. Electricidad - Atendiendo (Edificio I, Piso 0, I-1)
            [
                'asunto'           => 'Falla de iluminación en lámparas fluorescentes',
                'descripcion'      => 'La mitad de las lámparas fluorescentes del salón no encienden y dos parpadean continuamente, lo cual dificulta la visibilidad durante las clases matutinas.',
                'servicio'         => 'Electricidad',
                'edificio'         => 'Edificio I',
                'piso'             => 0,
                'aula'             => 'I-1',
                'id_solicitante'   => $solicLeslie?->id,
                'id_admin_atiende' => $adminFernando?->id,
                'prioridad'        => 'Media',
                'estado'           => 'Atendiendo',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(3)->setTime(8, 30, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [
                    [
                        'remitente' => $solicLeslie?->id,
                        'texto'     => 'Buen día, reporto que el problema empeoró; tres balastras dejaron de funcionar por completo.',
                        'fecha'     => Carbon::now()->subDays(2)->setTime(10, 15, 0),
                    ],
                    [
                        'remitente' => $adminFernando?->id,
                        'texto'     => 'Enterado, Leslie. Se solicitó el cambio de balastras y tubos LED a almacén. Mañana a primera hora acude el electricista.',
                        'fecha'     => Carbon::now()->subDays(1)->setTime(12, 0, 0),
                    ],
                ],
            ],

            // 2. Plomería - Atendiendo, Alta prioridad (Edificio I, Piso 1, Baño hombres)
            [
                'asunto'           => 'Fuga continua de agua en lavabo principal',
                'descripcion'      => 'Existe una fuga constante en la llave mezcladora y contracanasta del lavabo, provocando encharcamiento en el piso del baño y riesgo de resbalones.',
                'servicio'         => 'Plomería',
                'edificio'         => 'Edificio I',
                'piso'             => 1,
                'aula'             => 'Baño hombres',
                'id_solicitante'   => $solicAntonio?->id,
                'id_admin_atiende' => $adminChristian?->id,
                'prioridad'        => 'Alta',
                'estado'           => 'Atendiendo',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(2)->setTime(9, 45, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [
                    [
                        'remitente' => $solicAntonio?->id,
                        'texto'     => 'Colocamos una cubeta provisional para contener el agua mientras lo revisan.',
                        'fecha'     => Carbon::now()->subDays(2)->setTime(11, 0, 0),
                    ],
                    [
                        'remitente' => $adminChristian?->id,
                        'texto'     => 'Se cerró la llave de paso temporalmente. Se sustituirá el empaque y la manguera flexible hoy mismo.',
                        'fecha'     => Carbon::now()->subDays(1)->setTime(14, 20, 0),
                    ],
                ],
            ],

            // 3. Cerrajería - Pendiente, Próximo a cerrar (~27 días) (Edificio H, Piso 0, Aula H-1)
            [
                'asunto'           => 'Chapa trabada en puerta de acceso principal',
                'descripcion'      => 'La cerradura de la puerta principal se atora con frecuencia, impidiendo la apertura ágil para los profesores al iniciar clases.',
                'servicio'         => 'Cerrajería',
                'edificio'         => 'Edificio H',
                'piso'             => 0,
                'aula'             => 'Aula H-1',
                'id_solicitante'   => $solicLeslie?->id,
                'id_admin_atiende' => $adminFernando?->id,
                'prioridad'        => 'Media',
                'estado'           => 'Pendiente',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(27)->setTime(11, 10, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [
                    [
                        'remitente' => $solicLeslie?->id,
                        'texto'     => 'Sigue costando mucho trabajo abrir la puerta con la llave asignada.',
                        'fecha'     => Carbon::now()->subDays(26)->setTime(9, 0, 0),
                    ],
                ],
            ],

            // 4. Electricidad - Atendiendo, Alta prioridad, Próximo a cerrar (~28 días) (Edificio I, Piso 2, LCA-1)
            [
                'asunto'           => 'Cortocircuito en regleta de contactos regulados',
                'descripcion'      => 'Al conectar los equipos de cómputo se originaron chispas en la canaleta perimetral, botando la pastilla térmica y dejando sin luz una fila de terminales.',
                'servicio'         => 'Electricidad',
                'edificio'         => 'Edificio I',
                'piso'             => 2,
                'aula'             => 'LCA-1',
                'id_solicitante'   => $solicAntonio?->id,
                'id_admin_atiende' => $adminChristian?->id,
                'prioridad'        => 'Alta',
                'estado'           => 'Atendiendo',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(28)->setTime(13, 0, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [
                    [
                        'remitente' => $adminChristian?->id,
                        'texto'     => 'Se reemplazó el interruptor termomagnético en el tablero secundario. ¿Podrían validar si las computadoras ya operan con normalidad?',
                        'fecha'     => Carbon::now()->subDays(5)->setTime(16, 30, 0),
                    ],
                ],
            ],

            // 5. Plomería - Sin asignar (para panel Superadmin), Alta prioridad (Torre de Ingeniería, Piso 1, Aula T-101)
            [
                'asunto'           => 'Filtración de agua en plafones por lluvia',
                'descripcion'      => 'Se aprecian goteras en el centro del aula que caen cerca de las mesas de trabajo, poniendo en riesgo material y equipo de medición.',
                'servicio'         => 'Plomería',
                'edificio'         => 'Torre de Ingeniería',
                'piso'             => 1,
                'aula'             => 'Aula T-101',
                'id_solicitante'   => $solicLeslie?->id,
                'id_admin_atiende' => null, // Sin asignar
                'prioridad'        => 'Alta',
                'estado'           => 'Pendiente',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(1)->setTime(7, 50, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [],
            ],

            // 6. Carpintería - Sin asignar (para panel Superadmin), Media prioridad (Edificio I, Piso 2, CUBO)
            [
                'asunto'           => 'Puerta de madera descuadrada y no cierra',
                'descripcion'      => 'La puerta principal del cubículo roza fuertemente contra el marco inferior y el piso, impidiendo cerrarla con llave para resguardar los equipos.',
                'servicio'         => 'Carpintería',
                'edificio'         => 'Edificio I',
                'piso'             => 2,
                'aula'             => 'CUBO',
                'id_solicitante'   => $solicAntonio?->id,
                'id_admin_atiende' => null, // Sin asignar
                'prioridad'        => 'Media',
                'estado'           => 'Pendiente',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subHours(16),
                'fecha_cierre'     => null,
                'mensajes'         => [],
            ],

            // 7. Pintura - Pendiente, asignado a Superadmin (Edificio H, Piso 1, Aula H-3)
            [
                'asunto'           => 'Desprendimiento de pintura y humedad en muro frontal',
                'descripcion'      => 'El muro junto al pizarrón muestra descascaramiento severo de pintura y caída de polvo de yeso, requiriendo raspado, sellador y pintura vinílica.',
                'servicio'         => 'Pintura',
                'edificio'         => 'Edificio H',
                'piso'             => 1,
                'aula'             => 'Aula H-3',
                'id_solicitante'   => $solicLeslie?->id,
                'id_admin_atiende' => $superAdmin?->id,
                'prioridad'        => 'Baja',
                'estado'           => 'Pendiente',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(6)->setTime(10, 0, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [],
            ],

            // 8. Cerrajería - Terminado (Edificio I, Piso 0, Oficina intendente)
            [
                'asunto'           => 'Cambio de chapa de archivero de intendencia',
                'descripcion'      => 'Se requiere sustitución del tambor de seguridad en el archivero de expedientes debido al extravío de llaves y desgaste del cilindro.',
                'servicio'         => 'Cerrajería',
                'edificio'         => 'Edificio I',
                'piso'             => 0,
                'aula'             => 'Oficina intendente',
                'id_solicitante'   => $solicLeslie?->id,
                'id_admin_atiende' => $adminFernando?->id,
                'prioridad'        => 'Baja',
                'estado'           => 'Terminado',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(15)->setTime(8, 0, 0),
                'fecha_cierre'     => Carbon::now()->subDays(2)->setTime(14, 0, 0),
                'mensajes'         => [
                    [
                        'remitente' => $adminFernando?->id,
                        'texto'     => 'Se concluyó el cambio de cerradura y se entregaron dos juegos de llaves al encargado de intendencia.',
                        'fecha'     => Carbon::now()->subDays(2)->setTime(13, 50, 0),
                    ],
                ],
            ],

            // 9. Carpintería - Terminado (Edificio I, Piso 0, I-4)
            [
                'asunto'           => 'Reparación y ajuste de bancas escolares',
                'descripcion'      => 'Cuatro bancas universitarias tienen paletas sueltas y tornillería floja, con riesgo de causar pellizcos o caídas a los alumnos.',
                'servicio'         => 'Carpintería',
                'edificio'         => 'Edificio I',
                'piso'             => 0,
                'aula'             => 'I-4',
                'id_solicitante'   => $solicAntonio?->id,
                'id_admin_atiende' => $adminChristian?->id,
                'prioridad'        => 'Media',
                'estado'           => 'Terminado',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(11)->setTime(12, 0, 0),
                'fecha_cierre'     => Carbon::now()->subDays(3)->setTime(17, 30, 0),
                'mensajes'         => [
                    [
                        'remitente' => $adminChristian?->id,
                        'texto'     => 'Se reforzaron las paletas con tornillería de grado y escuadras metálicas. Las bancas están listas para su uso.',
                        'fecha'     => Carbon::now()->subDays(3)->setTime(17, 0, 0),
                    ],
                ],
            ],

            // 10. Pintura - Cerrado (Torre de Ingeniería, Piso 0, Aula T-PB1)
            [
                'asunto'           => 'Mantenimiento y esmalte en cancelería exterior',
                'descripcion'      => 'Aplicación de esmalte anticorrosivo en barandales y marcos exteriores de la planta baja que presentan oxidación visible.',
                'servicio'         => 'Pintura',
                'edificio'         => 'Torre de Ingeniería',
                'piso'             => 0,
                'aula'             => 'Aula T-PB1',
                'id_solicitante'   => $superAdmin?->id,
                'id_admin_atiende' => $adminFernando?->id,
                'prioridad'        => 'Baja',
                'estado'           => 'Cerrado',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(35)->setTime(9, 0, 0),
                'fecha_cierre'     => Carbon::now()->subDays(12)->setTime(16, 0, 0),
                'mensajes'         => [],
            ],

            // 11. Electricidad - Atendiendo, Alta prioridad (Edificio I, Piso 2, LCB-1)
            [
                'asunto'           => 'Falla y zumbido en centro de carga secundario',
                'descripcion'      => 'Se escucha un zumbido eléctrico en el centro de carga del piso 2 y no responden los contactos que alimentan las prácticas de electrónica analógica.',
                'servicio'         => 'Electricidad',
                'edificio'         => 'Edificio I',
                'piso'             => 2,
                'aula'             => 'LCB-1',
                'id_solicitante'   => $solicLeslie?->id,
                'id_admin_atiende' => $adminFernando?->id,
                'prioridad'        => 'Alta',
                'estado'           => 'Atendiendo',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subHours(8),
                'fecha_cierre'     => null,
                'mensajes'         => [
                    [
                        'remitente' => $solicLeslie?->id,
                        'texto'     => 'Tenemos laboratorio programado a las 3:00 PM, ¿será posible reanudar el suministro antes?',
                        'fecha'     => Carbon::now()->subHours(7),
                    ],
                    [
                        'remitente' => $adminFernando?->id,
                        'texto'     => 'El electricista está balanceando las líneas y revisando el térmico de 30A. Quedará resuelto antes de las 2:00 PM.',
                        'fecha'     => Carbon::now()->subHours(4),
                    ],
                ],
            ],

            // 12. Plomería - Atendiendo (Edificio I, Piso 0, Baño mujeres)
            [
                'asunto'           => 'Baja presión de agua en sanitarios de damas',
                'descripcion'      => 'No sube suficiente agua a los fluxómetros de los sanitarios de damas en la planta baja, afectando el servicio sanitario continuo.',
                'servicio'         => 'Plomería',
                'edificio'         => 'Edificio I',
                'piso'             => 0,
                'aula'             => 'Baño mujeres',
                'id_solicitante'   => $solicAntonio?->id,
                'id_admin_atiende' => $adminChristian?->id,
                'prioridad'        => 'Media',
                'estado'           => 'Atendiendo',
                'es_borrador'      => 0,
                'fecha_creacion'   => Carbon::now()->subDays(4)->setTime(10, 15, 0),
                'fecha_cierre'     => null,
                'mensajes'         => [
                    [
                        'remitente' => $adminChristian?->id,
                        'texto'     => 'Se realizó purga y ajuste en la bomba presurizadora. ¿Podrían avisar si el llenado es el adecuado durante el día?',
                        'fecha'     => Carbon::now()->subDays(1)->setTime(15, 45, 0),
                    ],
                ],
            ],
        ];

        foreach ($ticketsData as $data) {
            // Localizar el aula específica verificando piso y edificio
            $aula = $this->getAula($data['edificio'], $data['piso'], $data['aula']);

            if (!$aula) {
                continue;
            }

            $ticket = Ticket::create([
                'asunto'           => $data['asunto'],
                'descripcion'      => $data['descripcion'],
                'prioridad'        => $data['prioridad'],
                'estado'           => $data['estado'],
                'es_borrador'      => $data['es_borrador'],
                'fecha_creacion'   => $data['fecha_creacion'],
                'fecha_cierre'     => $data['fecha_cierre'],
                'id_solicitante'   => $data['id_solicitante'],
                'id_admin_atiende' => $data['id_admin_atiende'],
                'id_servicio'      => $servicios[$data['servicio']] ?? null,
                'id_edificio'      => $aula->piso->id_edificio,
                'piso'             => $aula->piso->piso,
                'id_aula'          => $aula->id_aula,
            ]);

            // Crear mensajes del chat si existen
            if (!empty($data['mensajes'])) {
                foreach ($data['mensajes'] as $msg) {
                    if (!empty($msg['remitente'])) {
                        Mensaje::create([
                            'id_ticket' => $ticket->id_ticket,
                            'remitente' => $msg['remitente'],
                            'texto'     => $msg['texto'],
                            'fecha_men' => $msg['fecha'],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Busca el Aula exacta por nombre, piso y edificio para evitar colisiones
     */
    private function getAula(string $edificioNombre, int $pisoNumero, string $aulaNombre): ?Aula
    {
        return Aula::where('nombre', $aulaNombre)
            ->whereHas('piso', function ($query) use ($edificioNombre, $pisoNumero) {
                $query->where('piso', $pisoNumero)
                      ->whereHas('edificio', function ($qEdificio) use ($edificioNombre) {
                          $qEdificio->where('nombre', $edificioNombre);
                      });
            })
            ->with('piso.edificio')
            ->first();
    }
}
