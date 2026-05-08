<?php

require_once __DIR__ . '/Objects/Ticket.php';
require_once __DIR__ . '/Widgets/TicketSummary.php';

class ITSMContainer
{
    private static array $services = [];

    public static function get(string $id): object
    {
        return self::$services[$id] ??= self::make($id);
    }

    private static function make(string $id): object
    {
        $db = Container::db();
        return match ($id) {
            'ticket'         => new Ticket($db),
            'ticket_summary' => new TicketSummary($db),
            default          => throw new RuntimeException("ITSMContainer: unknown service '{$id}'"),
        };
    }
}
