// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/outgoing_items/Interval}
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
