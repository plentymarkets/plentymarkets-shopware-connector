// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Settings}

/**
 * The settings data model defines the different data fields for reading, saving, deleting settings data and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
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
		name: 'ApiUseGzipCompression',
		type: 'boolean',
		defaultValue: false
	}, {
		name: 'ApiLogHttpHeaders',
		type: 'boolean',
		defaultValue: false
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
		name: 'ItemCategorySyncActionID',
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'ItemCleanupActionID',
		type: 'integer',
		defaultValue: 1
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
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'OutgoingItemsIntervalID',
		type: 'integer',
		defaultValue: 3
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
	
	{
		name: 'InitialExportChunkSize',
		type: 'integer',
		defaultValue: 250
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
