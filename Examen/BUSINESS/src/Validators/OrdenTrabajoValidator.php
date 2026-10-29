<?php

declare(strict_types=1);

namespace app\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class OrdenTrabajoValidator
{
    private const ESTADOS = ['Pendiente', 'En Proceso', 'Finalizado', 'Entregado'];

    private function reglas(): array
    {
        return [
            'placa_vehiculo' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(4, 10))
                    ->regex('/^[A-Za-z0-9-]{4,10}$/'),
                'mensaje' => 'La placa es obligatoria, de 4 a 10 caracteres (letras, números o guion).',
            ],

            'propietario' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(3, 100))
                    ->regex('/^[A-Za-zÑñÁÉÍÓÚáéíóú]+(?: [A-Za-zÑñÁÉÍÓÚáéíóú]+)*$/u'),
                'mensaje' => 'El propietario es obligatorio, de 3 a 100 caracteres, solo letras y espacios.',
            ],

            'descripcion_falla' => [
                'regla' => v::stringType()
                    ->notBlank()
                    ->length(v::between(5, 1000)),
                'mensaje' => 'La descripción de la falla es obligatoria y debe tener entre 5 y 1000 caracteres.',
            ],

            'costo_estimado' => [
                'regla' => v::numericVal()
                    ->positive()
                    ->lessThanOrEqual(99999999.99),
                'mensaje' => 'El costo estimado debe ser un número mayor que 0 (máximo 99999999.99).',
            ],

            'estado' => [
                'regla' => v::stringType()->in(self::ESTADOS),
                'mensaje' => 'El estado debe ser: Pendiente, En Proceso, Finalizado o Entregado.',
            ],
        ];
    }

    /**
     * En la creación todos los campos son obligatorios excepto "estado".
     * En la actualización ($parcial = true) solo se validan los campos enviados.
     */
    public function validate(array $data, bool $parcial = false): array
    {
        $errores = [];

        foreach ($this->reglas() as $campo => $config) {
            $existe = array_key_exists($campo, $data);

            if ($campo === 'estado' && !$existe) {
                continue;
            }
            if ($parcial && !$existe) {
                continue;
            }

            if (!$config['regla']->isValid($data[$campo] ?? null)) {
                $errores[$campo] = $config['mensaje'];
            }
        }

        return $errores;
    }
}
