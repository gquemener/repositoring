<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Database\DatabaseProviderInterface;
use Cycle\Schema\Definition\Entity;
use App\Domain\Todo;
use Cycle\Schema\Definition\Field;
use Cycle\Schema\Definition\Relation;

return function(DatabaseProviderInterface $dbal): Schema {
    $r = new Registry($dbal);

    $entity = new Entity();
    $entity->setRole('todo');
    $entity->setClass(Todo::class);
    $entity->getFields()->set(
        'id', (new Field())->setType('primary')->setColumn('id')->setPrimary(true)
    );
    $entity->getRelations()->set(
        'description', (new Relation())->setType('embedded')
    );
    $r->register($entity);
    $r->linkTable($entity, 'default', 'cycle_orm_todo');

    return new Schema((new Compiler())->compile($r, []));
};
