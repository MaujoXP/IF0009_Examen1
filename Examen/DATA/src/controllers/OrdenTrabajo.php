<?php
declare(strict_types=1);

namespace app\controllers;

use app\Models\OrdenTrabajo as OrdenTrabajoModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrdenTrabajo
{
    private const ESTADOS_ACTIVOS = ['Pendiente', 'En Proceso'];
    private const CAMPOS = ['placa_vehiculo', 'propietario', 'descripcion_falla', 'costo_estimado', 'estado'];

    public function __construct(private ContainerInterface $container) {}

    public function read(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        if (isset($args['id'])) {
            $orden = OrdenTrabajoModel::find($args['id']);

            if ($orden === null) {
                return $this->json($response, ['error' => 'Orden de trabajo no encontrada.'], 404);
            }

            return $this->json($response, ['data' => $orden->toArray()], 200);
        }

        $ordenes = OrdenTrabajoModel::query()->orderBy('id')->get()->toArray();

        return $this->json($response, ['data' => $ordenes], 200);
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];
        $data = array_intersect_key($data, array_flip(self::CAMPOS));

        if ($this->tieneOrdenActiva((string) ($data['placa_vehiculo'] ?? ''))) {
            return $this->json($response, [
                'error' => 'El vehículo ya tiene una orden de trabajo activa (Pendiente o En Proceso).',
            ], 409);
        }

        $orden = OrdenTrabajoModel::create($data);
        $orden->refresh();

        return $this->json($response, ['data' => $orden->toArray()], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        $orden = OrdenTrabajoModel::find($args['id']);

        if ($orden === null) {
            return $this->json($response, ['error' => 'Orden de trabajo no encontrada.'], 404);
        }

        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];
        $data = array_intersect_key($data, array_flip(self::CAMPOS));

        if (
            isset($data['placa_vehiculo'])
            && $data['placa_vehiculo'] !== $orden->placa_vehiculo
            && $this->tieneOrdenActiva((string) $data['placa_vehiculo'], (int) $orden->id)
        ) {
            return $this->json($response, [
                'error' => 'El vehículo ya tiene una orden de trabajo activa (Pendiente o En Proceso).',
            ], 409);
        }

        $orden->fill($data);
        $orden->save();

        return $this->json($response, ['data' => $orden->refresh()->toArray()], 200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->container->get('eloquent');

        $orden = OrdenTrabajoModel::find($args['id']);

        if ($orden === null) {
            return $this->json($response, ['error' => 'Orden de trabajo no encontrada.'], 404);
        }

        if ($orden->estado === 'En Proceso') {
            return $this->json($response, [
                'error' => 'No se puede eliminar una orden de trabajo que está En Proceso.',
            ], 409);
        }

        $orden->delete();

        return $response->withStatus(204);
    }

    private function tieneOrdenActiva(string $placa, ?int $excluirId = null): bool
    {
        $query = OrdenTrabajoModel::query()
            ->where('placa_vehiculo', $placa)
            ->whereIn('estado', self::ESTADOS_ACTIVOS);

        if ($excluirId !== null) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }

    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
