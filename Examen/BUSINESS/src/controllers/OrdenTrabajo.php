<?php

namespace app\controllers;

use app\Services\DataService;
use app\Validators\OrdenTrabajoValidator;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrdenTrabajo
{
    private const RECURSO = 'ordenes_trabajo';

    public function __construct(private ContainerInterface $container) {}

    public function read(Request $request, Response $response, array $args): Response
    {
        try {
            if (isset($args['id']) && !$this->idValido($args['id'])) {
                return $this->json($response, ['error' => 'El id debe ser un entero positivo.'], 400);
            }

            $dataService = $this->container->get(DataService::class);
            $path = isset($args['id']) ? self::RECURSO . '/' . rawurlencode($args['id']) : self::RECURSO;

            return $this->reenviar($response, $dataService->get($path));
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->leerJson($request);
            if ($data === null) {
                return $this->json($response, ['errors' => ['body' => 'Debe enviar un objeto JSON válido']], 400);
            }

            $errors = (new OrdenTrabajoValidator())->validate($data);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->post(self::RECURSO, json_encode($data, JSON_THROW_ON_ERROR));

            return $this->reenviar($response, $upstream);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            if (!$this->idValido($args['id'])) {
                return $this->json($response, ['error' => 'El id debe ser un entero positivo.'], 400);
            }

            $data = $this->leerJson($request);
            if ($data === null || $data === []) {
                return $this->json($response, ['errors' => ['body' => 'Debe enviar un objeto JSON válido']], 400);
            }

            $errors = (new OrdenTrabajoValidator())->validate($data, true);
            if ($errors !== []) {
                return $this->json($response, ['errors' => $errors], 422);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->put(
                self::RECURSO . '/' . rawurlencode($args['id']),
                json_encode($data, JSON_THROW_ON_ERROR)
            );

            return $this->reenviar($response, $upstream);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            if (!$this->idValido($args['id'])) {
                return $this->json($response, ['error' => 'El id debe ser un entero positivo.'], 400);
            }

            $dataService = $this->container->get(DataService::class);
            $upstream = $dataService->delete(self::RECURSO . '/' . rawurlencode($args['id']));

            return $this->reenviar($response, $upstream);
        } catch (ConnectException) {
            return $this->json($response, ['error' => 'El servicio de datos no está disponible'], 502);
        } catch (RequestException) {
            return $this->json($response, ['error' => 'No se pudo consultar el servicio de datos'], 502);
        }
    }

    private function idValido(string $id): bool
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0;
    }

    private function leerJson(Request $request): ?array
    {
        $request->getBody()->rewind();
        $data = json_decode((string) $request->getBody(), true);

        return is_array($data) && json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    private function reenviar(Response $response, $upstream): Response
    {
        $response->getBody()->write((string) $upstream->getBody());

        return $response
            ->withHeader('Content-Type', $upstream->getHeaderLine('Content-Type') ?: 'application/json; charset=utf-8')
            ->withStatus($upstream->getStatusCode());
    }

    private function json(Response $response, array $payload, int $status): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
