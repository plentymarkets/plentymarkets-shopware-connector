// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/outgoing_items/OutgoingItems}
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
