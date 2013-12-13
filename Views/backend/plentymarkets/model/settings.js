// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Settings}

/**
 * The settings data model defines the different data fields for reading,
 * saving, deleting settings data and is extended by the Ext data model
 * "Ext.data.Model".
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
		name: 'ApiIgnoreGetServerTime',
		type: 'boolean',
		defaultValue: false
	}, {
		name: 'ApiUseGzipCompression',
		type: 'boolean',
		defaultValue: false
	}, {
		name: 'ApiLogHttpHeaders',
		type: 'boolean',
		defaultValue: false
	}, {
		name: 'ApiHideCallsInLog',
		type: 'boolean',
		defaultValue: false
	}, {
		name: 'ApiLastAuthTimestamp',
		type: 'integer'
	}, {
		name: 'ApiLastStatusTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ApiTimestampDeviation',
		type: 'integer',
		defaultValue: 0
	},

	{
		name: 'IsSettingsFinished',
		type: 'bool',
		defaultValue: false
	}, {
		name: 'IsExportFinished',
		type: 'bool',
		defaultValue: false
	}, {
		name: 'IsDataIntegrityValid',
		type: 'bool',
		defaultValue: false
	}, {
		name: 'IsMappingFinished',
		type: 'bool',
		defaultValue: false
	}, {
		name: 'MayDatex',
		type: 'bool',
		defaultValue: false
	}, {
		name: 'MayDatexUser',
		type: 'bool',
		defaultValue: false
	}, {
		name: 'MayDatexActual',
		type: 'bool',
		defaultValue: false
	},

	{
		name: 'ItemWarehouseID',
		type: 'integer',
		defaultValue: 0
	}, {
		name: 'ItemImageSyncActionID',
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'ItemCategorySyncActionID',
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'ItemNumberImportActionID',
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'ItemCleanupActionID',
		type: 'integer',
		defaultValue: 1
	}, {
		name: 'ItemAssociateImportActionID',
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
	}, {
		name: 'OrderItemTextSyncActionID',
		type: 'integer',
		defaultValue: 1
	},

    {
		name: 'CustomerDefaultCity',
		type: 'string'
	}, {
		name: 'CustomerDefaultHouseNumber',
		type: 'string'
	}, {
		name: 'CustomerDefaultStreet',
		type: 'string'
	}, {
		name: 'CustomerDefaultZipcode',
		type: 'string'
	},

	{
		name: 'InitialExportChunkSize',
		type: 'integer',
		defaultValue: 250
	}, {
		name: 'InitialExportChunksPerRun',
		type: 'integer',
		defaultValue: -1
	}, {
		name: 'ImportItemChunkSize',
		type: 'integer',
		defaultValue: 250
	},
	
	{
		name: 'ConnectorVersion',
		type: 'string'
	},
	
	//
	{
		name: 'MayLogUsageData',
		type: 'boolean',
		defaultValue: false
	},
	
	//
	{
		name: '_WebserverSoftware',
		type: 'string'
	}, {
		name: '_WebserverSignature',
		type: 'string'
	}, {
		name: '_PhpInterface',
		type: 'string'
	}, {
		name: '_PhpVersion',
		type: 'string'
	}, {
		name: '_PhpMemoryLimit',
		type: 'string'
	}, {
		name: '_ApacheModules',
		type: 'string'
	}

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
