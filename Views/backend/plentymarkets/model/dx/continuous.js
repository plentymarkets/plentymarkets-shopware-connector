// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/dx/Continuous}

/**
 * The continuous data model defines the different data fields for continuous
 * data import and export and is extended by the Ext data model
 * "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.dx.Continuous', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/settings/Batch/fields"}{/block}
	'id'],

	associations: [{
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.dx.ContinuousRecord',
		name: 'getImport',
		associationKey: 'import'
	}, {
		type: 'hasMany',
		model: 'Shopware.apps.Plentymarkets.model.dx.ContinuousRecord',
		name: 'getExport',
		associationKey: 'export'
	}]
});
// {/block}
