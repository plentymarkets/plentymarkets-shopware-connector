// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Manufacturer}

/**
 * The manufacturer data model defines the different data fields for manufacturer lists and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Manufacturer', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Manufacturer/fields"}{/block}
	{
		name: 'id',
		type: 'integer'
	}, {
		name: 'name',
		type: 'string'
	}]

});
// {/block}
