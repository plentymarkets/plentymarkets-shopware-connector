// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/data/Status}

/**
 * The data data model defines the different data fields for dataging and is
 * extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.data.Field', {

	extend: 'Ext.data.Model',

	fields: [

	// {block name="backend/Plentymarkets/model/data/Status/fields"}{/block}
	{
		name: 'name',
		type: 'string'
	}, {
		name: 'description',
		type: 'string'
	}, {
		name: 'type',
		type: 'string'
	}],

});
// {/block}
