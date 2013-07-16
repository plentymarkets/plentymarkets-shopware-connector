// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Settings}
Ext.define('Shopware.apps.Plentymarkets.model.Settings', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Settings/fields"}{/block}
	{
		name: 'PlentymarketsVersion',
		type: 'string'
	}, {
		name: 'ApiWsdl',
		type: 'string'
	}, {
		name: 'ApiUsername',
		type: 'string'
	}, {
		name: 'ApiPassword',
		type: 'string'
	}, {
		name: 'ApiStatus',
		type: 'integer'
	}, {
		name: 'ApiLastAuthTimestamp',
		type: 'integer'
	}, {
		name: 'ApiLastStatusTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	},

	{
		name: 'IsSettingsFinished',
		type: 'bool'
	}, {
		name: 'IsExportFinished',
		type: 'bool'
	}, {
		name: 'IsMappingFinished',
		type: 'bool'
	}, {
		name: 'MayDatex',
		type: 'bool'
	}, {
		name: 'MayDatexUser',
		type: 'bool'
	}, {
		name: 'MayDatexActual',
		type: 'bool'
	},

	{
		name: 'ItemWarehouseID',
		type: 'integer',
		defaultValue: 0
	}, {
		name: 'ItemCategoryRootID',
		type: 'integer',
		defaultValue: 3
	}, {
		name: 'DefaultCustomerGroupKey',
		type: 'string',
		defaultValue: 'EK'
	}, {
		name: 'ItemWarehousePercentage',
		type: 'integer',
		defaultValue: 90
	}, {
		name: 'ItemProducerID',
		type: 'integer'
	}, {
		name: 'WebstoreID',
		type: 'integer'
	}, {
		name: 'OrderReferrerID',
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'OrderMarking1',
		type: 'integer'
	}, {
		name: 'OutgoingItemsOrderStatus',
		type: 'float'
	}, {
		name: 'OutgoingItemsID',
		type: 'integer'
	}, {
		name: 'OutgoingItemsIntervalID',
		type: 'integer'
	}, {
		name: 'OutgoingItemsShopwareOrderStatusID',
		type: 'integer',
		defaultValue: 7
	}, {
		name: 'IncomingPaymentShopwarePaymentFullStatusID',
		type: 'integer',
		defaultValue: 12
	}, {
		name: 'IncomingPaymentShopwarePaymentPartialStatusID',
		type: 'integer',
		defaultValue: 11
	}, {
		name: 'OrderPaidStatusID',
		type: 'integer',
		defaultValue: 12
	},

	],

	proxy: {
		type: 'ajax',

        api: {
        	read:   '{url action=readSettings}',
            update: '{url action=saveSettings}',
            delete: '{url action=deleteSettings}'
        },

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
