// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/dx/Continuous}
Ext.define('Shopware.apps.Plentymarkets.model.dx.Continuous', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/plentymarkets/model/dx/Continuous/fields"}{/block}
	{
		name: 'ExportOrderStatus',
		type: 'string'
	}, {
		name: 'ExportOrderError',
		type: 'string'
	}, {
		name: 'ExportOrderLastRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ExportOrderNextRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	},

	{
		name: 'ImportItemStatus',
		type: 'string'
	}, {
		name: 'ImportItemError',
		type: 'string'
	}, {
		name: 'ImportItemLastRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ImportItemLastUpdateTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ImportItemNextRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	},

	{
		name: 'ImportItemStockStatus',
		type: 'string'
	}, {
		name: 'ImportItemStockError',
		type: 'string'
	}, {
		name: 'ImportItemStockLastRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ImportItemStockLastUpdateTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ImportItemStockNextRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	},

	{
		name: 'ImportItemPriceStatus',
		type: 'string'
	}, {
		name: 'ImportItemPriceError',
		type: 'string'
	}, {
		name: 'ImportItemPriceLastRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ImportItemPriceNextRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}

	],

	proxy: {
		type: 'ajax',
		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
