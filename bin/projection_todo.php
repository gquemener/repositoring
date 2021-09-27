#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use App\Infrastructure\Repository\Prooph\OpenedTodoReadModel;
use App\Domain\TodoWasOpened;
use App\Domain\TodoWasClosed;
use Prooph\EventStore\Pdo\Projection\PdoEventStoreReadModelProjector;
use Prooph\EventStore\Projection\ReadModelProjector;

$pdo = new PDO('pgsql:host=postgres;port=5432;dbname=repositoring;user=bruce;password=mypass');
$eventStore = new PostgresEventStore(new FQCNMessageFactory(), $pdo, new PostgresSingleStreamStrategy());
$projectionManager = new PostgresProjectionManager($eventStore, $pdo);

$readModel = new OpenedTodoReadModel($pdo);

$projection = $projectionManager->createReadModelProjection('opened_todo', $readModel, [ReadModelProjector::OPTION_PCNTL_DISPATCH => true]);

pcntl_signal(SIGINT, function () use ($projection) {
    $projection->stop();

    exit(0);
});

$projection
    ->fromStream('todo')
    ->when([
        TodoWasOpened::class => function ($state, TodoWasOpened $event) {
            $this->readModel()->stack('insert', [
                'id' => $event->id->asString(),
                'description' => $event->description->asString(),
            ]);
        },
        TodoWasClosed::class => function ($state, TodoWasClosed $event) {
            $this->readModel()->stack('remove', [
                'id' => $event->id->asString(),
            ]);
        },
    ])
    ->run()
;
