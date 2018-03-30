<?php

namespace Febalist\LaravelSupport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;
use ReflectionMethod;

class Macro
{
    protected $blueprint;

    public function __construct(Blueprint $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public static function register()
    {
        $methods = static::methods();

        foreach ($methods as $method) {
            Blueprint::macro($method, function (...$arguments) use ($method) {
                $macro = new Macro($this);

                return $macro->$method(...$arguments);
            });
        }
    }

    protected static function methods()
    {
        $class = new ReflectionClass(static::class);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        return collect($methods)->filter(function (ReflectionMethod $method) {
            return !$method->isStatic() && !starts_with($method->name, '_');
        })->pluck('name');
    }

    public function model($reference, $nullable = false, $cascade = null)
    {
        $reference = $this->parseReference($reference);

        $cascade = $cascade === null ? !$nullable : $cascade;
        $onDelete = $cascade ? 'CASCADE' : ($nullable ? 'SET NULL' : 'NO ACTION');

        $fluent = $this->blueprint->unsignedInteger($reference['column'])->index();
        if ($nullable) {
            $fluent->nullable();
        }
        $this->blueprint->foreign($reference['foreign'])->references($reference['key'])->on($reference['table'])
            ->onUpdate('CASCADE')->onDelete($onDelete);

        return $fluent;
    }

    public function dropModel($reference)
    {
        $reference = $this->parseReference($reference);

        $this->blueprint->dropForeign($reference['foreign']);
        $this->blueprint->dropColumn($reference['column']);
    }

    public function name($column = 'name', $length = null)
    {
        return $this->blueprint->string($column, $length);
    }

    public function description($column = 'description')
    {
        return $this->blueprint->text($column);
    }

    public function total($column = 'total', $total = 9, $places = 2)
    {
        return $this->blueprint->float($column, $total, $places);
    }

    public function amount($column = 'amount')
    {
        return $this->blueprint->unsignedInteger($column);
    }

    protected function parseReference($reference)
    {
        if (!is_array($reference)) {
            $reference = [$reference];
        }

        $table = $reference[0];
        if (class_exists($table)) {
            $model = new $table();
            if ($model instanceof Model) {
                $table = $model->getTable();
                $key = $model->getKeyName();
                $column = $reference[1] ?? $model->getForeignKey();
            } else {
                throw new \Exception('Invalid column');
            }
        } else {
            $key = $reference[1] ?? 'id';
            $column = $reference[2] ?? str_singular($table).'_'.$key;
        }

        $foreign = [$column];

        return compact('column', 'foreign', 'table', 'key');
    }
}
