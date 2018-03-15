<?php
/**
 * This file is part of railt.org package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\RailtAuthorization;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Guard;
use Railt\Foundation\Events\TypeBuilding;
use Railt\Foundation\Extensions\BaseExtension;
use Railt\Io\File;
use Railt\Reflection\Contracts\Dependent\FieldDefinition;
use Railt\SDL\Schema\CompilerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AuthorizationExtension
 */
class AuthorizationExtension extends BaseExtension
{
    private const SCHEMA_FILE = __DIR__ . '/../auth.graphqls';

    /**
     * @param CompilerInterface $compiler
     * @param EventDispatcherInterface $events
     */
    public function boot(CompilerInterface $compiler, EventDispatcherInterface $events): void
    {
        $compiler->compile(File::fromPathname(self::SCHEMA_FILE));

        $events->addListener(TypeBuilding::class, function (TypeBuilding $event) {
            $field = $event->getType();

            if ($field instanceof FieldDefinition && ! $this->allowed($field)) {
                $event->stopPropagation();
            }
        });
    }

    /**
     * @param FieldDefinition $field
     * @return bool
     */
    private function allowed(FieldDefinition $field): bool
    {
        return $this->auth($field) && $this->guest($field) && $this->gate($field);
    }

    /**
     * @param FieldDefinition $field
     * @return bool
     */
    private function auth(FieldDefinition $field): bool
    {
        if ($field->hasDirective('auth')) {
            return $this->isAuthenticatable();
        }

        return true;
    }

    /**
     * @param FieldDefinition $field
     * @return bool
     */
    private function guest(FieldDefinition $field): bool
    {
        if ($field->hasDirective('guest')) {
            return ! $this->isAuthenticatable();
        }

        return true;
    }

    /**
     * @param FieldDefinition $field
     * @return bool
     */
    private function gate(FieldDefinition $field): bool
    {
        if ($field->hasDirective('can')) {
            if (! $this->isAuthenticatable()) {
                return false;
            }

            foreach ($field->getDirectives('can') as $gate) {
                if (! $this->hasRole($gate->getPassedArgument('role'), $field)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $role
     * @param FieldDefinition $field
     * @return bool
     */
    private function hasRole(string $role, FieldDefinition $field): bool
    {
        return $this->make(Authorizable::class)->can($role, [$field]);
    }

    /**
     * @return bool
     */
    private function isAuthenticatable(): bool
    {
        return $this->make(Guard::class)->check();
    }
}
