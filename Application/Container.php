<?php

/**
 * Container
 *
 * Simple service registry. One DB connection and one instance of each Object /
 * Widget class per request. Controllers call Container::get('name') or the
 * typed helper Container::db() instead of instantiating inline.
 *
 * Adding a new service: add a case to make() and a require_once below.
 */

require_once __DIR__ . '/Objects/User.php';
require_once __DIR__ . '/Module/CRM/Objects/Account.php';
require_once __DIR__ . '/Module/CRM/Objects/Contact.php';
require_once __DIR__ . '/Module/CRM/Objects/Location.php';
require_once __DIR__ . '/Module/CRM/Objects/Opportunity.php';
require_once __DIR__ . '/Module/CRM/Objects/OpportunityProductLineItem.php';
require_once __DIR__ . '/Module/CRM/Objects/ProductDefinition.php';
require_once __DIR__ . '/Module/CRM/Widgets/AccountContacts.php';
require_once __DIR__ . '/Module/CRM/Widgets/AccountPerformance.php';
require_once __DIR__ . '/Module/CRM/Widgets/AccountOpportunities.php';
require_once __DIR__ . '/Module/CRM/Widgets/AccountLocations.php';
require_once __DIR__ . '/Module/CRM/Widgets/DashContacts.php';
require_once __DIR__ . '/Module/CRM/Widgets/DashOpenDeals.php';
require_once __DIR__ . '/Module/CRM/Widgets/DashLeads.php';

class Container
{
    private static ?DB $db = null;
    private static array $services = [];

    public static function db(): DB
    {
        return self::$db ??= new DB();
    }

    public static function get(string $id): object
    {
        return self::$services[$id] ??= self::make($id);
    }

    private static function make(string $id): object
    {
        $db = self::db();
        return match ($id) {
            'user'                  => new User($db),
            'account'               => new Account($db),
            'contact'               => new Contact($db),
            'location'              => new Location($db),
            'opportunity'           => new Opportunity($db),
            'line_item'             => new OpportunityProductLineItem($db),
            'product'               => new ProductDefinition($db),
            'account_contacts'      => new AccountContacts($db),
            'account_performance'   => new AccountPerformance($db),
            'account_opportunities' => new AccountOpportunities($db),
            'account_locations'     => new AccountLocations($db),
            'dash_contacts'         => new DashContacts($db),
            'dash_deals'            => new DashOpenDeals($db),
            'dash_leads'            => new DashLeads($db),
            default                 => throw new RuntimeException("Container: unknown service '{$id}'"),
        };
    }
}
