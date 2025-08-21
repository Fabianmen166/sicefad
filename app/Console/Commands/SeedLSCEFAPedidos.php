<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\LSCEFA\Models\Service;
use Modules\LSCEFA\Models\Process;
use Modules\LSCEFA\Models\ServiceProcessDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SeedLSCEFAPedidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lscefa:seed-pedidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pobla la base de datos con 5 pedidos pendientes para cada servicio LSCEFA';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando creación de pedidos para cada servicio...');
        $services = Service::all();
        $now = Carbon::now();
        $dummyQuoteId = 'DUMMY-QUOTE-PRUEBA';
        $created = 0;

        // Crear cotización dummy si no existe
        $quoteExists = DB::table('quotes')->where('quote_id', $dummyQuoteId)->exists();
        if (!$quoteExists) {
            DB::table('quotes')->insert([
                'quote_id' => $dummyQuoteId,
                'customer_id' => 1,
                'user_id' => 1,
                'total' => 10000,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->info('Cotización dummy creada.');
        }

        foreach ($services as $service) {
            for ($i = 1; $i <= 5; $i++) {
                $processId = 'PROC-' . $service->services_id . '-' . $i . '-' . Str::random(4);
                $process = Process::updateOrCreate(
                    ['process_id' => $processId],
                    [
                        'process_id' => $processId,
                        'quote_id' => $dummyQuoteId,
                        'item_code' => 'ITEM-' . $service->services_id . '-' . $i,
                        'status' => 'pending',
                        'client_communication' => 'Pedido de prueba para servicio ' . $service->descripcion,
                        'processing_days' => rand(3, 10),
                        'reception_date' => $now,
                        'description' => 'Descripción dummy para el servicio ' . $service->descripcion,
                        'sampling_place' => 'Lugar de muestreo dummy',
                        'sampling_date' => $now->copy()->subDays(rand(1, 5)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                ServiceProcessDetail::updateOrCreate(
                    [
                        'process_id' => $process->process_id,
                        'service_id' => $service->services_id,
                    ],
                    [
                        'status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $created++;
            }
        }
        $this->info("Se crearon/actualizaron $created procesos y detalles de servicio.");
        return 0;
    }
}
