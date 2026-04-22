<?php

/**
 * CRMContainer
 *
 * Service registry for the CRM module. One instance of each CRM Object and
 * Widget per request. Controllers call CRMContainer::get('name').
 *
 * Adding a new service: add a case to make() and a require_once below.
 */

require_once __DIR__ . '/Objects/Account.php';
require_once __DIR__ . '/Objects/Contact.php';
require_once __DIR__ . '/Objects/Location.php';
require_once __DIR__ . '/Objects/Opportunity.php';
require_once __DIR__ . '/Objects/OpportunityProductLineItem.php';
require_once __DIR__ . '/Objects/ProductDefinition.php';
require_once __DIR__ . '/Widgets/AccountContacts.php';
require_once __DIR__ . '/Widgets/AccountPerformance.php';
require_once __DIR__ . '/Widgets/AccountOpportunities.php';
require_once __DIR__ . '/Widgets/AccountLocations.php';
require_once __DIR__ . '/Widgets/DashContacts.php';
require_once __DIR__ . '/Widgets/DashOpenDeals.php';
require_once __DIR__ . '/Widgets/DashLeads.php';

class CRMContainer
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
            default                 => throw new RuntimeException("CRMContainer: unknown service '{$id}'"),
        };
    }
}
