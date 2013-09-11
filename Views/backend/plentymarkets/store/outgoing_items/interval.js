// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/outgoing_items/Interval}

/**
 * The interval store is used to autoload the time interval of outgoing items. Therfore it
 * defines three data rows with different time data. It is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.outgoing_items.Interval', {
	extend: 'Ext.data.Store',

	autoLoad: true,
	remoteFilter: false,
	remoteSort: false,

	pageSize: 100,

	fields: [{
		name: 'id',
		type: 'integer'
	}, {
		name: 'name',
		type: 'string'
	}],

	data: [{
		id: 1,
		name: "täglich, 12:00 Uhr"
	}, {
		id: 2,
		name: "täglich, 18:00 Uhr"
	}, {
		id: 3,
		name: "stündlich"
	}]
});
// {/block}
