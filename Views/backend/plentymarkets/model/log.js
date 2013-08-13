// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Log}

/**
 * The log data model defines the different data fields for logging and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Log', {

	extend: 'Ext.data.Model',

	fields: [

	// {block name="backend/Plentymarkets/model/Log/fields"}{/block}
	{
		name: 'id',
		type: 'integer'
	}, {
		name: 'timestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'type',
		type: 'integer'
	}, {
		name: 'identifier',
		type: 'string'
	}, {
		name: 'message',
		type: 'string'
	}, {
		name: 'longmessage',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		api: {
			read: '{url action=getLog}'
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
