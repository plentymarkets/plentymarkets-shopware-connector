// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/outgoing_items/OutgoingItems}

/**
 * The outgoing items store is used to autoload the outgoing items data like "booked today". Therfore it
 * defines two data rows. It is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.outgoing_items.OutgoingItems', {
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
		id: 0,
		name: "---"
	}, {
		id: 1,
		name: "heute gebucht"
	}]
});
// {/block}
