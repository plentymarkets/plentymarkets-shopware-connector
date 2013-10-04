// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/dx/ContinuousRecord}

/**
 * The continuous data model defines the different data fields for continuous
 * data import and export and is extended by the Ext data model
 * "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.dx.ContinuousRecord', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/dx/ContinuousRecord/fields"}{/block}
	{
		name: 'Entity',
		type: 'string'
	}, {
		name: 'Section',
		type: 'string'
	}, {
		name: 'Status',
		type: 'integer'
	}, {
		name: 'Error',
		type: 'string'
	}, {
		name: 'LastRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'NextRunTimestamp',
		type: 'date',
		dateFormat: 'timestamp'
	}]
});
// {/block}
