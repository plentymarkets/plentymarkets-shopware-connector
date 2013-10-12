// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Export}

/**
 * The export data model defines the different data fields for data export and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Export', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Export/fields"}{/block}
	{
		name: 'status',
		type: 'string'
	}, {
		name: 'start',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'finished',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'error',
		type: 'string'
	}, {
		name: 'name',
		type: 'string'
	}, {
		name: 'mayAnnounce',
		type: 'boolean'
	}, {
		name: 'mayReset',
		type: 'boolean'
	}, {
		name: 'mayErase',
		type: 'boolean'
	}, {
		name: 'isOverdue',
		type: 'boolean'
	}, {
		name: 'needsDependency',
		type: 'boolean'
	}],

	proxy: {
		type: 'ajax',

		api: {
			create: '{url action=handleExport}',
			update: '{url action=handleExport}',
		},

		reader: {
			type: 'json',
			root: 'data'
		}
	}

});
// {/block}
